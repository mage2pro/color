<?php
namespace Dfe\Color\Plugin\Swatches\Block\Adminhtml\Attribute\Edit\Options;
use Dfe\Color\Setup\UpgradeSchema as Schema;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute as A;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection as OC;
use Magento\Framework\Data\Collection as C;
use Magento\Store\Model\Store;
use Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\AbstractSwatch as Sb;
// 2019-08-23
final class AbstractSwatch {
	/**
	 * 2019-08-23
	 * @param Sb $sb
	 * @param \Closure $f
	 * @param int $sid
	 * @return mixed[]
	 */
	function aroundGetStoreOptionValues(Sb $sb, \Closure $f, $sid) {
		/** @var mixed[] $r */
		if ($sid) {
			$r = $f($sid);
		}
		else if (is_null($r = $sb["store_option_values_$sid"])) {
			$r = [];
			$oc = df_new_om(OC::class); /** @var OC $oc */
			$a = df_registry('entity_attribute'); /** @var A $a */
			$oc->setAttributeFilter($a->getId());
			$this->addCollectionStoreFilter($oc, $sid);
			$oc->getSelect()->joinLeft(
				['swatch_table' => $oc->getTable('eav_attribute_option_swatch')],
				'swatch_table.option_id = main_table.option_id AND swatch_table.store_id = ' . $sid,
				['label' => 'swatch_table.value', Schema::F => 'swatch_table.' . Schema::F]
			);
			$oc->load();
			foreach ($oc as $item) {
				$r[$item->getId()] = $item->getValue();
				$r['swatch'][$item->getId()] = $item->getLabel();
				$r[Schema::F][$item->getId()] = df_eta(df_json_decode($item[Schema::F]));
			}
			$sb["store_option_values_$sid"] = $r;
		}
		return $r;
	}

	/**
	 * 2019-08-23
	 * @used-by aroundGetStoreOptionValues()
	 * @see \Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\AbstractSwatch::addCollectionStoreFilter()
	 * @param OC $oc
	 * @param int $storeId
	 * @return void
	 */
	private function addCollectionStoreFilter(OC $oc, $storeId) {
		$s = $oc->getSelect();
		$s->joinLeft(
			['tsv' => $oc->getTable('eav_attribute_option_value')]
			,$oc->getConnection()->quoteInto(
				'tsv.option_id = main_table.option_id AND tsv.store_id = ?', $storeId
			)
			,'value'
		);
		if (Store::DEFAULT_STORE_ID == $storeId) {
			$s->where('tsv.store_id = ?', $storeId);
		}
		$oc->setOrder('value', C::SORT_ORDER_ASC);
	}
}