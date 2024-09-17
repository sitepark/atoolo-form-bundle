<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Layout;

class FormDataModelFactory implements FromReaderHandler
{
    private array $items = [];

    private array $stack = [];

    private bool $includeEmptyFields = false;

    public function __construct(
        private readonly DataUrlParser $dataUrlParser
    ) {}

    public function create(FormDefinition $definition, array $data, bool $includeEmptyFields): array
    {
        $this->stack = [];
        $this->items = [];
        $this->includeEmptyFields = $includeEmptyFields;
        $reader = new FormReader($definition, $data, $this);
        $reader->read();

        return $this->items;
    }

    public function startLayout(Layout $layout): void
    {
        $data = [
            'type' => strtolower($layout->type->name),
            'layout' => true,
            'label' => $layout->label,
        ];
        $this->stack[] = $this->items;
        $this->stack[] = $data;
        $this->items = [];
    }

    public function endLayout(Layout $layout): void
    {
        $data = array_pop($this->stack);
        $data['items'] = $this->items;
        $this->items = array_pop($this->stack);
        $this->items[] = $data;
    }

    public function control(Control $control, array $schema, string $name, mixed $value): void
    {
        if (empty($value)) {
            if (!$this->includeEmptyFields) {
                return;
            }
            $value = '';
        }

        $type = $this->identifyType($control, $schema);
        if ($type === 'file' && !empty($value)) {
            $value = $this->dataUrlParser->parse($value);
        }

        $options = null;

        $optionsFromSchema = $schema['items']['oneOf'] ?? $schema['oneOf'] ?? null;
        if ($optionsFromSchema !== null) {
            $selected = is_array($value) ? $value : [$value];
            $value = [];
            $options = [];
            foreach ($optionsFromSchema as $option) {
                $isSelected = in_array($option['const'], $selected, true);
                $label = $option['title'];
                $options[] = [
                    'label' => $label,
                    'value' => $option['const'],
                    'selected' => $isSelected,
                ];
                if ($isSelected) {
                    $value[] = $label;
                }
            }
        }

        $this->items[] = [
            'type' => $type,
            'name' => $name,
            'label' => $control->label,
            'htmlLabel' => $control->htmlLabel,
            'value' => $value,
            'options' => $options,
        ];
    }

    private function identifyType(Control $control, array $schema): string
    {

        $type = $schema['type'] ?? '';
        $format = $schema['format'] ?? '';

        if ($type === 'string' && $format === 'data-url') {
            return 'file';
        }

        if ($type === 'string' && $format === 'html') {
            return 'html';
        }

        if ($type === 'boolean') {
            return 'checkbox';
        }

        if ($type === 'array' && isset($schema['items']['oneOf'])) {
            return 'checkbox-group';
        }

        if (
            $type === 'string'
            && isset($schema['oneOf'])
            && ($control->options['format'] ?? '') === 'radio') {
            return 'radio-buttons';
        }

        if ($type === 'string' && isset($schema['items']['oneOf'])) {
            return 'select';
        }

        return 'text';
    }
}
