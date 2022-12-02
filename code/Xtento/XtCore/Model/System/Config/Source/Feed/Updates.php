<?php

/**
 * Product:       Xtento_XtCore
 * ID:            DzC2eFiDpwD50/GgVbHWZ8tC9BZhoUDH2be4JBMyIeM=
 * Last Modified: 2017-08-16T08:52:13+00:00
 * File:          app/code/Xtento/XtCore/Model/System/Config/Source/Feed/Updates.php
 * Copyright:     Copyright (c) XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\XtCore\Model\System\Config\Source\Feed;

class Updates implements \Magento\Framework\Option\ArrayInterface
{
    const TYPE_NEW_RELEASE = 'NEW_RELEASE';
    const TYPE_UPDATED = 'UPDATE';
    const TYPE_PROMOTIONS = 'PROMOTIONS';
    const TYPE_OTHERINFO = 'OTHER_INFO';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::TYPE_UPDATED, 'label' => __('Updates for installed extensions')],
            ['value' => self::TYPE_NEW_RELEASE, 'label' => __('New Extensions')],
            ['value' => self::TYPE_PROMOTIONS, 'label' => __('Discounts/Promotions')],
            ['value' => self::TYPE_OTHERINFO, 'label' => __('Other information')]
        ];
    }
}
