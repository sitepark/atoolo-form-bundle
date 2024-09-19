<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'spam-detector')]
class SpamDetector implements SubmitProcessor
{
    public function process(FormSubmission $submission, SubmitProcessorOptions $options): FormSubmission
    {
        if ($submission->approved) {
            return $submission;
        }

        return $submission;
    }
}
