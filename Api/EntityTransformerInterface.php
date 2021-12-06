<?php

declare(strict_types=1);

namespace Danslo\Velvet\Api;

interface EntityTransformerInterface
{
    public function transform(array $data): array;
}
