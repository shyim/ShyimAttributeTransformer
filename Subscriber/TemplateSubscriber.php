<?php

namespace ShyimAttributeTransformer\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Controller_ActionEventArgs;
use Shopware\Bundle\AttributeBundle\Service\DataLoader;
use ShyimAttributeTransformer\Components\Converter;
use ShyimAttributeTransformer\ShyimAttributeTransformer;

class TemplateSubscriber implements SubscriberInterface
{
    /**
     * @var Converter
     */
    private $converter;
    /**
     * @var DataLoader
     */
    private $dataLoader;

    /**
     * TemplateSubscriber constructor.
     *
     * @param Converter  $converter
     * @param DataLoader $dataLoader
     */
    public function __construct(Converter $converter, DataLoader $dataLoader)
    {
        $this->converter = $converter;
        $this->dataLoader = $dataLoader;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ShyimAttributeTransformer::TYPE_FORMS => 'transformForm',
            ShyimAttributeTransformer::TYPE_STATIC => 'transformStatic',
            'Enlight_Controller_Action_PostDispatch' => [
                ['transformGlobals', 200]
            ]
        ];
    }

    public function transformForm(Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $form = $eventArgs->getSubject()->View()->getAssign('sSupport');

        $type = ShyimAttributeTransformer::TYPE_FORMS;

        if (!isset($form['attribute'])) {
            $form['attribute'] = $this->dataLoader->load(ShyimAttributeTransformer::TABLE_MAPPING[$type], $form['id']);
        }

        $eventArgs->getSubject()->View()->assign('sSupport', $this->converter->convert($type, $form));
    }

    /**
     * @param Enlight_Controller_ActionEventArgs $eventArgs
     *
     * @throws \Exception
     */
    public function transformStatic(Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $static = $eventArgs->getSubject()->View()->getAssign('sCustomPage');

        $type = ShyimAttributeTransformer::TYPE_STATIC;

        if (!isset($static['attribute'])) {
            $static['attribute'] = $this->dataLoader->load(ShyimAttributeTransformer::TABLE_MAPPING[$type], $static['id']);
        }
        $eventArgs->getSubject()->View()->assign('sCustomPage', $this->converter->convert($type, $static));
    }

    /**
     * @param Enlight_Controller_ActionEventArgs $eventArgs
     */
    public function transformGlobals(Enlight_Controller_ActionEventArgs $eventArgs)
    {
        $sCategories = $eventArgs->getSubject()->View()->getAssign('sCategories');

        foreach ($sCategories as &$category) {
            $category = $this->transformRecrusive(ShyimAttributeTransformer::TYPE_LIST_CATEGORY, $category, 'subcategories');
        }

        $eventArgs->getSubject()->View()->assign('sCategories', $sCategories);
    }

    /**
     * @param array $data
     * @param string $key
     */
    private function transformRecrusive($type, $data, $key)
    {
        $data = $this->converter->convert($type, $data);

        if (!empty($data[$key])) {
            foreach ($data[$key] as &$sub) {
                $sub = $this->transformRecrusive($type, $sub, $key);
            }
        }

        return $data;
    }
}
