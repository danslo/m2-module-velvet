<?php

declare(strict_types=1);

namespace Danslo\VelvetCmsGraphQl\Model\Resolver\Page;

use Danslo\VelvetGraphQl\Api\AdminAuthorizationInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\Page\CustomLayoutManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CustomLayouts implements ResolverInterface, AdminAuthorizationInterface
{
    private PageRepositoryInterface $pageRepository;
    private CustomLayoutManagerInterface $customLayoutManager;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        CustomLayoutManagerInterface $customLayoutManager
    ) {
        $this->pageRepository = $pageRepository;
        $this->customLayoutManager = $customLayoutManager;
    }

    public function getResource(): string
    {
        return 'Magento_Cms::page';
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $options = [['label' => 'No update', 'value' => '_no_update_']];

        try {
            $page = $this->pageRepository->getById($value['page_id']);
            if ($page->getCustomLayoutUpdateXml() || $page->getLayoutUpdateXml()) {
                $options[] = ['label' => 'Use existing layout update XML', 'value' => '_existing_'];
            }
            foreach ($this->customLayoutManager->fetchAvailableFiles($page) as $layoutFile) {
                $options[] = ['label' => $layoutFile, 'value' => $layoutFile];
            }
        } catch (LocalizedException $e) {
            // no-op
        }

        return $options;
    }

}
