<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.41
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\RewardsAdminUi\Controller\Adminhtml\Spending\Rule;

use Magento\Framework\Controller\ResultFactory;

class Add extends \Mirasvit\RewardsAdminUi\Controller\Adminhtml\Spending\Rule
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initSpendingRule();

        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('New Spending Rule'));

        return $resultPage;
    }

}
