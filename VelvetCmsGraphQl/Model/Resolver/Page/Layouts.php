<?php

declare(strict_types=1);

namespace Danslo\VelvetCmsGraphQl\Model\Resolver\Page;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Cms\Model\Page\Source\PageLayout;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Layouts implements ResolverInterface, AdminAuthorizationInterface
{
    private PageLayout $pageLayout;

    public function __construct(PageLayout $pageLayout)
    {
        $this->pageLayout = $pageLayout;
    }

    public function getResource(): string
    {
        return 'Magento_Cms::page';
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->pageLayout->toOptionArray();
    }
}
