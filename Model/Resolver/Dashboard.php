<?php

namespace Danslo\Velvet\Model\Resolver;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory as SearchCollectionFactory;

class Dashboard implements ResolverInterface
{
    private OrderCollectionFactory $orderCollectionFactory;
    private Currency $currency;
    private SearchCollectionFactory $searchCollectionFactory;

    public function __construct(
        OrderCollectionFactory $orderCollectionFactory,
        Currency $currency,
        SearchCollectionFactory $searchCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->currency = $currency;
        $this->searchCollectionFactory = $searchCollectionFactory;
    }

    private function getSearchTerms(bool $popularFilter, bool $recentFilter): array
    {
        $searchCollection = $this->searchCollectionFactory->create()->setPageSize(5);

        if ($popularFilter) {
            $searchCollection->setPopularQueryFilter();
        }

        if ($recentFilter) {
            $searchCollection->setRecentQueryFilter();
        }

        $searchTerms = [];
        foreach ($searchCollection as $searchTerm) {
            $searchTerms[] = [
                'search_term' => $searchTerm->getQueryText(),
                'results' => $searchTerm->getNumResults(),
                'uses' => $searchTerm->getPopularity()
            ];
        }
        return $searchTerms;
    }

    private function getLastOrders(): array
    {
        $lastOrdersCollection = $this->orderCollectionFactory->create()
            ->addItemCountExpr()
            ->joinCustomerName('customer')
            ->orderByCreatedAt()
            ->addRevenueToSelect(true)
            ->setPageSize(5);

        $lastOrders = [];
        foreach ($lastOrdersCollection as $item) {
            $item->getCustomer() ?: $item->setCustomer($item->getBillingAddress()->getName());

            $lastOrders[] = [
                'customer_name' => $item->getCustomer(),
                'num_items' => $item->getItemsCount(),
                'total' => $this->currency->format($item->getRevenue(), [], false)
            ];
        }
        return $lastOrders;
    }

    private function getSales(): array
    {
        $sales = $this->orderCollectionFactory->create()
            ->calculateSales()
            ->getFirstItem();

        return [
            'lifetime_sales' => $this->currency->format($sales->getLifetime(), [], false),
            'average_order' => $this->currency->format($sales->getAverage(), [], false)
        ];
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if ($context->getUserType() !== UserContextInterface::USER_TYPE_ADMIN) {
            throw new GraphQlAuthorizationException(__('Admin authorization required.'));
        }
        return [
            'sales' => $this->getSales(),
            'last_orders' => $this->getLastOrders(),
            'last_search_terms' => $this->getSearchTerms(false, true),
            'top_search_terms' => $this->getSearchTerms(true, false)
        ];
    }
}
