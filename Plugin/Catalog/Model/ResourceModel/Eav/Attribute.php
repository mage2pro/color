<?php
namespace Dfe\Color\Plugin\Catalog\Model\ResourceModel\Eav;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as Sb;
// 2019-08-23
final class Attribute {
	/**
	 * 2019-08-23
	 * @see \Magento\Swatches\Model\Plugin\EavAttribute::beforeBeforeSave()
	 * @see \Magento\Catalog\Model\ResourceModel\Eav\Attribute::beforeSave()
	 * @param Sb $sb
	 */
	function beforeBeforeSave(Sb $sb) {
		if (df_swatches_h()->isVisualSwatch($sb)) {
			self::$_a = $sb;
		}
	}

	/**
	 * 2019-08-23
	 * @used-by \Dfe\Color\Plugin\Swatches\Model\Swatch::beforeBeforeSave()
	 * @return Sb
	 */
	static function a() {return self::$_a;}

	/**
	 * 2019-08-23
	 * @used-by a()
	 * @used-by beforeBeforeSave()
	 * @var Sb
	 */
	private static $_a;
}