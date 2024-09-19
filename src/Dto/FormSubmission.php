<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto;

class FormSubmission
{
    public bool $approved = false;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $processors
     */
    public function __construct(
        public readonly string $remoteAddress,
        public readonly FormDefinition $formDefinition,
        public readonly \stdClass $data,
    ) {}
}
