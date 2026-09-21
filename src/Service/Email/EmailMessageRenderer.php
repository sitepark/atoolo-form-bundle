<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;

abstract class EmailMessageRenderer
{
    /**
     * @param EmailMessageModel $model
     * @return EmailHtmlMessageRendererResult
     */
    abstract public function render(string $format, array $model): EmailHtmlMessageRendererResult;

    /**
     * @param array<EmailMessageModelItem> $model
     * @return array<EmailMessageModelFileUpload>
     */
    protected function findAttachments(array $model): array
    {
        return array_map(static function (array $element) {
            /** @var EmailMessageModelFileUpload $upload */
            $upload = $element['value'];
            return $upload;
        }, $this->findByType($model, 'file'));
    }

    /**
     * @param array<array<string,mixed>> $model
     * @return list<array<string,mixed>>
     */
    protected function findByType(array $model, string $type): array
    {
        $results = [];
        foreach ($model as $item) {
            if (($item['type'] ?? '') === $type) {
                $results[] =  [$item];
            } elseif (isset($item['items']) && is_array($item['items'])) {
                /** @var array<array<string,mixed>> $nested */
                $nested = $item['items'];
                $results[] = $this->findByType($nested, $type);
            }
        }

        return array_merge(...$results);
    }
}
