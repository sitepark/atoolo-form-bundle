<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator;

use stdClass;

interface FormatConstraint extends Constraint
{
    public function getType(): string;
    public function getName(): string;
    public function check(mixed $value, stdClass $schema): bool;
}
