<?php
namespace Dfe\Color\Observer;
use Dfe\Color\Diff;
use Google\Cloud\Vision\V1\AnnotateImageResponse as Res;
use Google\Cloud\Vision\V1\ColorInfo;
use Google\Cloud\Vision\V1\ImageAnnotatorClient as Annotator;
use Google\Type\Color;
use Magento\Catalog\Model\Product as P;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as Att;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
// 2019-08-21
final class ProductSaveBefore implements ObserverInterface {
	/**
	 * 2019-08-21
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @used-by \Magento\Framework\Model\AbstractModel::beforeSave():
	 * 		$this->_eventManager->dispatch($this->_eventPrefix . '_save_before', $this->_getEventData());
	 * @param O $o
	 */
	function execute(O $o) {
		if (!df_product_type_composite($p = $o['product'])) { /** @var P $p */
			// 2019-08-21 A new image path can start with `//` because of a Magento 2 core bug.
			$image = str_replace('//', '/', df_path_n($p['image'])); /** @var string $image */
			if ($image !== df_path_n($p->getOrigData('image'))) {
				// 2019-08-21
				// https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.107.1/guides/authentication
				// https://github.com/googleapis/google-auth-library-php/tree/v1.5.2#application-default-credentials
				putenv('GOOGLE_APPLICATION_CREDENTIALS=' . dirname(BP) . '/doc/credentials.json');
				$a = new Annotator; /** @var Annotator $a */
				/** @var Res $res */
				try {$res = $a->imagePropertiesDetection(file_get_contents(df_product_image_path_absolute($image)));}
				finally {$a->close();}
				/** @var ColorInfo[] $cia */
				$cia = iterator_to_array($res->getImagePropertiesAnnotation()->getDominantColors()->getColors());
				/** @var Color $co */
				$p['color'] = df_first_key(
					df_sort(
						array_map(
							function(array $c) use($cia) {return self::dist4($cia, $c, null);}
							,array_map(
								'df_hex2rgb'
								,array_column(
									df_swatches_h()->getSwatchesByOptionsId(
										df_int(
											array_column(
												df_product_att('color')->getSource()->getAllOptions(false)
												,'value'
											)
										)
									)
									,'value', 'option_id'
								)
							)
						)
					)
				);
			}
		}
	}

	/**
	 * 2019-08-21
	 * @param int[] $a
	 * @param int[] $b
	 * @return int
	 */
	private static function dist(array $a, array $b) {return
		abs($a[0] - $b[0]) + abs($a[1] - $b[1]) + abs($a[2] - $b[2])
	;}

	/**
	 * 2019-08-21
	 * @param int[] $a
	 * @param int[] $b
	 * @return int
	 */
	private static function dist2(array $a, array $b) {return
		sqrt(($a[0] - $b[0]) ** 2 + ($a[1] - $b[1]) ** 2 + ($a[2] - $b[2]) ** 2)
	;}

	/**
	 * 2019-08-21
	 * @param int[] $a
	 * @param int[] $b
	 * @return int
	 */
	private static function dist3(array $a, array $b) {return abs(self::lum($a) - self::lum($b));}

	/**
	 * 2019-08-21
	 * @param ColorInfo[] $cia
	 * @param int[] $c
	 * @param int|null $slice [optional]
	 * @return int
	 */
	private static function dist4(array $cia, array $c, $slice = null) {
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
	 * 2019-08-21
	 * @param int[] $a
	 * @param int[] $b
	 * @return int
	 */
	private static function lum(array $a) {return (int)(0.2126 * $a[0] + 0.7152 * $a[1] + 0.0722 * $a[2]);}
}