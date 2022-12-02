<?php
/**
 * ||GEISSWEB| EU VAT Enhanced
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GEISSWEB End User License Agreement
 * that is available through the world-wide-web at this URL: https://www.geissweb.de/legal-information/eula
 *
 * DISCLAIMER
 *
 * Do not edit this file if you wish to update the extension in the future. If you wish to customize the extension
 * for your needs please refer to our support for more information.
 *
 * @copyright   Copyright (c) 2015 GEISS Weblösungen (https://www.geissweb.de)
 * @license     https://www.geissweb.de/legal-information/eula GEISSWEB End User License Agreement
 */

namespace Geissweb\Euvat\Model\System\Config\Source;

/**
 * Class ValidationPeriod
 */
class ValidationPeriod implements \Magento\Framework\Data\OptionSourceInterface
{
    const EVERY_LOGIN = 0;
    const EVERY_MONTH = 1;
    const EVERY_THREE_MONTHS = 3;
    const EVERY_SIX_MONTHS = 6;
    const EVERY_YEAR = 12;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::EVERY_MONTH,
                'label' => __('Every month')
            ],
            [
                'value' => self::EVERY_THREE_MONTHS,
                'label' => __('Every 3 months')
            ],
            [
                'value' => self::EVERY_SIX_MONTHS,
                'label' => __('Every 6 months')
            ],
            [
                'value' => self::EVERY_YEAR,
                'label' => __('Every year')
            ]
        ];
    }
}
