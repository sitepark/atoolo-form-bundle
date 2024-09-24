<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailHtmlMessageTwigRenderer implements EmailHtmlMessageRenderer
{
    public function __construct(
        private readonly Environment $twig,
    ) {}

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(array $model): EmailHtmlMessageRendererResult
    {
        $html = $this->twig->render('@AtooloForm/email.html.twig', $model);

        return new EmailHtmlMessageRendererResult(
            subject: '',
            html: $html,
            attachments: $this->findAttachments($model['items']),
        );
    }

    private function findAttachments(array $model): array
    {
        $attachments = [];
        foreach ($model as $item) {
            $type = $item['type'] ?? '';
            if ($type === 'file') {
                $attachments[] = $item['value'];
            }
        }

        return $attachments;
    }
}
