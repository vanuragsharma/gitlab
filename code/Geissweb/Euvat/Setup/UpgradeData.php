<?php
namespace Geissweb\Euvat\Setup;

use Geissweb\Euvat\Logger\Logger;
use Magento\Framework\DB\FieldDataConversionException;
use Magento\Framework\DB\FieldDataConverterFactory;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select\QueryModifierFactory;
use Magento\Framework\Module\Manager;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var FieldDataConverterFactory
     */
    private $fieldDataConverterFactory;

    /**
     * @var QueryModifierFactory
     */
    private $queryModifierFactory;

    /**
     * @var Generator
     */
    private $queryGenerator;
    /**
     * @var Manager
     */
    private $moduleManager;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param FieldDataConverterFactory   $fieldDataConverterFactory
     * @param QueryModifierFactory $queryModifierFactory
     * @param Generator             $queryGenerator
     * @param Manager                 $moduleManager
     * @param Logger                     $logger
     */
    public function __construct(
        FieldDataConverterFactory $fieldDataConverterFactory,
        QueryModifierFactory $queryModifierFactory,
        Generator $queryGenerator,
        Manager $moduleManager,
        Logger $logger
    ) {
        $this->fieldDataConverterFactory = $fieldDataConverterFactory;
        $this->queryModifierFactory = $queryModifierFactory;
        $this->queryGenerator = $queryGenerator;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '1.8.7', '<')) {
            $this->convertSerializedDataToJson($setup);
        }
    }

    /**
     * Upgrade to version 2.0.1, convert data for the sales_order_item.product_options and quote_item_option.value
     * from serialized to JSON format
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @return void
     */
    private function convertSerializedDataToJson(\Magento\Framework\Setup\ModuleDataSetupInterface $setup)
    {
        if ($this->moduleManager->isEnabled('Geissweb_Euvat')) {
            $fieldDataConverter = $this->fieldDataConverterFactory->create(
                \Magento\Framework\DB\DataConverter\SerializedToJson::class
            );

            $queryModifier = $this->queryModifierFactory->create('in', [ 'values' => [
                'path' => [
                    'euvat/group_price_display/catalog_price_display',
                    'euvat/group_price_display/cart_product_price_display',
                    'euvat/group_price_display/cart_subtotal_price_display'
                ]
            ]]);

            try {
                $fieldDataConverter->convert(
                    $setup->getConnection(),
                    $setup->getTable('core_config_data'),
                    'config_id',
                    'value',
                    $queryModifier
                );
            } catch (FieldDataConversionException $e) {
                $this->logger->critical($e);
            }
        }
    }
}
