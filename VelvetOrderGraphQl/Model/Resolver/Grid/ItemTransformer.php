<?php

declare(strict_types=1);

namespace Danslo\VelvetOrderGraphQl\Model\Resolver\Grid;

use Danslo\VelvetGraphQl\Api\ItemTransformerInterface;
use Magento\Directory\Model\Currency;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\System\Store as SystemStore;

class ItemTransformer implements ItemTransformerInterface
{
    private Currency $currency;
    private array $currencyFormatFields;
    private SystemStore $store;

    public function __construct(
        Currency $currency,
        SystemStore $store,
        array $currencyFormatFields = []
    ) {
        $this->currency = $currency;
        $this->currencyFormatFields = $currencyFormatFields;
        $this->store = $store;
    }

    private function getStorenameFromStoreId(int $storeId): string
    {
        return $this->store->getStoreName($storeId);
    }

    public function transform(AbstractModel $model, array $data): array
    {
        foreach ($this->currencyFormatFields as $field) {
            if (!isset($data[$field])) {
                continue;
            }
            $data['formatted_' . $field] = $this->currency->format($data[$field], [], false);
        }

        $data['store_name'] = $this->getStorenameFromStoreId((int) $data['store_id']);

        return $data;
    }
}
