<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;

class FormDataModelFactory
{
    public function __construct(
        private readonly DataUrlParser $dataUrlParser,
    ) {}

    /**
     * @param array<string,mixed> $data
     * @return array<EmailMessageModelItem>
     */
    public function create(FormDefinition $definition, array $data, bool $includeEmptyFields): array
    {
        $collector = new FormDataModelCollector($this->dataUrlParser, $includeEmptyFields);
        $reader = new FormReader($definition, $data, $collector);
        $reader->read();

        return $collector->getItems();
    }
}
