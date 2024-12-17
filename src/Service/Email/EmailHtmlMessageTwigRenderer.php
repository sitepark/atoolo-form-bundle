<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailHtmlMessageTwigRenderer extends EmailHtmlMessageRenderer
{
    public function __construct(
        private readonly Environment $twig,
    ) {}

    /**
     * @param EmailMessageModel $model
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(array $model): EmailHtmlMessageRendererResult
    {
        $html = $this->twig->render('@AtooloForm/email.html.twig', $model);

        return new EmailHtmlMessageRendererResult(
            html: $html,
            attachments: $this->findAttachments($model['items']),
        );
    }
}
