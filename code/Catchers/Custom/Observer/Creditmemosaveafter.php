<?php

namespace Catchers\Custom\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

use Psr\Log\LoggerInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;


class Creditmemosaveafter implements ObserverInterface
{
    protected $_request;
    protected $_layout;
    protected $_objectManager = null;
    protected $creditmemoFactory;

     /**
     * @var CreditmemoRepositoryInterface
     */
    private $creditmemoRepository;
 
    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
         CreditmemoRepositoryInterface $creditmemoRepository,
        LoggerInterface $logger
    ) {
        $this->_layout = $context->getLayout();
        $this->_request = $context->getRequest();
        $this->_objectManager = $objectManager;
        $this->creditmemoFactory = $creditmemoFactory;
        $this->creditmemoRepository = $creditmemoRepository;
        $this->logger = $logger;
    }

    /**
    * @param \Magento\Framework\Event\Observer $observer
    * @return void
    */
    public function execute(EventObserver $observer)
    {
        // echo "<pre>.............";

        $adjustAmount = $observer->getEvent()->getCreditmemo()->getAdjustment();
        $taxAmount = $observer->getEvent()->getCreditmemo()->getTaxAmount();
        $grandTotal = $observer->getEvent()->getCreditmemo()->getGrandTotal();
        $creditId = $observer->getEvent()->getCreditmemo()->getId();
        $order = $observer->getEvent()->getCreditmemo()->getOrder();
        $taxRate = 0;
        foreach ($order->getAllItems() as $item) {
            // print_r($item->getData());
            $taxRate = $item->getTaxPercent();
            // exit;
            break;
        }
     
     // echo "order";exit;
        /*echo "adjustAmount::" .$adjustAmount;
        echo "taxAmount::"    .$taxAmount;
        echo "credit memo--"  .$creditId;*/

        $creditmemo = NULL;

        if( $adjustAmount > 0 && $taxAmount == 0 && $taxRate > 0 ){
            // $taxAmountFinal = $adjustAmount*0.21;
            $taxAmountFinal = ($adjustAmount*$taxRate)/100;
            $grandTotalFinal = $grandTotal + $taxAmountFinal;
            //$taxAmountValue;
            $creditmemo = $this->creditmemoRepository->get($creditId);
            // print_r($creditmemo->getData());exit;
            $creditmemo->setTaxAmount($taxAmountFinal);
            $creditmemo->setGrandTotal($grandTotalFinal);
            $creditmemo->setBaseGrandTotal($grandTotalFinal);
            $creditmemo->save();
        }

       
        return $creditmemo;

        
    }
}

