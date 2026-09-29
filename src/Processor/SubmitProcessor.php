<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * A step in the pipeline that the SubmitHandler runs for each form submission,
 * ordered by priority. A processor rejects a submission by throwing an
 * exception, which is transformed into an API problem response.
 *
 * A processor may set FormSubmission::$approved for a trusted submission.
 * Abuse protection (IP blocking, rate limiting) is then skipped, but
 * validation and delivery still run.
 */
#[AutoconfigureTag('atoolo_form.processor')]
interface SubmitProcessor
{
    /**
     * @param array<string,mixed> $options
     */
    public function process(FormSubmission $submission, array $options): FormSubmission;
}
