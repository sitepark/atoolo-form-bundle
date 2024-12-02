<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;

interface EmailHtmlMessageRenderer
{
    /**
     * @param EmailMessageModel $model
     * @return EmailHtmlMessageRendererResult
     */
    public function render(array $model): EmailHtmlMessageRendererResult;
}
