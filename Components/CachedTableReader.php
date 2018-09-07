<?php

namespace ShyimAttributeTransformer\Components;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
use Zend_Cache_Core;

/**
 * Class CachedTableReader
 *
 * @author Soner Sayakci <shyim@posteo.de>
 */
class CachedTableReader
{
    /**
     * @var Zend_Cache_Core
     */
    private $cache;

    /**
     * @var CrudService
     */
    private $crudService;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * CachedTableReader constructor.
     *
     * @param Zend_Cache_Core $cache
     * @param CrudService     $crudService
     * @param ModelManager    $modelManager
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(Zend_Cache_Core $cache, CrudService $crudService, ModelManager $modelManager)
    {
        $this->cache = $cache;
        $this->crudService = $crudService;
        $this->modelManager = $modelManager;
    }

    /**
     * @param string $table
     *
     * @throws \Zend_Cache_Exception
     *
     * @return \Shopware\Bundle\AttributeBundle\Service\ConfigurationStruct[]
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getColumns(string $table)
    {
        $cacheKey = 'cachedTableReader' . $table;

        if ($cache = $this->cache->load($cacheKey)) {
            return $cache;
        }

        $rawList = $this->crudService->getList($table);
        $list = [];

        foreach ($rawList as $item) {
            if ($item->getColumnType() === TypeMapping::TYPE_SINGLE_SELECTION || $item->getColumnType() === TypeMapping::TYPE_MULTI_SELECTION) {
                $list[$item->getColumnName()] = $item;
            }
        }

        $this->cache->save($list, $cacheKey, [], 86400);

        return $list;
    }

    /**
     * @param string $entity
     *
     * @throws \Zend_Cache_Exception
     *
     * @return string
     *
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function getTableName(string $entity): string
    {
        $cacheKey = 'tableName' . str_replace('\\', '', $entity);

        if ($cache = $this->cache->load($cacheKey)) {
            return $cache;
        }

        $tableName = $this->modelManager->getClassMetadata($entity)->getTableName();

        $this->cache->save($tableName, $cacheKey, [], 86400);

        return $tableName;
    }
}
