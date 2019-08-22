<?php
namespace Dfe\Color;
use Google\Cloud\Vision\V1\AnnotateImageResponse as Res;
use Google\Cloud\Vision\V1\ColorInfo;
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
		$a = new Annotator; /** @var Annotator $a */
		try {$res = $a->imagePropertiesDetection(file_get_contents($this->_path)); /** @var Res $res */}
		finally {$a->close();}
		/** @var ColorInfo[] $cia */
		$cia = iterator_to_array($res->getImagePropertiesAnnotation()->getDominantColors()->getColors());
		return self::softmaxNeg(df_sort(array_map(
			function(array $c) use($cia) {return self::dist($cia, $c, null);}, self::palette()
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
	 * @used-by __construct()
	 * @used-by probabilities()
	 * @var string
	 */
	private $_path;

	/**
	 * 2019-08-21
	 * @used-by probabilities()
	 * @param ColorInfo[] $cia
	 * @param int[] $c
	 * @param int|null $slice [optional]
	 * @return int
	 */
	private static function dist(array $cia, array $c, $slice = null) {
		$r = 0;
		if ($slice) {
			$cia = array_slice($cia, $slice);
		}
		foreach ($cia as $ci) { /** @var ColorInfo $ci */
			$co = $ci->getColor(); /** @var Color $co */
			$r += $ci->getScore() * Diff::p($c, [$co->getRed(), $co->getGreen(), $co->getBlue()]);
		}
		return $r;
	}

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
		$f = 100 / array_sum($a); /** @var float $f */
        return array_map(function ($v) use ($f) {return $v * $f;}, $a);
	}
}