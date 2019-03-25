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
    const TYPE_FORMS = 'Enlight_Controller_Action_PostDispatch_Frontend_Forms';
    const TYPE_STATIC = 'Enlight_Controller_Action_PostDispatch_Frontend_Custom';

    const TABLE_MAPPING = [
        self::TYPE_LIST_PRODUCT => 's_articles_attributes',
        self::TYPE_LIST_CATEGORY => 's_categories_attributes',
        self::TYPE_FORMS => 's_cms_support_attributes',
        self::TYPE_STATIC => 's_cms_static_attributes',
        self::TYPE_MANUFACTURER => 's_articles_supplier_attributes'
    ];

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new EntityTransformerCompilerPass());
    }
}
