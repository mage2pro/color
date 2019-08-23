<?php
namespace Dfe\Color\Observer;
use Dfe\Color\Image;
use Magento\Catalog\Model\Product as P;
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
		if (!df_product_type_composite($p = $o['product']) && $p['color'] === $p->getOrigData('color')) {
			/** @var P $p */
			// 2019-08-21 A new image path can start with `//` because of a Magento 2 core bug.
			/** @var string $path */
			$path = df_trim_text_right(df_path_n($p['image']), '.tmp');
			if ($path !== df_path_n($p->getOrigData('image'))) {
				$image = new Image(df_product_image_path_absolute($path)); /** @var Image $image */
				$p['color'] = df_first_key($image->probabilities());
			}
		}
	}
}