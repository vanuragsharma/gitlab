<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-rewards
 * @version   3.0.41
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Rewards\Api\Data\Spending;

/**
 * Interface for spending rule search results.
 */
interface RuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get earning rules list.
     *
     * @return \Mirasvit\Rewards\Api\Data\Spending\RuleInterface[]
     */
    public function getItems();

    /**
     * Set earning rules list.
     *
     * @param array $items Array of \Mirasvit\Rewards\Api\Data\Spending\RuleInterface[]
     * @return $this
     */
    public function setItems(array $items);
}
