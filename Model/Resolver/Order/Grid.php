<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Order;

use Danslo\Velvet\Model\Authorization;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory as OrderGridCollectionFactory;

class Grid implements ResolverInterface
{
    private Authorization $authorization;
    private OrderGridCollectionFactory $orderGridCollectionFactory;

    public function __construct(Authorization $authorization, OrderGridCollectionFactory $orderGridCollectionFactory)
    {
        $this->authorization = $authorization;
        $this->orderGridCollectionFactory = $orderGridCollectionFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);

        /**
         * TODO:
         * - pagination
         * - limit
         * - sorting
         */
        $orders = [];
        foreach ($this->orderGridCollectionFactory->create() as $order) {
            $orders[] = $order->getData();
        }
        return $orders;
    }
}
