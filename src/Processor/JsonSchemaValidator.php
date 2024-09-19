<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsTaggedItem(index: 'json-schema-validator')]
class JsonSchemaValidator implements SubmitProcessor
{
    public function __construct(private readonly \Atoolo\Form\Service\JsonSchemaValidator $validator) {}

    /**
     * @throws ValidationFailedException
     */
    public function process(FormSubmission $submission, SubmitProcessorOptions $options): FormSubmission
    {
        $this->validator->validate($submission->formDefinition->schema, $submission->data);
        return $submission;
    }
}
