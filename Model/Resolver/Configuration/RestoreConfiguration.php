<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Danslo\Velvet\Model\Authorization;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Config\Model\ResourceModel\Config\Data as ConfigDataResource;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory as ConfigDataCollectionFactory;

class RestoreConfiguration implements ResolverInterface
{
    private Authorization $authorization;
    private ConfigDataResource $configDataResource;
    private ConfigDataCollectionFactory $configDataCollectionFactory;

    public function __construct(
        Authorization $authorization,
        ConfigDataResource $configDataResource,
        ConfigDataCollectionFactory $configDataCollectionFactory
    ) {
        $this->authorization = $authorization;
        $this->configDataResource = $configDataResource;
        $this->configDataCollectionFactory = $configDataCollectionFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);

        $collection = $this->configDataCollectionFactory->create()
            // todo: add scopes
            ->addPathFilter($args['path']);

        foreach ($collection as $config) {
            $this->configDataResource->delete($config);
        }
    }
}
