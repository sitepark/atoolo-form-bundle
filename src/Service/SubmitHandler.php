<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Processor\SubmitProcessor;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
    public function __construct(
        #[AutowireIterator('atoolo_form.processor', indexAttribute: 'key')]
        iterable $processors,
        #[Autowire(param: 'atoolo_form.default_processors')]
        private readonly array $defaultProcessorKeys,
    ) {
        $this->processors = $processors instanceof \Traversable ?
            iterator_to_array($processors) :
            $processors;
    }

    public function handle(FormSubmission $submit): void
    {
        $processorsOptions = $this->getResultingOptions($submit->formDefinition->processors);

        foreach ($this->processors as $key => $processor) {

            if (!isset($processorsOptions[$key])) {
                continue;
            }

            $options = $processorsOptions[$key];

            $submit = $processor->process($submit, $options);
        }
    }

    private function getResultingOptions(array $processors): array
    {
        $resultingProcessors = [];

        foreach ($this->defaultProcessorKeys as $key => $options) {
            $resultingProcessors[$key] = $options ?? [];
        }
        foreach ($processors as $key => $options) {
            $resultingProcessors[$key] = array_merge($resultingProcessors[$key] ?? [], $options);
        }

        return $resultingProcessors;
    }
}
