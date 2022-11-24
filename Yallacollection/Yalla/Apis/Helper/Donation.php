<?php

namespace Yalla\Apis\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Donation extends AbstractHelper {

	protected $locale;
	protected $_dir;
	protected $_objectManager;
	
	/**
     * @var \MageWorx\Donations\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\Donations\Helper\Price
     */
    protected $helperPrice;
    
    /**
     * @var \MageWorx\Donations\Model\Donation
     */
    protected $modelDonation;
    
    /**
     * Escaper
     *
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    
    protected $quote;
    
    /**
     * @var SerializerInterface
     */
    protected $serializer;
	
	public function __construct(
		\MageWorx\Donations\Helper\Data $helperData,
        \MageWorx\Donations\Helper\Price $helperPrice,
        \MageWorx\Donations\Model\Donation $modelDonation,
        PriceCurrencyInterface $priceCurrency,
		SerializerInterface $serializer,
		\Magento\Framework\Escaper $escaper,
	    \Magento\Framework\Filesystem\DirectoryList $dir
	) {
        $this->helperData      = $helperData;
        $this->helperPrice     = $helperPrice;
        $this->modelDonation   = $modelDonation;
        $this->escaper         = $escaper;
        $this->priceCurrency   = $priceCurrency;
		$this->serializer     = $serializer;
	    $this->_dir = $dir;
	    $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	}
	
	public function applyDonation($quote, $amount, $remove = 0){
		$this->quote = $quote;
		$charity = 1;
		$data = array('donation' => $amount, 'charity' => $charity, 'pressedRoudUp' => false, 'pressedAddDonation' => "true");
		$donationRoundup  = $this->getRoundUp($data);
		$isCheckedRoundUp = $this->getIsUseDonationRoundUp();
		
		/* if press button deleteDonation or unchecked round up*/
		if($remove){
		    $isDelete = $this->deleteDonation($charity, $donationRoundup, $isCheckedRoundUp);
		    if ($isDelete) {
		        return ['result' => 'true'];
		    }
		}

        /* if press button addDonation or add round up*/
        return $this->addDonation($charity, $donationRoundup, $isCheckedRoundUp, $data);
		
	}
	
	protected function addDonation($charity, $donationRoundup, $isCheckedRoudUp, $data)
    {
        $isPressedButtonRoudUp      = $data['pressedRoudUp'];
        $isPressedButtonAddDonation = $data['pressedAddDonation'];
        $minDonations               = $this->helperData->getMinimumDonation();
        $donation                   = $this->getDonation($data);

        if ($minDonations < 0) {
            $message = __('Minimum donations should be more than 0');

            return [
                'result' => 'false',
                'error'  => $message
            ];
        }

        if ($isPressedButtonAddDonation === "true") {
            if (empty($donation)) {
                $message = __('Please specify a donation amount.');

                return [
                    'result' => 'false',
                    'error'  => $message
                ];
            }

            if ($donation < $minDonations) {
                $price   = $this->helperPrice->getFormatPrice($minDonations);
                $message =
                    __(
                        'Minimum accepted donation is %1',
                        $this->escaper->escapeHtml($price)
                    );

                return [
                    'result' => 'false',
                    'error'  => $message
                ];
            }

            $donationRoundup = (!$isCheckedRoudUp) ? 0 : $donationRoundup;
            // Save donation data with quote shipping address
            $address = $this->quote->getShippingAddress();
            $address->setMageworxDonationDetails($this->serializer->serialize(array(
        		"global_donation" => $donation,
        		"donation" => $donation,
        		"donation_roundup" => 0,
        		"isUseDonationRoundUp" => false,
        		"charity_id" => $charity,
        		"charity_title" => "Education Above All"
        	)));
            $address->save();
            $this->quote->collectTotals()->save();
            return ['result' => 'true'];
        }
        
        return ['result' => 'false'];
    }
    
    protected function deleteDonation($charity, $donationRoundup, $isCheckedRoudUp)
    {
        $address = $this->quote->getShippingAddress();
        $address->setMageworxDonationDetails('');
        $address->save();
        $this->quote->collectTotals()->save();

        return true;
    }
    
    protected function getDonation($data)
    {
        if (!empty($data['donation'])) {
            $donation = $this->helperPrice->convertToBaseCurrency($data['donation']);
        }

        if (empty($donation)) {
            $donation = $this->getValueDonation();
        }

        return $donation;
    }
    
    protected function getValueDonation()
    {
        $value = $this->modelDonation->getQuoteDetailsDonation();

        return !empty($value['donation']) ? $value['donation'] : 0;
    }
	
	/**
     * @param $data
     *
     * @return int
     */
    protected function getRoundUp($data)
    {
        if (!empty($data['donation_roundup'])) {
            $donationRoundup = $this->helperPrice->convertToBaseCurrency($data['donation_roundup']);
        }

        if (empty($donationRoundup)) {
        	$value = $this->modelDonation->getQuoteDetailsDonation();

        	$donationRoundup = !empty($value['donation_roundup']) ? $value['donation_roundup'] : 0;
        }

        return $donationRoundup;
    }
    
    protected function getIsUseDonationRoundUp()
    {
        $value = $this->modelDonation->getQuoteDetailsDonation();

        return !empty($value['isUseDonationRoundUp']) ? $value['isUseDonationRoundUp'] : false;
    }
}

?>
