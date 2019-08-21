<?php
namespace Dfe\Color\Observer;
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
		/**
		 * 2019-08-21
		 * A deleted image has `removed = 1` property here, e.g.:
		 *	{
		 *		"disabled": "0",
		 *		"disabled_default": "0",
		 *		"entity_id": "67",
		 *		"file": "//w/w/wwfhnfv49ii.jpg",
		 *		"label": "",
		 *		"media_type": "image",
		 *		"position": "3",
		 *		"position_default": "3",
		 *		"removed": "1",
		 *		"role": "",
		 *		"value_id": "414",
		 *		<...>
		 *	}
		 * $o['product']['media_gallery']['images']
		 */
		$p = $o['product']; /** @var P $p */
	}
}