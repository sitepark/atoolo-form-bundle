<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\Email;

class EmailHtmlMessageRendererResult
{
    public function __construct(
        public readonly string $subject,
        public readonly string $html,
        public readonly array $attachments,
    ) {}
}
