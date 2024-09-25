<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto;

/**
 * @codeCoverageIgnore
 */
class FormSubmission
{
    public bool $approved = false;

    public function __construct(
        public readonly string $remoteAddress,
        public readonly FormDefinition $formDefinition,
        public readonly object $data,
    ) {}
}
