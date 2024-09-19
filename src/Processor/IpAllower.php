<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'ip-allower', priority: 100)]
class IpAllower implements SubmitProcessor
{
    private array $allowedIps = [];

    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        if (in_array($submission->remoteAddress, $this->allowedIps, true)) {
            $submission->approved = true;
        }
        return $submission;
    }
}
