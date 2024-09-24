<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\Email;

use Atoolo\Form\Dto\FormData\UploadFile;

/**
 * @codeCoverageIgnore
 */
class EmailHtmlMessageRendererResult
{
    /**
     * @param array<EmailMessageModelFileUpload> $attachments
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $html,
        public readonly array $attachments,
    ) {}
}
