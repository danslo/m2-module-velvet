<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Configuration;

use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Config\ScopeInterfaceFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Config\Model\Config\Structure\DataFactory as StructureDataFactory;

class Tabs implements ResolverInterface
{
    private StructureFactory $structureFactory;
    private StructureDataFactory $structureDataFactory;
    private ScopeInterfaceFactory $scopeFactory;

    public function __construct(
        StructureFactory $structureFactory,
        StructureDataFactory $structureDataFactory,
        ScopeInterfaceFactory $scopeFactory
    ) {
        $this->structureFactory = $structureFactory;
        $this->structureDataFactory = $structureDataFactory;
        $this->scopeFactory = $scopeFactory;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $configScope = $this->scopeFactory->create();
        $configScope->setCurrentScope(Area::AREA_ADMINHTML);
        $structureData = $this->structureDataFactory->create([
            'configScope' => $configScope
        ]);
        $structure = $this->structureFactory->create([
            'structureData' => $structureData
        ]);

        $structure->getTabs()->rewind();;
        foreach ($structure->getTabs() as $tab) {
            echo $tab->getLabel();
        }

        return [
            [
                'title' => 'General',
                'sections' => [
                    'path' => 'web',
                    'label' => 'Web'
                ]
            ]
        ];
    }
}
