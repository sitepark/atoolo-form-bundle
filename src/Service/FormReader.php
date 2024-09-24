<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Element;
use Atoolo\Form\Dto\UISchema\Layout;

class FormReader
{
    public function __construct(
        public readonly FormDefinition $formDefinition,
        public readonly array $data,
        public readonly FromReaderHandler $handler,
    ) {}

    public function read(): void
    {
        $this->readElement($this->formDefinition->uischema);
    }

    private function readElement(Element $element): void
    {
        if ($element instanceof Control) {
            $this->readControl($element);
        } elseif ($element instanceof Layout) {
            $this->readLayout($element);
        }
    }

    private function readControl(Control $control): void
    {
        if ($control->scope === null) {
            return;
        }

        [$schema, $name, $data] = $this->getValue($control->scope);
        if ($schema === null) {
            return;
        }
        $this->handler->control($control, $schema, $name, $data);
    }

    private function readLayout(Layout $layout): void
    {
        $this->handler->startLayout($layout);
        foreach ($layout->elements as $element) {
            $this->readElement($element);
        }
        $this->handler->endLayout($layout);
    }

    private function getValue(string $scope): ?array
    {
        $keys = explode('/', ltrim($scope, '#'));
        $name = end($keys);

        $data = $this->data;

        $schema = $this->formDefinition->schema;

        foreach ($keys as $key) {

            if (empty($key)) {
                continue;
            }

            if ($schema !== null && isset($schema[$key])) {
                $schema = $schema[$key];
            } else {
                $schema = null;
            }

            if ($key === 'properties') {
                continue;
            }
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                $data = null;
                break;
            }
        }

        return [$schema, $name, $data];
    }
}
