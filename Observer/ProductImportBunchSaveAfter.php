<?php
namespace Dfe\Color\Observer;
use Dfe\Color\Image;
use Magento\Catalog\Model\Product as P;
use Magento\Catalog\Model\ResourceModel\Product\Action;
use Magento\CatalogImportExport\Model\Import\Product as Import;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
// 2019-09-20
// "The color analysis is not triggered when an image is imported via CSV":
// https://github.com/mage2pro/color/issues/2
final class ProductImportBunchSaveAfter implements ObserverInterface {
	/**
	 * 2019-09-20
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @used-by \Magento\CatalogImportExport\Model\Import\Product::_saveProducts():
	 * 		$this->_eventManager->dispatch('catalog_product_import_bunch_save_after', [
	 * 			'adapter' => $this, 'bunch' => $bunch
	 * 		]);
	 * https://github.com/magento/magento2/blob/2.3.2/app/code/Magento/CatalogImportExport/Model/Import/Product.php#L1979-L1982
	 * @used-by \Firebear\ImportExport\Model\Import\Product::saveProducts():
	 * 		$this->_eventManager->dispatch('catalog_product_import_bunch_save_after', [
	 * 			'adapter' => $this, 'bunch' => $nextBunch
	 * 		]);
	 * @param O $o
	 */
	function execute(O $o) {
		$adapter = $o['adapter']; /** @var Import $adapter */
		$sep = $adapter->getMultipleValueSeparator(); /** @var string $sep */
		$action = df_product_action(); /** @var Action $action */
		foreach(df_eta($o['bunch']) as $d) { /** @var array(string => string $d) */
			if (
				!isset($d['color']) && isset($d['base_image'])
				&& !df_product_type_composite($pBase = df_product_r()->get($sku = df_assert(dfa($d, 'sku'))))
			) {
				/** @var P $pBase */ /** @var string $sku */
				$image = new Image(df_product_image_path($pBase, 'image')); /** @var Image $image */
				$color = df_first_key($image->probabilities());	/** @var int $color */
				/**
				 * 2019-09-20
				 * @see \Magento\CatalogImportExport\Model\Import\Product::_saveProducts():
				 *	if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
				 *		$websiteCodes = explode(
				 * 			$this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES]
				 * 		);
				 *		foreach ($websiteCodes as $websiteCode) {
				 *			$websiteId = $this->storeResolver->getWebsiteCodeToId($websiteCode);
				 *			$this->websitesCache[$rowSku][$websiteId] = true;
				 *		}
				 *	}
				 * https://github.com/magento/magento2/blob/2.3.2/app/code/Magento/CatalogImportExport/Model/Import/Product.php#L1725-L1732
				 */
				;
				$storeIds = !($websitesS = dfa($d, Import::COL_PRODUCT_WEBSITES)) ? [null] :
					/** @var string|null $websitesS */
					array_unique(df_int(dfa_flatten(df_map(function($websiteC) use($sep) {return
						df_store_m()->getStoreByWebsiteId(df_ie_store_r()->getWebsiteCodeToId($websiteC))
					;}, explode($sep, $websitesS)))))
				; /** @var array(int|null) $storeIds */
				foreach ($storeIds as $storeId) { /** @var int|null $storeId */
					$action->updateAttributes([df_product_sku2id($sku)], ['color' => $color], $storeId);
				}
			}
		}
	}
}