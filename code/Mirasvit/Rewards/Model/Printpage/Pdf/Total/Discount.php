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



namespace Mirasvit\Rewards\Model\Printpage\Pdf\Total;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Mirasvit\Rewards\Helper\Purchase as PurchaseHelper;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation as TaxCalculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory as OrdersFactory;
use Mirasvit\Rewards\Service\Order\Transaction\CancelEarnedPoints;

class Discount extends DefaultTotal
{
    private $purchaseHelper;

    private $cancelEarnedPointsService;

    public function __construct(
        PurchaseHelper     $purchaseHelper,
        TaxHelper          $taxHelper,
        TaxCalculation     $taxCalculation,
        OrdersFactory      $ordersFactory,
        CancelEarnedPoints $cancelEarnedPointsService,
        array              $data = []
    ) {
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);

        $this->purchaseHelper            = $purchaseHelper;
        $this->cancelEarnedPointsService = $cancelEarnedPointsService;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        $creditMemo        = $this->getSource();
        $order             = $creditMemo->getOrder();
        $orderRefundAmount = 0;

        if (!$order instanceof \Magento\Sales\Model\Order) {
            return 0;
        }

        if ($order->getSubtotal() > 0) {
            $proportion = $creditMemo->getSubtotal() / $order->getSubtotal();
        } else { // for zero orders with earning points
            $proportion = $this->cancelEarnedPointsService->getCreditmemoItemsQty($creditMemo) /
                $this->cancelEarnedPointsService->getCreditmemoOrderItemsQty($creditMemo);
        }

        if ($proportion > 1) {
            $proportion = 1;
        }

        $purchase = $this->purchaseHelper->getByOrder($this->getOrder());

        if ($purchase && $purchase->getSpendAmount() > 0) {
            $orderRefundAmount = $purchase->getSpendAmount();
        }

        $creditMemoRefundAmount = round($orderRefundAmount * $proportion, 2);

        return $creditMemoRefundAmount;
    }
}
