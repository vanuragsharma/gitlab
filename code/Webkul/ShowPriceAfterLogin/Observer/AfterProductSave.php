<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ShowPriceAfterLogin
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ShowPriceAfterLogin\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterProductSave implements ObserverInterface
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var \Magento\Framework\EntityManager\EntityManager
     */
    protected $entityManager;

    /**
     * __construct function
     *
     * @param \Magento\Catalog\Model\ProductFactory            $productFactory
     * @param \Magento\Backend\Model\Session                   $session
     * @param \Magento\Framework\App\RequestInterface          $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository
     * @param \Magento\Framework\EntityManager\EntityManager   $entityManager
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Model\Session $session,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\EntityManager\EntityManager $entityManager
    ) {
        $this->productFactory = $productFactory;
        $this->session = $session;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }
    /**
     * Observer to add the value to the attribute of product for ShowPriceAfterLogin
     * module after product save store wise.
     */
    public function execute(Observer $observer)
    {
        $productId = $observer->getProduct()->getEntityId();
        $storeId = $this->request->getParam('store');
        if (!$storeId) {
            $storeId = 0;
        }
        $product = $this->productRepository->getById($productId, true, $storeId);
        $sessionValue = $this->session->getCustomerGroupProductAttributeValue();
        if ($sessionValue) {
            $values = implode(',', $this->session->getCustomerGroupProductAttributeValue());
            $product->setShowPriceCustomerGroup($values);
            $product->setStoreId($storeId);
            $this->entityManager->save($product);
            $this->session->setCustomerGroupProductAttributeValue(null);
        }
    }
}
