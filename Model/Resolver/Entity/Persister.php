<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Entity;

use Danslo\Velvet\Api\AdminAuthorizationInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Persister implements ResolverInterface, AdminAuthorizationInterface
{
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        throw new \Exception('Persistence not implemented yet.');
    }
}
