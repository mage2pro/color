<?php
namespace Dfe\Color;
use Dfe\Color\Setup\UpgradeSchema as Schema;
use Google\Cloud\Vision\V1\AnnotateImageResponse as Res;
use Google\Cloud\Vision\V1\ColorInfo;
use Google\Cloud\Vision\V1\DominantColorsAnnotation as Dominant;
use Google\Cloud\Vision\V1\ImageAnnotatorClient as Annotator;
use Google\Type\Color;
// 2019-08-22
final class Image {
	/**
	 * 2019-08-22
	 * @used-by vendor/mage2pro/color/view/frontend/templates/index.phtml
	 * @return array(string => string)
	 */
	function labels() {return array_filter(df_map_kr($this->probabilities(), function($k, $v) {return [
		self::optsM()[$k], dff_eq0($v) ? 0 : dff_2f($v)
	];}));}

	/**
	 * 2019-08-22
	 * @used-by labels()
	 * @used-by \Dfe\Color\Observer\ProductSaveBefore::execute()
	 * @return array(int => float)
	 */
	function probabilities() {return dfc($this, function() {
		$ciaAll = iterator_to_array($this->dominant()->getColors()); /** @var ColorInfo[] $ciaAll */
		// 2019-08-23
		// Sometimes (rarely) Cloud Vision API wrongly considers a white background as the primary color.
		// So I filter out all colors close to white.
		$cia = array_values(array_filter($ciaAll, function(ColorInfo $ci) {
			$co = $ci->getColor(); /** @var Color $co */
			return 250 * 3 > $co->getRed() + $co->getGreen() + $co->getBlue();
		})) ?: [$ciaAll[0]]; /** @var ColorInfo[] $cia */
		return self::softmaxNeg(df_sort(df_map(self::palette(), function(array $cc) use($cia) {return
			self::dist($cia, $cc)
		;})));
	});}

	/**
	 * 2019-08-22
	 * @used-by \Dfe\Color\Observer\ProductSaveBefore::execute()
	 * @used-by vendor/mage2pro/color/view/frontend/templates/index.phtml
	 * @param string $path
	 */
	function __construct($path) {
		dfcf(function() {
			// 2019-08-21
			// https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.107.1/guides/authentication
			// https://github.com/googleapis/google-auth-library-php/tree/v1.5.2#application-default-credentials
			putenv('GOOGLE_APPLICATION_CREDENTIALS=' . df_fs_etc('google-app-credentials.json'));
		});
		$this->_path = $path;
	}

	/**
	 * 2019-08-22
	 * @used-by probabilities()
	 * @return Dominant
	 */
	private function dominant() {return dfc($this, function() {
		/** @var Dominant $r */
		$f = file_get_contents($this->_path); /** @var string $f */
		$k = md5($f . __CLASS__); /** @var string $k */
		$useCache = true;
		if ($useCache && false !== ($json = df_cache_load($k))) { /** @var string|bool $resultS */
			$r = new Dominant;
			$r->mergeFromJsonString($json);
		}
		else {
			$a = new Annotator; /** @var Annotator $a */
			try {$res = $a->imagePropertiesDetection($f); /** @var Res $res */}
			finally {$a->close();}
			$r = $res->getImagePropertiesAnnotation()->getDominantColors();
			if ($useCache) {
				df_cache_save($r->serializeToJsonString(), $k);
			}
		}
		return $r;
	});}

	/**
	 * 2019-08-22
	 * @used-by __construct()
	 * @used-by dominant()
	 * @var string
	 */
	private $_path;

	/**
	 * 2019-08-21
	 * @used-by probabilities()
	 * @param ColorInfo[] $cia
	 * @param int[][] $cc
	 * @return float
	 */
	private static function dist(array $cia, array $cc) {
		$ci = df_first($cia); /** @var ColorInfo $ci */
		return min(df_map($cc, function($tone) use($ci) {
			$co = $ci->getColor(); /** @var Color $co */
			return Diff::p($tone, [$co->getRed(), $co->getGreen(), $co->getBlue()]);
		}));
	}

	/**
	 * 2019-08-22
	 * @used-by optsM()
	 * @used-by palette()
	 * @return array(array(string => int|string))
	 */
	private static function opts() {return df_product_att_options('color');}

	/**
	 * 2019-08-22
	 * @used-by labels()
	 * @return array(int => string)
	 */
	private static function optsM() {return dfcf(function() {return array_map('strtolower', array_column(
		self::opts(), 'label', 'value')
	);});}

	/**
	 * 2019-08-22
	 * @used-by probabilities()
	 * @return array(array(string => int|string))
	 */
	private static function palette() {return dfcf(function() {
		/** @var array(int => array(string => string)) $d */
		$d = df_swatches_h()->getSwatchesByOptionsId(df_int(array_column(self::opts(), 'value')));
		$primary = array_column($d, 'value', 'option_id'); /** @var array(int => string) $primary */
		/** @var array(int => string[]) $tones */
		$tones = array_map('array_filter', array_map('df_json_decode', array_column($d, Schema::F, 'option_id')));
		$r = [];
		foreach ($primary as $id => $v) { /** @var int $id */ /** @var string $v */
			$r[$id]	= array_map('df_hex2rgb', array_merge([$v], dfa($tones, $id, [])));
		}
		return $r;
	});}

	/**
	 * 2019-08-22
	 * @used-by probabilities()
	 * @param array(int|string => float) $a
	 * @return array(int|string => float)
	 */
	private static function softmaxNeg(array $a) {
		$a = array_map(function($v) {return exp(-$v);}, $a);
		if (!array_filter($a)) {
			$r = $a;
		}
		else {
			$f = 100 / array_sum($a); /** @var float $f */
			$r = array_map(function ($v) use ($f) {return $v * $f;}, $a);
		}
		return $r;
	}
}