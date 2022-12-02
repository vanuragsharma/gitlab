<?php

namespace BoostMyShop\Supplier\Block\Widget\Form\Renderer;

use Magento\Framework\DataObject;

class Link extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $_assetRepo;


    public function getElementHtml()
    {
        $link = '<a href="'.$this->getData('url').'" target="_blank">'.$this->getlabel().'</a>';
        return $link;
    }

}