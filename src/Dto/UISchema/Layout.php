<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

/**
 * @codeCoverageIgnore
 */
class Layout extends Element
{
    /**
     * @param Type $type
     * @param array<Element> $elements
     * @param string|bool|null $label
     * @param array<string,mixed> $options
     */
    public function __construct(
        Type $type,
        public readonly array $elements = [],
        public readonly string|bool|null $label = null,
        public readonly array $options = [],
    ) {
        parent::__construct($type);
    }
}
