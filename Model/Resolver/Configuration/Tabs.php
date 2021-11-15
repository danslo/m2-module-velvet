<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Danslo\Velvet\Model\Authorization;
use Danslo\Velvet\Model\Configuration;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Tabs implements ResolverInterface
{
    private Configuration $configuration;
    private Authorization $authorization;

    public function __construct(Configuration $configuration, Authorization $authorization)
    {
        $this->configuration = $configuration;
        $this->authorization = $authorization;
    }

    private function getConfigurationTabs(): array
    {
        $tabs = [];
        foreach ($this->configuration->getAdminhtmlConfigStructure()->getTabs() as $tab) {
            $sections = [];
            foreach ($tab->getChildren() as $section) {
                $sections[] = ['label' => $section->getLabel(), 'path' => $section->getId()];
            }
            $tabs[] = ['label' => $tab->getLabel(), 'sections' => $sections];
        }
        return $tabs;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);
        return $this->getConfigurationTabs();
    }
}
