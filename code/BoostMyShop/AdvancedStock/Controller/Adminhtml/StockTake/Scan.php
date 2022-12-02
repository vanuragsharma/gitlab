<?php namespace BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake;

/**
 * Class Scan
 *
 * @package   BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Scan extends \BoostMyShop\AdvancedStock\Controller\Adminhtml\StockTake {

    public function execute(){

        try {

            $this->_registerCurrentStockTake();

            $location = filter_var($this->getRequest()->getParam('location'), FILTER_SANITIZE_STRING);

            if ($location) {
                $this->_coreRegistry->register('current_stock_take_location', $location);
            }

            $this->_view->loadLayout();
            if (!$location)
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Scan Products'));
            else
                $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Scan Products for location "%1"', $location));
            $this->_view->renderLayout();

        }catch(\Exception $e){

            $this->messageManager->addErrorMessage(__('An error occurred : '.$e->getMessage()));
            return $this->resultRedirectFactory->create()->setPath('advancedstock/stocktake/index');

        }

    }

}