<?php 

namespace Yalla\Theme\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class ProcessOrder implements ObserverInterface
{
	protected $quoteRepository;

    public function __construct(
    	\Magento\Quote\Model\QuoteRepository $quoteRepository
    )
    {
        $this->quoteRepository = $quoteRepository; 
    }
	public function execute(Observer $observer)
	{

		$order = $observer->getOrder();
	    $quote = $observer->getQuote();
	    $quote_gift = $quote->getData('giftwrap');
	    
	    $order->setData('giftwrap',$quote_gift);// Fill data
        $order->save();

	    return $this;
	}
}