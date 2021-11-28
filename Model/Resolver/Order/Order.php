<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Order;

use Danslo\Velvet\Api\AdminAuthorizationInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\SalesGraphQl\Model\Formatter\Order as OrderFormatter;

class Order implements ResolverInterface, AdminAuthorizationInterface
{
    private OrderRepositoryInterface $orderRepository;
    private OrderFormatter $orderFormatter;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderFormatter $orderFormatter
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderFormatter = $orderFormatter;
    }

    private function getOrderActionsAvailability(SalesOrder $order): array
    {
        return [
            'can_ship'       => $order->canShip(),
            'can_cancel'     => $order->canCancel(),
            'can_invoice'    => $order->canInvoice(),
            'can_hold'       => $order->canHold(),
            'can_unhold'     => $order->canUnhold(),
            'can_creditmemo' => $order->canCreditmemo()
        ];
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $orderId = $args['order_id'] ?? null;
        if ($orderId === null) {
            throw new GraphQlInputException(__('Required parameter "order_id" is missing'));
        }

        // At this point we have passed admin authorization.
        // Bypass core customer validation.
        $context->getExtensionAttributes()->setIsCustomer(true);

        /** @var SalesOrder $order */
        $order = $this->orderRepository->get($orderId);

        return array_merge(
            $this->orderFormatter->format($order),
            $this->getOrderActionsAvailability($order)
        );
    }
}
