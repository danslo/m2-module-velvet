<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver;

use Danslo\Velvet\Model\Authorization;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Dashboard implements ResolverInterface
{
    private Authorization $authorization;

    public function __construct(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);
        return [];
    }
}
