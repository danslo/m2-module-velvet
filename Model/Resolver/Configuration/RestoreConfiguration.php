<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Danslo\Velvet\Model\Authorization;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Config\Model\ResourceModel\Config as ConfigResource;

class RestoreConfiguration implements ResolverInterface
{
    private Authorization $authorization;
    private ConfigResource $configResource;
    private ReinitableConfigInterface $reinitableConfig;

    public function __construct(
        Authorization $authorization,
        ConfigResource $configResource,
        ReinitableConfigInterface $reinitableConfig
    ) {
        $this->authorization = $authorization;
        $this->configResource = $configResource;
        $this->reinitableConfig = $reinitableConfig;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);

        // todo: add scopes
        $this->configResource->deleteConfig($args['path']);

        $this->reinitableConfig->reinit();

        return true;
    }
}
