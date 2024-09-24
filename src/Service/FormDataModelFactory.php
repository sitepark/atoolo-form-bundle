<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Layout;

class FormDataModelFactory implements FromReaderHandler
{
    /**
     * @var array<EmailMessageModelItem>
     */
    private array $items = [];

    /**
     * @var array<EmailMessageModelItem|array<EmailMessageModelItem>>
     */
    private array $stack = [];

    private bool $includeEmptyFields = false;

    public function __construct(
        private readonly DataUrlParser $dataUrlParser,
    ) {}

    /**
     * @param array<string,mixed> $data
     * @return array<EmailMessageModelItem>
     */
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
        ];
        if (!empty($layout->label) && ($layout->options['hideLabel'] ?? false) === false) {
            $data['label'] = $layout->label;
        }

        $this->stack[] = $this->items;
        $this->stack[] = $data;
        $this->items = [];
    }

    public function endLayout(Layout $layout): void
    {
        /** @var EmailMessageModelItem $data */
        $data = array_pop($this->stack);
        $data['items'] = $this->items;
        /** @var array<EmailMessageModelItem> $item */
        $item = array_pop($this->stack);
        $this->items = $item;
        $this->items[] = $data;
    }

    /**
     * @param Control $control
     * @param JsonSchema $schema
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function control(
        Control $control,
        array $schema,
        string $name,
        mixed $value,
    ): void {
        if (empty($value)) {
            if (!$this->includeEmptyFields) {
                return;
            }
            $value = '';
        }

        $type = $this->identifyType($control, $schema);
        if ($type === 'file' && !empty($value)) {
            /** @var string $value */
            $uploadFile = $this->dataUrlParser->parse($value);
            /** @var EmailMessageModelFileUpload $value */
            $value = get_object_vars($uploadFile);
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
            if (($schema['type'] ?? '') === 'string') {
                $value = $value[0] ?? '';
            }
        }

        $item = [
            'type' => $type,
            'name' => $name,
        ];
        if (!empty($control->label)) {
            $item['label'] = $control->label;
        }
        if (!empty($control->htmlLabel)) {
            $item['htmlLabel'] = $control->htmlLabel;
        }
        if (!empty($value)) {
            $item['value'] = $value;
        }
        if (!empty($options)) {
            $item['options'] = $options;
        }

        $this->items[] = $item;
    }

    /**
     * @param JsonSchema $schema
     */
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

        if ($type === 'string' && isset($schema['oneOf'])
        ) {
            return 'select';
        }

        return 'text';
    }
}
