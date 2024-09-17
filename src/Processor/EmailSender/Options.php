<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor\EmailSender;

use Atoolo\Form\Processor\SubmitProcessorOptions;
use Symfony\Component\Mime\Address;

class Options extends SubmitProcessorOptions
{
    /**
     * @param array<Address> $from
     * @param array<Address> $to
     */
    public function __construct(
        public readonly array $from,
        public readonly array $to,
        public readonly string $subject,
        public readonly string $format,
        public readonly bool $attachCsv,
        public readonly bool $showEmpty,
    ) {}
}
