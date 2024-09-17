<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

class Layout extends Element
{
    /** @var array<Element> */
    public array $elements;

    /**
     * @param Type $type
     * @param array<Element> $elements
     * @param string|bool|null $label
     */
    public function __construct(
        Type $type,
        array $elements = [],
        public string|bool|null $label = null,
    ) {
        parent::__construct($type);
        $this->elements = $elements;
    }
}
