<?php
namespace Dfe\Color;
use Google\Cloud\Vision\V1\AnnotateImageResponse as Res;
use Google\Cloud\Vision\V1\ColorInfo;
use Google\Cloud\Vision\V1\DominantColorsAnnotation as Dominant;
use Google\Cloud\Vision\V1\ImageAnnotatorClient as Annotator;
use Google\Type\Color;
// 2019-08-22
final class Image {
	/**
	 * 2019-08-22
	 * @return array(string => string)
	 */
	function labels() {return array_filter(df_map_kr($this->probabilities(), function($k, $v) {return [
		self::optsM()[$k], dff_eq0($v) ? 0 : dff_2f($v)
	];}));}

	/**
	 * 2019-08-22
	 * @return array(int => float)
	 */
	function probabilities() {return dfc($this, function() {
		$cia = iterator_to_array($this->dominant()->getColors()); /** @var ColorInfo[] $cia */
		return /*self::softmaxNeg*/(df_sort(array_map(
			function(array $c) use($cia) {return self::dist($cia, $c);}, self::palette()
		)));
	});}

	/**
	 * 2019-08-22
	 * @param string $path
	 */
	function __construct($path) {
		dfcf(function() {
			// 2019-08-21
			// https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.107.1/guides/authentication
			// https://github.com/googleapis/google-auth-library-php/tree/v1.5.2#application-default-credentials
			putenv('GOOGLE_APPLICATION_CREDENTIALS=' . dirname(BP) . '/doc/credentials.json');
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
	 * @param int[] $c
	 * @return float
	 */
	private static function dist(array $cia, array $c) {
		$r = 0;
		$slice = 1;
		if ($slice) {
			$cia = array_slice($cia, 0, $slice);
		}
		$useTones = true;
		if (!$useTones) {
			foreach ($cia as $ci) { /** @var ColorInfo $ci */
				$co = $ci->getColor(); /** @var Color $co */
				$r +=
					//$ci->getScore()
					//* $ci->getPixelFraction() //* $ci->getPixelFraction()
					Diff::p($c, [$co->getRed(), $co->getGreen(), $co->getBlue()])// / ($ci->getScore())
					/// ($ci->getScore() * $ci->getPixelFraction())
					//*
					//self::distSimple($c, [$co->getRed(), $co->getGreen(), $co->getBlue()])
					// self::distLum($c, [$co->getRed(), $co->getGreen(), $co->getBlue()])
					/** 0.5 * (
						self::distSimple($c, [$co->getRed(), $co->getGreen(), $co->getBlue()])
						+ Diff::p($c, [$co->getRed(), $co->getGreen(), $co->getBlue()])
					) */
				;
			}
		}
		else {
			$tones = self::tones($c, 10); /** @var int[][] $tones */
			$ci = $cia[0]; /** @var ColorInfo $ci */
			$r = min(df_map($tones, function($tone) use($ci) {
				$co = $ci->getColor(); /** @var Color $co */
				return Diff::p($tone, [$co->getRed(), $co->getGreen(), $co->getBlue()]);
			}));
		}
		return $r;
	}

	/**
	 * 2019-08-21
	 * @param int[] $a
	 * @param int[] $b
	 * @return int
	 */
	private static function distLum(array $a, array $b) {return abs(self::lum($a) - self::lum($b));}

	/**
	 * 2019-08-21
	 * @param int[] $a
	 * @param int[] $b
	 * @return int
	 */
	private static function distSimple(array $a, array $b) {return
		abs($a[0] - $b[0]) + abs($a[1] - $b[1]) + abs($a[2] - $b[2])
	;}

	/**
	 * 2019-08-23
	 * @used-by tones()
	 * @param float $v
	 * @return int
	 */
	private static function limit255($v) {return round(max(0, min(255, $v)));}

	/**
	 * 2019-08-21
	 * @param int[] $a
	 * @param int[] $b
	 * @return int
	 */
	private static function lum(array $a) {return (int)(0.2126 * $a[0] + 0.7152 * $a[1] + 0.0722 * $a[2]);}

	/**
	 * 2019-08-22
	 * @used-by optsM()
	 * @used-by palette()
	 * @return array(array(string => int|string))
	 */
	private static function opts() {return dfcf(function() {return
		df_product_att('color')->getSource()->getAllOptions(false)
	;});}

	/**
	 * 2019-08-22
	 * @used-by labels()
	 * @return array(int => string)
	 */
	private static function optsM() {return dfcf(function() {return
		array_map('strtolower', array_column(self::opts(), 'label', 'value'))
	;});}

	/**
	 * 2019-08-22
	 * @used-by optsM()
	 * @return array(array(string => int|string))
	 */
	private static function palette() {return dfcf(function() {return array_map('df_hex2rgb', array_column(
		df_swatches_h()->getSwatchesByOptionsId(df_int(array_column(self::opts(), 'value')))
		,'value', 'option_id'
	));});}

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

	/**
	 * 2019-08-23
	 * @used-by dist()
	 * @param int[] $c
	 * @param int $count
	 * @return int[][]
	 */
	private static function tones($c, $count) {
		$dNeg = [];
		$dPos = [];
		$rNeg = [];
		$rPos = [];
		for ($j = 0; $j < 3; $j++) {
			// 2019-08-23 I use `$count + 1` because the last tone is just pure white or black, we do not need it.
			$dNeg[$j] = $c[$j] / ($count + 1);
			$dPos[$j] = (255 - $c[$j]) / ($count + 1);
		}
		for ($i = 0; $i < $count; $i++) {
			for ($j = 0; $j < 3; $j++) {
				$rNeg[$i][$j] = self::limit255($c[$j] - ($dNeg[$j] * $i + 1));
				$rPos[$i][$j] = self::limit255($c[$j] + ($dPos[$j] * $i + 1));
			}
		}
		return array_merge($rNeg, [$c], $rPos);
	}
}