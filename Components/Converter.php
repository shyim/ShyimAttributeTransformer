<?php

namespace ShyimAttributeTransformer\Components;

use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
use ShyimAttributeTransformer\ShyimAttributeTransformer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Converter
{
    const TABLE_MAPPING = [
        ShyimAttributeTransformer::TYPE_LIST_PRODUCT => 's_articles_attributes',
        ShyimAttributeTransformer::TYPE_LIST_CATEGORY => 's_categories_attributes',
        ShyimAttributeTransformer::TYPE_FORMS => 's_cms_support_attributes',
        ShyimAttributeTransformer::TYPE_STATIC => 's_cms_static_attributes',
        ShyimAttributeTransformer::TYPE_MANUFACTURER => 's_articles_supplier_attributes',
    ];
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
     */
    public function convert($mapping, $data)
    {
        if (!isset($this->fieldsList[$mapping])) {
            return $data;
        }

        $table = self::TABLE_MAPPING[$mapping];
        $fields = $this->fieldsList[$mapping];

        $columns = $this->cachedTableReader->getColumns($table);

        if (empty($columns)) {
            return $data;
        }

        if (isset($data['attribute'])) {
            $data['attribute'] = $this->transformAttributeFields($fields, $data['attribute'], $columns);
        }

        if (isset($data['attributes']['core'])) {
            $attributeData = $data['attributes']['core']->jsonSerialize();
            if (!empty($attributeData)) {
                $attributeData = $this->transformAttributeFields($fields, $attributeData, $columns);
                $data['attributes']['core'] = new Attribute($attributeData);
            }
        }

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
