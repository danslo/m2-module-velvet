<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Danslo\Velvet\Model\Authorization;
use Magento\Config\Block\System\Config\Form;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\Group;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;

class Scopes implements ResolverInterface
{
    private Authorization $authorization;
    private StoreManagerInterface $storeManager;

    public function __construct(Authorization $authorization, StoreManagerInterface $storeManager)
    {
        $this->authorization = $authorization;
        $this->storeManager = $storeManager;
    }

    private function getStoresFromGroup(Group $group): array
    {
        $stores = [];
        foreach ($group->getStores() as $store) {
            $stores[] = [
                'name'     => $store->getName(),
                'type'     => Form::SCOPE_STORES,
                'scope_id' => $store->getId(),
                'disabled' => false,
                'children' => []
            ];
        }
        return $stores;
    }

    private function getStoreGroupsFromWebsite(Website $website): array
    {
        $groups = [];
        foreach ($website->getGroups() as $group) {
            $groups[] = [
                'name'     => $group->getName(),
                'type'     => 'groups',
                'scope_id' => $group->getId(),
                'disabled' => true,
                'children' => $this->getStoresFromGroup($group)
            ];
        }
        return $groups;
    }

    private function getWebsites(): array
    {
        $websites = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $websites[] = [
                'name'     => $website->getName(),
                'type'     => Form::SCOPE_WEBSITES,
                'scope_id' => $website->getId(),
                'disabled' => false,
                'children' => $this->getStoreGroupsFromWebsite($website)
            ];
        }
        return $websites;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);
        return [
            [
                'name'     => 'Default',
                'type'     => Form::SCOPE_DEFAULT,
                'scope_id' => null,
                'disabled' => false,
                'children' => $this->getWebsites()
            ]
        ];
    }
}
