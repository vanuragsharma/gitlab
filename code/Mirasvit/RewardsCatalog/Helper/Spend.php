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



namespace Mirasvit\RewardsCatalog\Helper;

use Mirasvit\Rewards\Model\Config as Config;
use Mirasvit\Rewards\Helper\Calculation;

/**
 * Calculate spending points for product page
 */
class Spend
{
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $currencyHelper;
    /**
     * @var \Mirasvit\Rewards\Helper\Balance\SpendRulesList
     */
    private $spendRulesHelper;
    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Magento\Framework\Pricing\Helper\Data $currencyHelper,
        \Mirasvit\Rewards\Model\Config $config,
        \Mirasvit\Rewards\Helper\Balance\SpendRulesList $spendRulesHelper
    ) {
        $this->currencyHelper                = $currencyHelper;
        $this->spendRulesHelper                = $spendRulesHelper;
        $this->config                        = $config;
    }

    /**
     * Calcs possible earn product points in money equivalent
     *
     * @param float $points
     * @param float $subtotal
     * @param \Magento\Customer\Model\Customer $customer
     * @param int $websiteId
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getProductPointsAsMoney($points, $subtotal, $customer, $websiteId)
    {
        if ($subtotal <= Calculation::ZERO_VALUE) {
            return 0;
        }
        if ($this->config->getGeneralIsDisplayProductPointsAsMoney()) {
            $rules = $this->spendRulesHelper->getRuleCollection($websiteId, $customer->getGroupId());
            if ($rules->count()) {
                $pointsMoney = [0];
                /** @var \Mirasvit\Rewards\Model\Spending\Rule $rule */
                foreach ($rules as $rule) {
                    $tier = $rule->getTier($customer);
                    $spendPoints = $tier->getSpendPoints();

                    if ($spendPoints <= Calculation::ZERO_VALUE) {
                        continue;
                    }

                    if ($rule->getTiersSerialized() && $rule->getTiersSerialized()[1]['spend_max_points']
                        && $rule->getTiersSerialized()[1]['spend_max_points'] > 0

                    ) {
                        if ($rule->getTiersSerialized()[1]['spend_max_points'] < $spendPoints) {
                            $spendPoints = $rule->getTiersSerialized()[1]['spend_max_points'];
                        }

                        if (strpos($rule->getTiersSerialized()[1]['monetary_step'], '%')) {
                            $points = $rule->getTiersSerialized()[1]['spend_max_points'];
                        }
                    }

                    $res = ($points / $spendPoints) * $tier->getMonetaryStep($subtotal);

                    if ($res > $subtotal) {
                        $res = $subtotal;
                    }

                    $pointsMoney[] = $res;
                }

                $points = $this->currencyHelper->currency(max($pointsMoney), true, false);
            }
        }

        return $points;
    }
}
