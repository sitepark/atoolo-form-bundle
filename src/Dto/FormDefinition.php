<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto;

use Atoolo\Form\Dto\UISchema\Element;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * @codeCoverageIgnore
 */
class FormDefinition
{
    /**
     * @param array<string, mixed> $schema
     * @param array<string, mixed> $data Default values of form fields that should be prefilled
     */
    public function __construct(
        public readonly array $schema,
        public readonly Element $uischema,
        public readonly ?array $data,
        public readonly ?array $buttons,
        public readonly ?array $messages,
        public readonly string $component,
        #[Ignore]
        public readonly ?array $processors,
    ) {}
}
