<?php

namespace Sas\AdvanceShipping\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 * Advance shipping model
 */
class Advanceshipping extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'advanceshipping';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    private $rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    private $rateMethodFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param RateRequest $request
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerGroup = $this->getConfigData('customergroup');

        if($customerGroup == ""){
            return false;
        }
        //echo $customerGroup;
        $customerGroup = explode(",", $customerGroup);
        
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');

        if($customerSession->isLoggedIn()) {
            $groupId = $customerSession->getCustomer()->getGroupId();
            if(!in_array($groupId, $customerGroup)) {
                return false;
            }
            
        }else{
            if(!in_array("0", $customerGroup)) {
                return false;
            }
        }


        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $percentage = (float)$this->getConfigData('shipping_cost');

        
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');
        //$subTotal = $cart->getQuote()->getSubtotal();

        $items = $cart->getQuote()->getAllItems();
        $subTotal = 0;
        foreach($items as $item) {
            $productId =  $item->getProductId();
            $product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
            $ignoreFromShipping = $product->getData('ignore_from_shipping');
            $shippingPrice = $product->getData('shipping_price');
            if(!$ignoreFromShipping){
                if($shippingPrice){
                    $subTotal += $shippingPrice;
                }else{
                    $subTotal += $item->getRowTotal();
                }
            }
        }
        if($subTotal == 0){
            return false;
        }
        $shippingCost = ($percentage / 100) * $subTotal;

        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);

        

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

}