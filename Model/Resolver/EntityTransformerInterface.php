<?php

declare(strict_types=1);

namespace Danslo\Velvet\Model\Resolver;

interface EntityTransformerInterface
{
    public function transform(array $data): array;
}
