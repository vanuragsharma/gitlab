<?php

namespace Yalla\Apis\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;

class Brands implements \Yalla\Apis\Api\BrandsInterface
{
    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var categoryCollection
     */
    protected $_categoryCollection;
    
	protected $request;
	
	protected $objectManager;
	
	protected $helper;
	
    /**
     * @param CollectionFactory $wishlistCollectionFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
    	\Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        CategoryCollection $categoryCollection,
		\Magento\Framework\App\Request\Http $request,
		\Yalla\Apis\Helper\Data $helper
    ) {
    	$this->categoryRepository = $categoryRepository;
        $this->_categoryCollection = $categoryCollection;
		$this->request = $request;
        $this->helper = $helper;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->brand_helper = $this->objectManager->create('Mageplaza\Shopbybrand\Helper\Data');
        $this->character_helper = $this->objectManager->create('Mageplaza\Shopbycharacter\Helper\Data');
    }
    
    /**
     * Returns all brands
     *
     * @return array
     */
    public function getAll()
    {
        
		$lang = $this->request->getParam('lang');

		if(!empty($lang)){
			$this->helper->setStore($lang);
		}
		
        $char = array_unique(
            explode(',', str_replace(' ', '', $this->brand_helper->getBrandConfig('brand_filter/alpha_bet')))
        );

        /*
         * remove empty  field in array
         */
        foreach ($char as $offset => $row) {
            if (trim($row) === '') {
                unset($char[$offset]);
            }
        }

        /*
         * set default alphabet if leave alphabet config blank
         */
        if (empty($char)) {
            $char = [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
                'K',
                'L',
                'M',
                'N',
                'O',
                'P',
                'Q',
                'R',
                'S',
                'T',
                'U',
                'V',
                'W',
                'X',
                'Y',
                'Z'
            ];
        }

		$categoryId = 33; // Brand ID
		$category = $this->categoryRepository->get($categoryId);

        if ($category->hasChildren()) {
            $brands = $category->getChildren();
            $categoryIds = explode(',', $brands);
        }
        
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->_categoryCollection->create()->addAttributeToSelect(
                '*'
        )->addAttributeToFilter(
                'is_active',
                1
        )->addIdFilter(
                $category->getChildren()
        )->addAttributeToSort('name');

		$media_url = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);


        $categories = [];
        $alphaBet    = [];
        $activeChars = [];

        foreach ($collection as $subCat) {
            
            $child = $this->objectManager->create('Magento\Catalog\Model\Category')->load($subCat->getId());
            /*$categories[$subCat->getId()] = [
                'id' => (string) $subCat->getId(),
                'name' => $subCat->getName(),
                'product_count' => 0,
                'image' => ''
            ];*/
            
            $firstChar = mb_substr($subCat->getName(), 0, 1, 'UTF-8');
            
            if (!in_array($firstChar, $activeChars, true)) {
                $activeChars[] = $firstChar;
            }
            
            $image = $child->getBrandLogo();
            if($image){
            	$image = $media_url . 'catalog/category/' . $image;
            }else{
            	$image = '';
            }
            if($image){
		        if(isset($categories[$firstChar])){
		        	$categories[$firstChar] = array_merge($categories[$firstChar], array([
				        'id' => (string) $subCat->getId(),
				        'name' => $subCat->getName(),
				        'product_count' => $subCat->getProductCount(),
				        'image' => $image
				    ]));
		        }else{
		        	$categories[$firstChar][] = [
				        'id' => (string) $subCat->getId(),
				        'name' => $subCat->getName(),
				        'product_count' => $subCat->getProductCount(),
				        'image' => $image
				    ];
		        }
            }
        }
        
        //$categories = array_values($categories);
        
        
        foreach ($collection as $brand) {
            $firstChar = mb_substr($brand->getValue(), 0, 1, 'UTF-8');
            
            if (!in_array($firstChar, $activeChars, true)) {
                $activeChars[] = $firstChar;
            }
        }

        $activeChars = $this->brand_helper->convertUppercase($activeChars);

        foreach ($char as $item) {
            $alphaBet[] = [
                'char'   => $item,
                'active' => in_array($item, $activeChars, true)
            ];
        }
        
        $data = [
        	'alphaBet' => $alphaBet,
        	'brands' => $categories
        ];
        echo json_encode(['success' => 'true', 'msg' => __('Success'), 'collection' => $data]);
        exit;
    }
    
    /**
     * Returns all characters
     *
     * @return array
     */
    public function getAllCharacters()
    {
        
		$lang = $this->request->getParam('lang');
		if(!empty($lang)){
			$this->helper->setStore($lang);
		}
		
        $char = array_unique(
            explode(',', str_replace(' ', '', $this->character_helper->getCharacterConfig('character_filter/alpha_bet')))
        );

        /*
         * remove empty  field in array
         */
        foreach ($char as $offset => $row) {
            if (trim($row) === '') {
                unset($char[$offset]);
            }
        }

        /*
         * set default alphabet if leave alphabet config blank
         */
        if (empty($char)) {
            $char = [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
                'K',
                'L',
                'M',
                'N',
                'O',
                'P',
                'Q',
                'R',
                'S',
                'T',
                'U',
                'V',
                'W',
                'X',
                'Y',
                'Z'
            ];
        }

		$categoryId = 105; // Brand ID
		$category = $this->categoryRepository->get($categoryId);

        if ($category->hasChildren()) {
            $brands = $category->getChildren();
            $categoryIds = explode(',', $brands);
        }
        
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->_categoryCollection->create()->addAttributeToSelect(
                '*'
        )->addAttributeToFilter(
                'is_active',
                1
        )->addIdFilter(
                $category->getChildren()
        )->addAttributeToSort('name');

		$media_url = $this->objectManager->get('Magento\Store\Model\StoreManagerInterface')
                ->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);


        $categories = [];
        $alphaBet    = [];
        $activeChars = [];

        foreach ($collection as $subCat) {
            
            $child = $this->objectManager->create('Magento\Catalog\Model\Category')->load($subCat->getId());
            /*$categories[$subCat->getId()] = [
                'id' => (string) $subCat->getId(),
                'name' => $subCat->getName(),
                'product_count' => 0,
                'image' => ''
            ];*/
            
            $firstChar = mb_substr($subCat->getName(), 0, 1, 'UTF-8');
            
            if (!in_array($firstChar, $activeChars, true)) {
                $activeChars[] = $firstChar;
            }
            
            $image = $child->getBrandLogo();
            if($image){
            	$image = $media_url . 'catalog/category/' . $image;
            }else{
            	$image = '';
            }
            if($image){
		        if(isset($categories[$firstChar])){
		        	$categories[$firstChar] = array_merge($categories[$firstChar], array([
				        'id' => (string) $subCat->getId(),
				        'name' => $subCat->getName(),
				        'product_count' => $subCat->getProductCount(),
				        'image' => $image
				    ]));
		        }else{
		        	$categories[$firstChar][] = [
				        'id' => (string) $subCat->getId(),
				        'name' => $subCat->getName(),
				        'product_count' => $subCat->getProductCount(),
				        'image' => $image
				    ];
		        }
            }
        }
        
        //$categories = array_values($categories);
        
        
        foreach ($collection as $brand) {
            $firstChar = mb_substr($brand->getValue(), 0, 1, 'UTF-8');
            
            if (!in_array($firstChar, $activeChars, true)) {
                $activeChars[] = $firstChar;
            }
        }

        $activeChars = $this->brand_helper->convertUppercase($activeChars);

        foreach ($char as $item) {
            $alphaBet[] = [
                'char'   => $item,
                'active' => in_array($item, $activeChars, true)
            ];
        }
        
        $data = [
        	'alphaBet' => $alphaBet,
        	'characters' => $categories
        ];
        echo json_encode(['success' => 'true', 'msg' => __('Success'), 'collection' => $data]);
        exit;
    }
    
}
