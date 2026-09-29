<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Marker interface for custom JSON schema constraints. Implementations are
 * tagged automatically and registered with the JsonSchemaValidator, which
 * uses them to validate submitted form data beyond the JSON schema standard.
 */
#[AutoconfigureTag('atoolo_form.jsonSchemaConstraint')]
interface Constraint {}
