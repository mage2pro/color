<?php
namespace Dfe\Color\Setup;
use Df\Framework\DB\ColumnType as T;
# 2019-08-23
/** @final Unable to use the PHP «final» keyword here because of the M2 code generation. */
class UpgradeSchema extends \Df\Framework\Upgrade\Schema {
	/**
	 * 2019-08-23
	 * @override
	 * @see \Df\Framework\Upgrade::_process()
	 * @used-by \Df\Framework\Upgrade::process()
	 */
	final protected function _process():void {
		if ($this->v('0.0.2')) {
			df_db_column_add('eav_attribute_option_swatch', self::F, T::textLong('[mage2pro/color] Additional swatches'));
		}
	}

	/**
	 * 2019-08-23
	 * @used-by self::_process()
	 * @used-by \Dfe\Color\Image::palette()
	 * @used-by \Dfe\Color\Plugin\Swatches\Block\Adminhtml\Attribute\Edit\Options\Visual::afterGetJsonConfig()
	 * @used-by \Dfe\Color\Plugin\Swatches\Model\Swatch::beforeBeforeSave()
	 * @used-by vendor/mage2pro/color/view/adminhtml/templates/catalog/product/attribute/visual.phtml
	 * @var string
	 */
	const F = 'mage2pro_color';
}