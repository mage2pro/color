<?php
namespace Dfe\Color\Controller\Index;
use Magento\Framework\App\Action\Action as _P;
use Magento\Framework\Controller\ResultFactory as F;
use Magento\Framework\View\Result\Page;
# 2019-08-22
class Index extends _P {
	/**    
	 * 2019-08-22
	 * @override
	 * @see _P::execute()    
	 * @used-by \Magento\Framework\App\Action\Action::dispatch():
	 * 		$result = $this->execute();
	 * https:#github.com/magento/magento2/blob/2.2.1/lib/internal/Magento/Framework/App/Action/Action.php#L84-L125
	 */
	function execute():Page {return $this->resultFactory->create(F::TYPE_PAGE);}
}