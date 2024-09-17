<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Processor\SubmitProcessor;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SubmitHandler
{

    /**
     * @var array<string, SubmitProcessor>
     */
    private readonly array $processors;

    /**
     * @param array<SubmitProcessor> $processors
     */
    public function __construct(#[AutowireIterator('atoolo_form.processor', indexAttribute: 'key')] iterable $processors)
    {
        $this->processors = $processors instanceof \Traversable ?
            iterator_to_array($processors) :
            $processors;
    }

    public function handle(FormSubmission $submit): void
    {
        foreach ($submit->processors as $key => $options) {
            if (!isset($this->processors[$key])) {
                throw new \RuntimeException(sprintf('Processor "%s" not found', $key));
            }
            $processor = $this->processors[$key];
            $submit = $processor->process($submit, $options);
        }
    }
}
