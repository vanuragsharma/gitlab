<?php

namespace BoostMyShop\ErpMagentoFix\Block\Erp\Products\Edit;


class Tabs extends \BoostMyShop\Erp\Block\Products\Edit\Tabs
{
    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->_tabs = $this->reorderTabs();

        if ($activeTab = $this->getRequest()->getParam('active_tab')) {
            $this->setActiveTab($activeTab);
        } elseif ($activeTabId = $this->_authSession->getActiveTabId()) {
            $this->_setActiveTab($activeTabId);
        }

        if ($this->_activeTab === null && !empty($this->_tabs)) {
            /** @var TabInterface $tab */
            $this->_activeTab = (reset($this->_tabs))->getId();
        }

        $this->assign('tabs', $this->_tabs);
        return parent::_beforeToHtml();
    }

    /**
     * Reorder the tabs.
     *
     * @return array
     */
    private function reorderTabs()
    {
        $orderByIdentity = [];
        $orderByPosition = [];
        $position        = 100;

        /**
         * Set the initial positions for each tab.
         *
         * @var string       $key
         * @var TabInterface $tab
         */
        foreach ($this->_tabs as $key => $tab) {
            $tab->setPosition($position);

            $orderByIdentity[$key]      = $tab;
            $orderByPosition[$position] = $tab;

            $position += 100;
        }

        return $this->applyTabsCorrectOrder($orderByPosition, $orderByIdentity);
    }

    private function applyTabsCorrectOrder (array $orderByPosition, array $orderByIdentity)
    {
        $positionFactor = 1;
        /**
         * Rearrange the positions by using the after tag for each tab.
         *
         * @var int $position
         * @var TabInterface $tab
         */
        foreach ($orderByPosition as $position => $tab) {
            if (!$tab->getAfter() || !in_array($tab->getAfter(), array_keys($orderByIdentity))) {
                $positionFactor += 1; //BMS override
                continue;
            }
            $grandPosition = $orderByIdentity[$tab->getAfter()]->getPosition();
            $newPosition   = $grandPosition + $positionFactor;
            unset($orderByPosition[$position]);
            $orderByPosition[$newPosition] = $tab;
            $tab->setPosition($newPosition);
            $positionFactor++;
        }

        return $this->finalTabsSortOrder($orderByPosition);
    }

    /**
     * Apply the last sort order to tabs.
     *
     * @param array $orderByPosition
     *
     * @return array
     */
    private function finalTabsSortOrder(array $orderByPosition)
    {
        ksort($orderByPosition);

        $ordered = [];

        /** @var TabInterface $tab */
        foreach ($orderByPosition as $tab) {
            $ordered[$tab->getId()] = $tab;
        }

        return $ordered;
    }

}