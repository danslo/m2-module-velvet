<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Config;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Config\ReaderInterface;

class GridReader implements ReaderInterface
{
    private ResourceConnection $resourceConnection;
    private string $tableName;
    private string $schemaType;

    public function __construct(
        ResourceConnection $resourceConnection,
        string $tableName,
        string $schemaType
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableName = $tableName;
        $this->schemaType = $schemaType;
    }

    private function getTypeFromColumnDescription(array $description): String
    {
        switch ($description['DATA_TYPE']) {
            case 'tinyint':
            case 'smallint':
            case 'int':
            case 'mediumint':
            case 'bigint':
                return 'Int';
            case 'decimal':
            case 'numeric':
                return 'Float';
            default:
                return 'String';
        }
    }

    public function read($scope = null)
    {
        $connection = $this->resourceConnection->getConnection();
        if (!$connection->isTableExists($this->tableName)) {
            return [];
        }

        $config = [];
        foreach ($connection->describeTable($this->tableName) as $column => $description) {
            $config[$this->schemaType]['fields'][$column] = [
                'name' => $column,
                'type' => $this->getTypeFromColumnDescription($description),
                'required' => $description['NULLABLE'] === false,
                'arguments' => []
            ];
        }

        return $config;
    }
}
