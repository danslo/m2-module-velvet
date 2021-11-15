<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Danslo\Velvet\Model\Authorization;
use Danslo\Velvet\Model\Configuration;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Section implements ResolverInterface
{
    private Configuration $configuration;
    private Authorization $authorization;

    public function __construct(Configuration $configuration, Authorization $authorization)
    {
        $this->configuration = $configuration;
        $this->authorization = $authorization;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);

        $section = $this->configuration->getAdminhtmlConfigStructure()->getElement($args['section']);

        if ($section->hasChildren() === false) {
            return [];
        }

        $groups = [];
        foreach ($section->getChildren() as $group) {
            $fields = [];
            foreach ($group->getChildren() as $field) {
                if (!($field instanceof \Magento\Config\Model\Config\Structure\Element\Field)) {
                    // TODO: Some modules place groups inside groups, handle this edge case.
                    continue;
                }
                $fields[] = [
                    'label' => (string) $field->getLabel(),
                    'type' => $field->getType(),
                    'comment' => ((string) $field->getComment()) ?: null
                ];
            }
            $groups[] = [
                'label' => (string) $group->getLabel(),
                'fields' => $fields
            ];
        }

        return $groups;
    }
}
