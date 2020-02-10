<?php

namespace ShyimAttributeTransformer\Components;

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use ShyimAttributeTransformer\ShyimAttributeTransformer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Converter
{
    /**
     * @var array
     */
    private $fieldsList;
    /**
     * @var AttributeTransformer
     */
    private $transformer;

    /**
     * @var CachedTableReader
     */
    private $cachedTableReader;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(AttributeTransformer $transformer, CachedTableReader $cachedTableReader, ContainerInterface $container)
    {
        $this->container = $container;
        $this->fieldsList = $this->loadFieldList();
        $this->transformer = $transformer;
        $this->cachedTableReader = $cachedTableReader;
    }

    /**
     * @param string $mapping
     * @param array  $data
     *
     * @return array
     *
     * @throws \Zend_Cache_Exception
     * @throws \Exception
     */
    public function convert($mapping, $data)
    {
        if (!isset($this->fieldsList[$mapping])) {
            return $data;
        }

        $table = ShyimAttributeTransformer::TABLE_MAPPING[$mapping];
        $fields = $this->fieldsList[$mapping];

        $columns = $this->cachedTableReader->getColumns($table);

        if (empty($columns)) {
            return $data;
        }

        // Legacy_Struct_Converter_Convert_Category
        if (isset($data['attribute'])) {
            if(is_array($data['attribute'])) {
                $data['attribute'] = $this->transformAttributeFields($fields, $data['attribute'], $columns);
            }
            if(is_object($data['attribute'])){
                $attributeData = $data['attribute']->jsonSerialize();
                if (!empty($attributeData)) {
                    $attributeData = $this->transformAttributeFields($fields, $attributeData, $columns);
                    $data['attribute'] = new Attribute($attributeData);
                }
            }
        }

        // Legacy_Struct_Converter_Convert_Category
        // Legacy_Struct_Converter_Convert_List_Product/Legacy_Struct_Converter_Convert_Product
        // Legacy_Struct_Converter_Convert_Manufacturer
        // Legacy_Struct_Converter_Convert_Configurator_Option
        // ProductSearch_Facet
        // ProductSearch_Facet_Value
        if (isset($data['attributes']['core'])) {
            $attributeData = $data['attributes']['core']->jsonSerialize();
            if (!empty($attributeData)) {
                $attributeData = $this->transformAttributeFields($fields, $attributeData, $columns);
                $data['attributes']['core'] = new Attribute($attributeData);
            }
        }

        switch ($mapping) {
            case 'Legacy_Struct_Converter_Convert_Property_Set':
                foreach ($data as &$option) {
                    if (isset($option['attributes']['core'])) {
                        $attributeData = $option['attributes']['core']->jsonSerialize();
                        if (!empty($attributeData)) {
                            $attributeData = $this->transformAttributeFields($fields, $attributeData, $columns);
                            $option['attributes']['core'] = new Attribute($attributeData);
                        }
                    }
                }
                break;
        }

        // Legacy_Struct_Converter_Convert_List_Product/Legacy_Struct_Converter_Convert_Product
        $data = $this->transformAttributeFields($fields, $data, $columns);

        return $data;
    }

    /**
     * @param array $fields
     * @param array $data
     * @param array $columns
     *
     * @return array
     *
     * @throws \Zend_Cache_Exception
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function transformAttributeFields(array $fields, array $data, array $columns): array
    {
        $hasFoundKey = false;

        foreach ($data as $key => $value) {
            if (empty($value)) {
                continue;
            }

            if (\in_array($key, $fields, true)) {
                if (empty($value)) {
                    continue;
                }

                $this->transformer->addAttribute($columns[$key], $value);
                $hasFoundKey = true;
            }
        }

        if (!$hasFoundKey) {
            return $data;
        }

        $this->transformer->resolve();

        foreach ($data as $key => &$value) {
            if (empty($value)) {
                continue;
            }

            if (\in_array($key, $fields, true)) {
                $value = $this->transformer->get($columns[$key], $value);
            }
        }

        unset($value);

        return $data;
    }

    private function loadFieldList()
    {
        if ($this->container->hasParameter('shopware.transformer')) {
            return $this->container->getParameter('shopware.transformer');
        }

        trigger_error('[ShyimAttributeTransformer] The usage of config.php in plugin directory is deprecated. Please use add it to the config.php in the project root instead', E_USER_DEPRECATED);

        if (!file_exists(dirname(__DIR__) . '/config.php')) {
            return [];
        }

        return require dirname(__DIR__) . '/config.php';
    }
}
