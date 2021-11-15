<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Danslo\Velvet\Model\Authorization;
use Danslo\Velvet\Model\Configuration;
use Magento\Config\Block\System\Config\FormFactory;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Section implements ResolverInterface
{
    private Configuration $configuration;
    private Authorization $authorization;
    private FormFactory $formFactory;
    private \Magento\Framework\Data\Form\Element\FieldsetFactory $fieldsetFactory;

    public function __construct(
        Configuration $configuration,
        Authorization $authorization,
        FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\FieldsetFactory $fieldsetFactory
    ) {
        $this->configuration = $configuration;
        $this->authorization = $authorization;
        $this->formFactory = $formFactory;
        $this->fieldsetFactory = $fieldsetFactory;
    }

    private function generateElementId(string $path)
    {
        return str_replace('/', '_', $path);
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);

        /** @var \Magento\Config\Model\Config\Structure\Element\Section $section */
        $section = $this->configuration->getAdminhtmlConfigStructure()->getElement($args['section']);

        if ($section->hasChildren() === false) {
            return [];
        }

        $groups = [];

        /** @var Group $group */
        foreach ($section->getChildren() as $group) {
            /** @var Fieldset $fieldset */
            $fieldset = $this->fieldsetFactory->create();

            $form = $this->formFactory->create();
            try {
                $form->initFields($fieldset, $group, $section);
            } catch (\Exception $e) {
                // todo: why sometimes?
            }

            $fields = [];
            foreach ($group->getChildren() as $field) {

                if (!($field instanceof \Magento\Config\Model\Config\Structure\Element\Field)) {
                    // TODO: Some modules place groups inside groups, handle this edge case.
                    continue;
                }


                $value = null;
                $elementId = $this->generateElementId($field->getPath());
                if ($elementId) {
                    foreach ($fieldset->getElements() as $element) {
                        if ($element->getId() === $elementId) {
                            if (!is_string($element->getValue())) {
                                // TODO: sometimes json?
                                continue;
                            }
                            $value = $element->getValue();
                            break;
                        }
                    }
                }

                $fields[] = [
                    'label' => (string) $field->getLabel(),
                    'type' => $field->getType(),
                    'comment' => ((string) $field->getComment()) ?: null,
                    'options' => $field->hasOptions() ? $field->getOptions() : null,
                    'value' => $value
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
