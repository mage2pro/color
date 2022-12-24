<?php
namespace Dfe\Color\Observer;
use Dfe\Color\Image;
use Magento\Catalog\Model\Product as P;
use Magento\Framework\App\Filesystem\DirectoryList as DL;
use Magento\Framework\Event\Observer as O;
use Magento\Framework\Event\ObserverInterface;
# 2019-08-21
final class ProductSaveBefore implements ObserverInterface {
	/**
	 * 2019-08-21
	 * @override
	 * @see ObserverInterface::execute()
	 * @used-by \Magento\Framework\Event\Invoker\InvokerDefault::_callObserverMethod()
	 * @used-by \Magento\Framework\Model\AbstractModel::beforeSave():
	 * 		$this->_eventManager->dispatch($this->_eventPrefix . '_save_before', $this->_getEventData());
	 */
	function execute(O $o):void {
		$p = $o['product']; /** @var P $p */
		if (
			# 2019-09-22 I have removed the `!df_product_type_composite($p)` condition.
			$p['color'] === $p->getOrigData('color')
			# 2019-08-21 A new image path can start with `#` because of a Magento 2 core bug.
			&& ($path = df_trim_text_right(df_path_n($p['image']), '.tmp')) !== df_path_n($p->getOrigData('image'))
		) {
			/** @var string $path */
			$full1 = df_product_image_path2abs($path); /** @var string $full1 */
			$full2 = df_product_image_tmp_path2abs($path); /** @var string $full1 */
			$image = new Image(file_exists($full1) ? $full1 : $full2); /** @var Image $image */
			$p['color'] = df_first_key($image->probabilities());
		}
	}
}