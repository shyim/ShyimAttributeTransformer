<?php

namespace ShyimAttributeTransformer\Components;

use Doctrine\DBAL\Connection;
use PDO;

/**
 * Class ModelTransformer
 * @author Soner Sayakci <shyim@posteo.de>
 */
class ModelTransformer
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var array
     */
    protected $ids;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * ModelTransformer constructor.
     * @param string $table
     * @param Connection $connection
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function __construct(string $table = null, Connection $connection = null)
    {
        $this->table = $table;
        $this->connection = $connection;
    }

    /**
     * @param array $ids
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function addIds(array $ids)
    {
        foreach ($ids as $id) {
            if (isset($this->data[$id]) || \in_array($id, $this->ids, true)) {
                return;
            }

            $this->ids[] = $id;
        }
    }

    /**
     * @author Soner Sayakci <shyim@posteo.de>
     */
    public function resolve()
    {
        if (!empty($this->ids)) {
            $qb = $this->connection->createQueryBuilder();
            $data = $qb
                ->from($this->table, 'attributeTable')
                ->select('*')
                ->where('attributeTable.id IN(:ids)')
                ->setParameter('ids', $this->ids, Connection::PARAM_INT_ARRAY)
                ->execute()
                ->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

            $data = array_map('reset', $data);
            foreach ($data as $key => $item) {
                $this->data[$key] = $item;
            }

            $this->ids = [];
        }
    }

    /**
     * @param string $index
     * @author Soner Sayakci <shyim@posteo.de>
     * @return mixed|null
     */
    public function get(string $index)
    {
        return $this->data[$index] ?? null;
    }
}