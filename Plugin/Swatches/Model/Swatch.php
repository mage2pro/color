<?php
namespace Dfe\Color\Plugin\Swatches\Model;
use Dfe\Color\Plugin\Catalog\Model\ResourceModel\Eav\Attribute as Plugin;
use Dfe\Color\Setup\UpgradeSchema as Schema;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as A;
use Magento\Swatches\Model\Swatch as Sb;
// 2019-08-23
final class Swatch {
	/**
	 * 2019-08-23
	 * @see \Magento\Swatches\Model\Plugin\EavAttribute::saveSwatchData()
	 */
	function beforeBeforeSave(Sb $sb):void {
		if (
			($a = Plugin::a()) /** @var A $a */
			// 2019-08-23 The `[]` syntax does not support `/`, so I use `getData()`.
			 /** @var array(int => array(int => string)) $d */
			&& ($d = df_eta($a->getData('swatchvisual/' . Schema::F)))
			&& ($v = dfa($d, $sb['option_id']))  /** @var array(int => string) $v */
		) {
			$sb[Schema::F] = df_json_encode($v);
		}
	}
}