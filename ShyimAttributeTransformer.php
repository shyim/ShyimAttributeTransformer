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

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Legacy_Struct_Converter_Convert_Manufacturer' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_Category' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_List_Product' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_Product' => 'legacyStructConverter',

            self::TYPE_FORMS => 'transformForm',
            self::TYPE_STATIC => 'transformStatic'
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     * @throws \Zend_Cache_Exception
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function legacyStructConverter(Enlight_Event_EventArgs $args)
    {
        $data = $args->getReturn();
        $eventName = $args->getName();

        if ($eventName === 'Legacy_Struct_Converter_Convert_Product') {
            $eventName = 'Legacy_Struct_Converter_Convert_List_Product';
        }

        $fieldsList = require __DIR__ . '/config.php';

        foreach ($fieldsList as $key => $fields) {
            if ($eventName === $key) {
                $columns = $this->container->get('shyim_media.cached_table_reader')->getColumns(self::TABLE_MAPPING[$key]);

                if (empty($columns)) {
                    return;
                }

                if (isset($data['attribute'])) {
                    $data['attribute'] = $this->transformAttributeFields($fields, $data['attribute'], $columns);
                }

                if (isset($data['attributes']['core'])) {
                    $attributeData = $data['attributes']['core']->jsonSerialize();
                    $attributeData = $this->transformAttributeFields($fields, $attributeData, $columns);
                    $data['attributes']['core'] = new Attribute($attributeData);
                }

                $data = $this->transformAttributeFields($fields, $data, $columns);
            }
        }

        $args->setReturn($data);
    }

    public function transformForm(Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $form = $eventArgs->getSubject()->View()->getAssign('sSupport');

        if (!isset($form['attribute'])) {
            $form['attribute'] = $this->container->get('shopware_attribute.data_loader')->load(self::TABLE_MAPPING[self::TYPE_FORMS], $form['id']);
        }

        $event = new Enlight_Event_EventArgs();
        $event->setName($eventArgs->getName());
        $event->setReturn($form);

        $this->legacyStructConverter($event);

        $eventArgs->getSubject()->View()->assign('sSupport', $event->getReturn());
    }

    public function transformStatic(Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $form = $eventArgs->getSubject()->View()->getAssign('sCustomPage');

        if (!isset($form['attribute'])) {
            $form['attribute'] = $this->container->get('shopware_attribute.data_loader')->load(self::TABLE_MAPPING[self::TYPE_FORMS], $form['id']);
        }

        $event = new Enlight_Event_EventArgs();
        $event->setName($eventArgs->getName());
        $event->setReturn($form);

        $this->legacyStructConverter($event);
        dd($event->getReturn());

        $eventArgs->getSubject()->View()->assign('sCustomPage', $event->getReturn());
    }


    /**
     * @param array $fields
     * @param array $data
     * @param array $columns
     * @return array
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function transformAttributeFields(array $fields, array $data, array $columns): array
    {
        $attributeTransformer = $this->container->get('shyim_media.attribute_transformer');
        $hasFoundKey = false;

        foreach ($data as $key => $value) {
            if (\in_array($key, $fields, true)) {
                if (empty($value)) {
                    continue;
                }

                $attributeTransformer->addAttribute($columns[$key], $value);
                $hasFoundKey = true;
            }
        }

        if (!$hasFoundKey) {
            return $data;
        }

        $attributeTransformer->resolve();

        foreach ($data as $key => &$value) {
            if (\in_array($key, $fields, true)) {
                $value = $attributeTransformer->get($columns[$key], $value);
            }
        }

        return $data;
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new EntityTransformerCompilerPass());
    }


}