<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Cms\Page;

use Danslo\Velvet\Api\AdminAuthorizationInterface;
use Magento\Cms\Model\ResourceModel\Page\Grid\CollectionFactory as CmsPageGridCollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Grid implements ResolverInterface, AdminAuthorizationInterface
{
    private CmsPageGridCollectionFactory $cmsPageGridCollectionFactory;
    private int $defaultPageSize;

    public function __construct(CmsPageGridCollectionFactory $cmsPageGridCollectionFactory, int $defaultPageSize = 20)
    {
        $this->cmsPageGridCollectionFactory = $cmsPageGridCollectionFactory;
        $this->defaultPageSize = $defaultPageSize;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $collection = $this->cmsPageGridCollectionFactory->create()
            ->setCurPage($args['input']['page_number'] ?? 0)
            ->setPageSize($args['input']['page_size'] ?? $this->defaultPageSize)
            ->addOrder('creation_time');

        $cmsPages = [];
        foreach ($collection as $cmsPage) {
            $cmsPages[] = $cmsPage->getData();
        }
        return [
            'pages' => $cmsPages,
            'last_page_number' => $collection->getLastPageNumber(),
            'total_pages' => $collection->getTotalCount()
        ];
    }
}
