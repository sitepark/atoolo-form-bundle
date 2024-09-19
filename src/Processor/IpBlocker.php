<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Exception\AccessDeniedException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(index: 'ip-blocker', priority: 90)]
class IpBlocker implements SubmitProcessor
{
    private array $blockedIps = [];

    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        if ($submission->approved) {
            return $submission;
        }

        if (in_array($submission->remoteAddress, $this->blockedIps, true)) {
            throw new AccessDeniedException();
        }

        return $submission;
    }
}
