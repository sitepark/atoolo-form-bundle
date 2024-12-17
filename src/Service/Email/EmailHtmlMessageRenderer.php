<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;

abstract class EmailHtmlMessageRenderer
{
    /**
     * @param EmailMessageModel $model
     * @return EmailHtmlMessageRendererResult
     */
    abstract public function render(array $model): EmailHtmlMessageRendererResult;

    /**
     * @param array<EmailMessageModelItem> $model
     * @return array<EmailMessageModelFileUpload>
     */
    protected function findAttachments(array $model): array
    {
        $attachments = [];
        foreach ($model as $item) {
            $type = $item['type'] ?? '';
            if ($type === 'file') {
                /** @var EmailMessageModelControlItem $item */
                /** @var EmailMessageModelFileUpload $value */
                $value = $item['value'];
                $attachments[] = $value;
            }
        }

        return $attachments;
    }
}
