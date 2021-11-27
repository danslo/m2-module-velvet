<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Order;

use Danslo\Velvet\Api\AdminAuthorizationInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory as OrderGridCollectionFactory;
use Magento\Store\Model\System\Store as SystemStore;

class Grid implements ResolverInterface, AdminAuthorizationInterface
{
    private OrderGridCollectionFactory $orderGridCollectionFactory;
    private Currency $currency;
    private array $currencyFormatFields;
    private int $defaultPageSize;
    private SystemStore $store;

    public function __construct(
        OrderGridCollectionFactory $orderGridCollectionFactory,
        Currency $currency,
        SystemStore $store,
        array $currencyFormatFields = [],
        int $defaultPageSize = 20
    ) {
        $this->orderGridCollectionFactory = $orderGridCollectionFactory;
        $this->currency = $currency;
        $this->currencyFormatFields = $currencyFormatFields;
        $this->defaultPageSize = $defaultPageSize;
        $this->store = $store;
    }

    private function getStorenameFromStoreId(int $storeId): string
    {
        return $this->store->getStoreName($storeId);
    }

    private function getOrderData(Document $order): array
    {
        $data = $order->getData();

        foreach ($this->currencyFormatFields as $field) {
            if (!isset($data[$field])) {
                continue;
            }
            $data[$field] = $this->currency->format($data[$field], [], false);
        }

        $data['store_name'] = $this->getStorenameFromStoreId((int) $data['store_id']);

        return $data;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        /** @var Collection $collection */
        $collection = $this->orderGridCollectionFactory->create()
            ->setCurPage($args['input']['page_number'] ?? 0)
            ->setPageSize($args['input']['page_size'] ?? $this->defaultPageSize)
            ->addOrder('created_at'); /** todo: add user-defined sorting */

        // todo: add filters

        $orders = [];
        foreach ($collection as $order) {
            $orders[] = $this->getOrderData($order);
        }
        return [
            'orders' => $orders,
            'last_page_number' => $collection->getLastPageNumber(),
            'total_orders' => $collection->getTotalCount()
        ];
    }
}
