<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Config;

use Magento\Framework\Config\ReaderInterface;

class OrderGridReader implements ReaderInterface
{
    public function read($scope = null)
    {
        /** @see \Magento\CatalogGraphQl\Model\Config\AttributeReader */
        return [];
    }
}
