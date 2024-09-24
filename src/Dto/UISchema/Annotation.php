<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

/**
 * @codeCoverageIgnore
 */
class Annotation extends Element
{
    /**
     * @param array<string,mixed>|null $options
     */
    public function __construct(
        public array $value = [],
        public readonly array $options = [],
    ) {
        parent::__construct(Type::ANNOTATION);
    }
}
