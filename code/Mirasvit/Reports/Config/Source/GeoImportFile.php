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
 * @package   mirasvit/module-reports
 * @version   1.3.40
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Config\Source;

use Mirasvit\Reports\Model\ConfigProvider;

class GeoImportFile
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function toOptionArray()
    {
        $result = [];

        $url = ConfigProvider::GEO_FILE_URL;
        try {
            $json = \Zend_Json::decode(file_get_contents($url));

            if (is_array($json)) {
                foreach ($json['data'] as $entity) {
                    $result[] = [
                        'label' => $entity["name"],
                        'value' => 'http://files.mirasvit.com/report/postcode/download/?identifier=' . $entity["identifier"],
                    ];
                }
            }
        } catch (\Exception $e) {
        }

        asort($result);

        return $result;
    }
}