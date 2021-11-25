<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Config;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ReaderInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;

class OrderGridReader implements ReaderInterface
{
    private ResourceConnection $resourceConnection;
    private OrderGridCollection $orderGridCollection;

    public function __construct(ResourceConnection $resourceConnection, OrderGridCollection $orderGridCollection)
    {
        $this->resourceConnection = $resourceConnection;
        $this->orderGridCollection = $orderGridCollection;
    }

    public function read($scope = null)
    {
        $connection = $this->resourceConnection->getConnection();
        if (!$connection->isTableExists($this->orderGridCollection->getMainTable())) {
            return [];
        }

        $config = [];
        foreach ($connection->describeTable($this->orderGridCollection->getMainTable()) as $column => $description) {
            $config['GridOrder']['fields'][$column] = ['name' => $column, 'type' => 'String', 'arguments' => []];
        }

        return $config;
    }
}
