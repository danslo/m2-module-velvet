<?php

namespace Danslo\Velvet\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Dashboard implements ResolverInterface
{
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return [
            'sales' => [
                'lifetime_sales' => '$29.00',
                'average_order' => '$14.50'
            ],
            'last_orders' => [
                ['customer_name' => 'Foo Bar', 'num_items' => 5, 'total' => '$56.99']
            ],
            'last_search_terms' => [
                ['search_term' => 'cool', 'results' => 10, 'uses' => 5],
                ['search_term' => 'bad', 'results' => 20, 'uses' => 2],
                ['search_term' => 'fun', 'results' => 30, 'uses' => 1],
            ],
            'top_search_terms' => [
                ['search_term' => 'abc', 'results' => 5, 'uses' => 3],
                ['search_term' => 'def', 'results' => 10, 'uses' => 1]
            ]
        ];
    }
}
