<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

class Control extends Element
{
    /**
     * @param string|null $scope https://jsonforms.io/docs/uischema/controls#scope-string
     * @param string|bool|null $label https://jsonforms.io/docs/uischema/controls#label-string--boolean
     * @param array<string,mixed>|null $htmlLabel CMS specific property
     * @param array<string,mixed> $options https://jsonforms.io/docs/uischema/controls#options
     */
    public function __construct(
        public ?string $scope = null,
        public string|bool|null $label = null,
        public ?array $htmlLabel = null, // custom property
        public readonly array $options = [],
    ) {
        parent::__construct(Type::CONTROL);
        if (
            $this->scope !== null
            && !preg_match('/^#(?:\/[^\/"]+)+$/', $this->scope)
        ) {
            throw new \InvalidArgumentException('Invalid scope: ' . $this->scope);
        }
    }
}
