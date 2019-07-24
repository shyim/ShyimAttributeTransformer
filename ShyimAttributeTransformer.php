<?php

namespace ShyimAttributeTransformer;

use Shopware\Components\Plugin;
use ShyimAttributeTransformer\Components\CompilerPass\EntityTransformerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ShyimAttributeTransformer extends Plugin
{
    const TYPE_LIST_PRODUCT = 'Legacy_Struct_Converter_Convert_List_Product';
    const TYPE_LIST_CATEGORY = 'Legacy_Struct_Converter_Convert_Category';
    const TYPE_MANUFACTURER = 'Legacy_Struct_Converter_Convert_Manufacturer';
    const TYPE_PROPERTY_OPTION = 'Legacy_Struct_Converter_Convert_Property_Set';
    const TYPE_PROPERTY_OPTION2 = 'ProductSearch_Facet';
    const TYPE_CONFIGURATOR_OPTION = 'Legacy_Struct_Converter_Convert_Configurator_Option';
    const TYPE_PROPERTY_VALUE = 'Legacy_Struct_Converter_Convert_Property_Option';
    const TYPE_PROPERTY_VALUE2 = 'ProductSearch_Facet_Value';
    const TYPE_FORMS = 'Enlight_Controller_Action_PostDispatch_Frontend_Forms';
    const TYPE_STATIC = 'Enlight_Controller_Action_PostDispatch_Frontend_Custom';
    const TYPE_PAYMENT = 'Legacy_Struct_Converter_Convert_Payment';

    const TABLE_MAPPING = [
        self::TYPE_LIST_PRODUCT => 's_articles_attributes',
        self::TYPE_LIST_CATEGORY => 's_categories_attributes',
        self::TYPE_PROPERTY_OPTION => 's_filter_options_attributes',
        self::TYPE_PROPERTY_OPTION2 => 's_filter_options_attributes',
        self::TYPE_CONFIGURATOR_OPTION => 's_article_configurator_options_attributes',
        self::TYPE_PROPERTY_VALUE => 's_filter_values_attributes',
        self::TYPE_PROPERTY_VALUE2 => 's_filter_values_attributes',
        self::TYPE_FORMS => 's_cms_support_attributes',
        self::TYPE_STATIC => 's_cms_static_attributes',
        self::TYPE_MANUFACTURER => 's_articles_supplier_attributes',
        self::TYPE_PAYMENT => 's_core_paymentmeans_attributes',
    ];

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new EntityTransformerCompilerPass());
    }
}
