<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailMessageRendererResult;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class EmailMessageTwigRenderer extends EmailMessageRenderer
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
    public function render(string $format, array $model): EmailMessageRendererResult
    {
        $html = $this->twig->render('@AtooloForm/email.' . $format . '.twig', $model);

        return new EmailMessageRendererResult(
            message: $html,
            attachments: $this->findAttachments($model['items']),
        );
    }
}
