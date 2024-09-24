<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\Email;

/**
 * @codeCoverageIgnore
 */
class EmailHtmlMessageRendererResult
{
    public function __construct(
        public readonly string $subject,
        public readonly string $html,
        public readonly array $attachments,
    ) {}
}
