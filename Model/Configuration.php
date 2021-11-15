<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\DataFactory as StructureDataFactory;
use Magento\Config\Model\Config\StructureFactory;
use Magento\Framework\App\Area;
use Magento\Framework\Config\ScopeInterfaceFactory;

class Configuration
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

    public function getAdminhtmlConfigStructure(): Structure
    {
        $configScope = $this->scopeFactory->create();
        $configScope->setCurrentScope(Area::AREA_ADMINHTML);
        $structureData = $this->structureDataFactory->create(['configScope' => $configScope]);
        return $this->structureFactory->create(['structureData' => $structureData]);
    }
}
