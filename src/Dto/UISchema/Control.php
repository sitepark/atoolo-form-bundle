<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

class Control extends Element
{
    /**
     * @param array<string,mixed> $options
     * @param array<string,mixed>|null $htmlLabel
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
