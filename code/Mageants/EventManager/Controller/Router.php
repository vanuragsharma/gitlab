<?php
/**
* @category Mageants EventManager
* @package Mageants_EventManager
* @copyright Copyright (c) 2019 Mageants
* @author Mageants Team <support@mageants.com>
*/
namespace Mageants\EventManager\Controller;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    public $actionFactory;
 
    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    public $response;
 
    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\ResponseInterface $response,
        \Mageants\EventManager\Helper\Data $helperData,
        \Mageants\EventManager\Model\Eventdata $Eventdata
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->helperData = $helperData;
        $this->_Eventdata = $Eventdata;
    }
 
    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {   
        
        
        $eventurls=$this->_Eventdata->getCollection()->addFieldToSelect('urlprefix')->getData();

        $route = $this->helperData->getConfigValue('event/seoconfigration/urlprefix')
        .$this->helperData->getConfigValue('event/seoconfigration/urlsufix');
        $identifierUrl = str_replace($route."/", "", trim($request->getPathInfo(), '/'));
        if (str_replace("/", "", $request->getRequestString())== $route) {
            
            $request->setModuleName('eventsmanager')
                ->setControllerName('index')
                ->setActionName('index');

        } elseif (str_replace("/", "", $request->getRequestString())== $route) {
            
            $request->setModuleName('eventsmanager')
                ->setControllerName('index')
                ->setActionName('view');
                /*->setParam('id', $return_val);*/

        } else {
            return false;
        }

        return $this->actionFactory->create(
            'Magento\Framework\App\Action\Forward',
            ['request' => $request]
        );
    }

    public function getEventUrls($identifier)
    {
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $model = $_objectManager->create('Mageants\EventManager\Model\Eventdata');
        $eventurls=$model->getCollection()->getData();
        foreach ($eventurls as $eventurl) {

            $identifier= stripslashes(str_replace(".html", "", $identifier));
            if (str_replace("/", "", $identifier)== $eventurl['urlprefix']) {
                return $eventurl['e_id'];
            }
        }
    }
}
