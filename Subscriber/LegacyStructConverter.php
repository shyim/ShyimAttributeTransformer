<?php

namespace ShyimAttributeTransformer\Subscriber;

use Enlight\Event\SubscriberInterface;
use Enlight_Event_EventArgs;
use ShyimAttributeTransformer\Components\Converter;

class LegacyStructConverter implements SubscriberInterface
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * LegacyStructConverter constructor.
     *
     * @param Converter $converter
     */
    public function __construct(Converter $converter)
    {
        $this->converter = $converter;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Legacy_Struct_Converter_Convert_Manufacturer' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_Category' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_List_Product' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_Product' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_Property_Set' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_Configurator_Option' => 'legacyStructConverter',
            'Legacy_Struct_Converter_Convert_Property_Option' => 'legacyStructConverter',
        ];
    }

    /**
     * @param Enlight_Event_EventArgs $args
     *
     * @throws \Zend_Cache_Exception
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function legacyStructConverter(Enlight_Event_EventArgs $args)
    {
        $data = $args->getReturn();
        $eventName = $args->getName();
        
        if ($eventName === 'Legacy_Struct_Converter_Convert_Product') {
            $eventName = 'Legacy_Struct_Converter_Convert_List_Product';
        }

        $args->setReturn($this->converter->convert($eventName, $data));
    }
}
