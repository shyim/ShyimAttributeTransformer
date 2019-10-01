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

    public function __construct(
        CachedTableReader $tableReader,
        Connection $connection,
        iterable $transformers
    ) {
        $this->tableReader = $tableReader;
        $this->connection = $connection;
        $this->applyCustomTransformers($transformers);
    }

    public function addAttribute(ConfigurationStruct $column, string $ids)
    {
        $transformer = $this->getTransformer($column);
        $transformer->addIds(array_filter(explode('|', $ids)));
    }

    public function resolve(): void
    {
        foreach ($this->transformers as $transformer) {
            $transformer->resolve();
        }
    }

    /**
     * @return mixed|null
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

    private function getTransformer(ConfigurationStruct $column): ModelTransformer
    {
        $tableName = $this->tableReader->getTableName($column->getEntity());

        if (!isset($this->transformers[$tableName])) {
            $this->transformers[$tableName] = new ModelTransformer($tableName, $this->connection);
        }

        return $this->transformers[$tableName];
    }

    private function applyCustomTransformers(iterable $transformers)
    {
        /** @var EntityTransformer $transformer */
        foreach ($transformers as $transformer) {
            $this->transformers[$transformer->getEntity()] = $transformer;
        }
    }
}
