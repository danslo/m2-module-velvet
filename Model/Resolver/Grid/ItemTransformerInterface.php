<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver\Grid;

interface ItemTransformerInterface
{
    public function transform(array $data): array;
}
