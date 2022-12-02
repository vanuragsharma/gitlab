<?php
namespace Catchers\CatalogImageGrid\Block\Category\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Grid of Category component
 */
class CategoryList extends Template implements \Magento\Widget\Block\BlockInterface
{
    const PAGE_SIZE = 5;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Category $category,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Magento\Framework\View\Asset\Repository $assetRepos,
        \Magento\Catalog\Helper\ImageFactory $helperImageFactory,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->storeManager = $context->getStoreManager();
        $this->category = $category;
        $this->categoryRepository = $categoryRepository;
        $this->conditionsHelper = $conditionsHelper;
        $this->assetRepos = $assetRepos;
        $this->helperImageFactory = $helperImageFactory;
        $this->imageFactory = $imageFactory;
        $this->_filesystem = $filesystem;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Get small place holder image
     *
     * @return string
     */
    public function getPlaceHolderImage()
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $imagePlaceholder = $this->helperImageFactory->create();
        return $this->assetRepos->getUrl($imagePlaceholder->getPlaceholder('image'));
    }
    /**
     * @param array $categoryIds
     * @return \Magento\Framework\Data\Collection
     */
    public function getCategory()
    {
        $ids = $this->getSubCategoryIds();
        if(!empty($ids)) {
            $category = [];
            foreach($ids as $id) {
                $category[] = $this->categoryRepository->get($id);
            }
            return $category;
        }
        return '';
    }

    /**
     * @param $imageName string imagename onlygetCategory
     * @param $width
     * @param $height
     */
    public function getResize($imageName,$width = 258,$height = 200)
    {
        $imageName = basename($imageName);
        $realPath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('catalog/category/'.$imageName);
        if (!$this->_directory->isFile($realPath) || !$this->_directory->isExist($realPath)) {
            return false;
        }
        $targetDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('resized/'.$width.'x'.$height);
        $pathTargetDir = $this->_directory->getRelativePath($targetDir);
        if (!$this->_directory->isExist($pathTargetDir)) {
            $this->_directory->create($pathTargetDir);
        }
        if (!$this->_directory->isExist($pathTargetDir)) {
            return false;
        }

        $image = $this->imageFactory->create();
        $image->open($realPath);
        $image->keepAspectRatio(true);
        $image->resize($width,$height);
        $dest = $targetDir . '/' . pathinfo($realPath, PATHINFO_BASENAME);
        $image->save($dest);
        if ($this->_directory->isFile($this->_directory->getRelativePath($dest))) {
            return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'resized/'.$width.'x'.$height.'/'.$imageName;
        }
        return false;
    }

    /**
     * Retrieve how many category should be displayed
     *
     * @return int
     */
    public function getCategoryToDisplay()
    {
        if (!$this->hasData('page_size')) {
            $this->setData('page_size', self::PAGE_SIZE);
        }
        return $this->getData('page_size');
    }

    /**
     * Retrieve category ids from widget
     *
     * @return string
     */
    public function getCategoryIds()
    {
        $conditions = $this->getData('conditions')
            ? $this->getData('conditions')
            : $this->getData('conditions_encoded');

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        foreach ($conditions as $key => $condition) {
            if (!empty($condition['attribute']) && $condition['attribute'] == 'category_ids')    {
                return $condition['value'];
            }
        }
        return '';
    }

    public function getSubCategoryIds() {
        $cat = $this->_registry->registry('current_category');
        $categoryIds = [];
        $parent_category_id = $cat->getId();
        $categoryObj = $this->categoryRepository->get($parent_category_id);
        $subcategories = $categoryObj->getChildrenCategories();
        foreach($subcategories as $subcategorie) {
            $categoryIds[] = $subcategorie->getId();
        }

        return $categoryIds;
    }
}