<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Ui\Component\Listing\Column;
class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    const URL_PATH_EDIT = 'event/data/AddRow';
    const URL_PATH_DELETE = 'event/data/delete';
    /**
     * URL builder
     * 
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * constructor
     * 
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->_urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }


    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
         if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                    $item[$this->getData('name')] = [
                        'edit' => [
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'e_id' => $item['e_id']
                                    
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete'=>[
                            'href' => $this->_urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'e_id' => $item['e_id']
                                ]
                            ),
                            
                            'label' => __('Delete'),
                            'confirm' => [
                            'title' => __('Delete items'),
                            'message' => __('Are you sure you wan\'t to Delete selected items?')
                           ]




                        ],
                    ];
            }
        }
        
        return $dataSource;
    }
}