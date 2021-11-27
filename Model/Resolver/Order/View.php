<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Order;

use Danslo\Velvet\Model\Authorization;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

class View implements ResolverInterface
{
    private Authorization $authorization;
    private OrderRepositoryInterface $orderRepository;
    private OrderFormatter $orderFormatter;

    public function __construct(
        Authorization $authorization,
        OrderRepositoryInterface $orderRepository,
        OrderFormatter $orderFormatter
    ) {
        $this->authorization = $authorization;
        $this->orderRepository = $orderRepository;
        $this->orderFormatter = $orderFormatter;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);

        $orderId = $args['order_id'] ?? null;
        if ($orderId === null) {
            throw new GraphQlInputException(__('Required parameter "order_id" is missing'));
        }

        $order = $this->orderRepository->get($orderId);
        return $this->orderFormatter->format($order);
    }
}
