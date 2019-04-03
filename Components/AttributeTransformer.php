<?php

namespace ShyimAttributeTransformer\Components;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use ShyimAttributeTransformer\Components\Entity\EntityTransformer;

/**
 * Class AttributeTransformer
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class AttributeTransformer
{
    /**
     * @var ModelTransformer[]
     */
    private $transformers;

    /**
     * @var CachedTableReader
     */
    private $tableReader;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * AttributeTransformer constructor.
     *
     * @param CachedTableReader $tableReader
     * @param Connection        $connection
     * @param array             $transformers
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(
        CachedTableReader $tableReader,
        Connection $connection,
        array $transformers
    ) {
        $this->tableReader = $tableReader;
        $this->connection = $connection;
        $this->applyCustomTransformers($transformers);
    }

    /**
     * @param ConfigurationStruct $column
     * @param string $ids
     *
     * @throws \Zend_Cache_Exception
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function addAttribute(ConfigurationStruct $column, string $ids)
    {
        $transformer = $this->getTransformer($column);
        $transformer->addIds(array_filter(explode('|', $ids)));
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function resolve()
    {
        foreach ($this->transformers as $transformer) {
            $transformer->resolve();
        }
    }

    /**
     * @param ConfigurationStruct $column
     * @param string $ids
     *
     * @return mixed|null
     *
     * @throws \Zend_Cache_Exception
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function get(ConfigurationStruct $column, string $ids)
    {
        $transformer = $this->getTransformer($column);

        if ($column->getColumnType() === TypeMapping::TYPE_SINGLE_SELECTION) {
            return $transformer->get($ids);
        }

        $ids = array_map(function ($value) use ($transformer) {
            return $transformer->get($value);
        }, explode('|', $ids));

        return array_filter($ids);
    }

    /**
     * @param ConfigurationStruct $column
     *
     * @return ModelTransformer
     *
     * @throws \Zend_Cache_Exception
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    private function getTransformer(ConfigurationStruct $column): ModelTransformer
    {
        $tableName = $this->tableReader->getTableName($column->getEntity());

        if (!isset($this->transformers[$tableName])) {
            $this->transformers[$tableName] = new ModelTransformer($tableName, $this->connection);
        }

        return $this->transformers[$tableName];
    }

    /**
     * @param array $transformers
     */
    private function applyCustomTransformers(array $transformers)
    {
        /** @var EntityTransformer $transformer */
        foreach ($transformers as $transformer) {
            $this->transformers[$transformer->getEntity()] = $transformer;
        }
    }
}
