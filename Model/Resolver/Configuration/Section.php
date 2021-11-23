<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Danslo\Velvet\Model\Authorization;
use Danslo\Velvet\Model\Configuration;
use Magento\Config\App\Config\Type\System;
use Magento\Config\Block\System\Config\Form;
use Magento\Config\Model\Config\Factory as ConfigFactory;
use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Config\Model\Config\Structure\Element\Field as ConfigField;
use Magento\Config\Model\Config\Structure\Element\Group;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Section implements ResolverInterface
{
    private Configuration $configuration;
    private Authorization $authorization;
    private ConfigFactory $configFactory;
    private ScopeConfigInterface $scopeConfig;
    private SettingChecker $settingChecker;
    private DeploymentConfig $deploymentConfig;

    public function __construct(
        Configuration $configuration,
        Authorization $authorization,
        ConfigFactory $configFactory,
        ScopeConfigInterface $scopeConfig,
        SettingChecker $settingChecker,
        DeploymentConfig $deploymentConfig
    ) {
        $this->configuration = $configuration;
        $this->authorization = $authorization;
        $this->configFactory = $configFactory;
        $this->scopeConfig = $scopeConfig;
        $this->settingChecker = $settingChecker;
        $this->deploymentConfig = $deploymentConfig;
    }

    private function getScope()
    {
        // todo
        return Form::SCOPE_DEFAULT;
    }

    public function canUseDefaultValue($fieldValue)
    {
        if ($this->getScope() == Form::SCOPE_STORES && $fieldValue) {
            return true;
        }
        if ($this->getScope() == Form::SCOPE_WEBSITES && $fieldValue) {
            return true;
        }
        return false;
    }

    public function canUseWebsiteValue($fieldValue)
    {
        if ($this->getScope() == Form::SCOPE_STORES && $fieldValue) {
            return true;
        }
        return false;
    }

    public function isCanRestoreToDefault($fieldValue)
    {
        if ($this->getScope() == Form::SCOPE_DEFAULT && $fieldValue) {
            return true;
        }
        return false;
    }

    private function isInheritCheckboxRequired(ConfigField $field)
    {
        return $this->canUseDefaultValue($field->showInDefault()) ||
            $this->canUseWebsiteValue($field->showInWebsite()) ||
            $this->isCanRestoreToDefault($field->canRestore());
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->authorization->validate($context);

        /** @var \Magento\Config\Model\Config\Structure\Element\Section $section */
        $section = $this->configuration->getAdminhtmlConfigStructure()->getElement($args['section']);
        if ($section->hasChildren() === false) {
            return [];
        }

        $configDataObject = $this->configFactory->create(
            [
                'data' => [
                    'section' => $section->getId(),
                    //'website' => $this->getWebsiteCode(),
                    //'store' => $this->getStoreCode(),
                ],
            ]
        );

        $configData = $configDataObject->load();

        $groups = [];
        /** @var Group $group */
        foreach ($section->getChildren() as $group) {
            $fields = [];
            foreach ($group->getChildren() as $field) {
                if (!($field instanceof ConfigField)) {
                    // TODO: handle groups inside of groups
                    continue;
                }

                $path = $field->getPath();
                $data = $this->getFieldData($configData, $field, $path);
                if (is_array($data)) {
                    // TODO: handle multi dimensional configuration
                    continue;
                }

                $options = $this->getOptionsFromField($field);
                $inheritRequired = $this->isInheritCheckboxRequired($field);
                $fields[] = [
                    'label' => (string) $field->getLabel(),
                    'type' => $field->getType(),
                    'comment' => ((string) $field->getComment()) ?: null,
                    'options' =>  $options,
                    'value' => $data ?? ($field->hasOptions() ? $options[0]['value'] : null),
                    'inherit' => $inheritRequired && !array_key_exists($path, $configData),
                    'show_inherit' => $inheritRequired,
                    'path' => $path
                ];
            }
            $groups[] = [
                'label' => (string) $group->getLabel(),
                'fields' => $fields
            ];
        }

        return $groups;
    }

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            //$this->getScope(),
            //$this->getScopeCode()
        );
    }

    private function getAppConfigDataValue($path)
    {
        $appConfig = $this->deploymentConfig->get(System::CONFIG_TYPE);

        //$scope = $this->getScope();
        //$scopeCode = $this->getStringScopeCode();
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $scopeCode = '';

        if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
            $data = new DataObject(isset($appConfig[$scope]) ? $appConfig[$scope] : []);
        } else {
            $data = new DataObject(isset($appConfig[$scope][$scopeCode]) ? $appConfig[$scope][$scopeCode] : []);
        }
        return $data->getData($path);
    }

    private function getFieldData(array $configData, ConfigField $field, $path)
    {
        $data = $this->getAppConfigDataValue($path);

        $placeholderValue = $this->settingChecker->getPlaceholderValue(
            $path,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            //$this->getScope(),
            //$this->getStringScopeCode()
        );

        if ($placeholderValue) {
            $data = $placeholderValue;
        }

        if ($data === null) {
            $path = $field->getConfigPath() !== null ? $field->getConfigPath() : $path;
            $data = $this->getConfigValue($path);
            if ($field->hasBackendModel()) {
                $backendModel = $field->getBackendModel();
                if (!$backendModel instanceof ProcessorInterface) {
                    if (array_key_exists($path, $configData)) {
                        $data = $configData[$path];
                    }

                    $backendModel->setPath($path)
                        ->setValue($data)
                        //->setWebsite($this->getWebsiteCode())
                        //->setStore($this->getStoreCode())
                        ->afterLoad();
                    $data = $backendModel->getValue();
                }
            }
        }

        return $data;
    }

    private function getOptionsFromField($field): array
    {
        $options = [];
        if ($field->hasOptions()) {
            foreach ($field->getOptions() as $k => $v) {
                if (is_array($v)) {
                    $v['label'] = (string)$v['label'];
                    $options[] = $v;
                } else {
                    $options[] = ['value' => (string) $k, 'label' => (string)$v];
                }
            }
        }
        return $options;
    }
}
