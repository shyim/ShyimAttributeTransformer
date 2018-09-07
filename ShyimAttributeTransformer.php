<?php

namespace ShyimAttributeTransformer;

use Enlight_Controller_ActionEventArgs;
use Enlight_Event_EventArgs;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;
use Shopware\Components\Plugin;
use ShyimAttributeTransformer\Components\CompilerPass\EntityTransformerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ShyimAttributeTransformer extends Plugin
{
    const TYPE_LIST_PRODUCT = 'Legacy_Struct_Converter_Convert_List_Product';
    const TYPE_LIST_CATEGORY = 'Legacy_Struct_Converter_Convert_Category';
    const TYPE_FORMS = 'Enlight_Controller_Action_PostDispatch_Frontend_Forms';
    const TYPE_STATIC = 'Enlight_Controller_Action_PostDispatch_Frontend_Custom';

    const TABLE_MAPPING = [
        self::TYPE_LIST_PRODUCT => 's_articles_attributes',
        self::TYPE_LIST_CATEGORY => 's_categories_attributes',
        self::TYPE_FORMS => 's_cms_support_attributes',
        self::TYPE_STATIC => 's_cms_static_attributes',
    ];

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new EntityTransformerCompilerPass());
    }
}
