<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @template T of SubmitProcessorOptions
 */
#[AutoconfigureTag('atoolo_form.processor')]
interface SubmitProcessor
{
    public function process(FormSubmission $submission, SubmitProcessorOptions $options): FormSubmission;
}
