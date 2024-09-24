<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailHtmlMessageTwigMjmlRenderer implements EmailHtmlMessageRenderer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MjmlRenderer $mjmlRenderer,
    ) {}

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(array $model): EmailHtmlMessageRendererResult
    {
        $mjml = $this->twig->render('@AtooloForm/email.mjml.html.twig', $model);
        return new EmailHtmlMessageRendererResult(
            subject: '',
            html: $this->mjmlRenderer->render($mjml),
            attachments: [],
        );
    }
}
