<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Atoolo\Form\Service\Email\EmailHtmlMessageTwigRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

#[CoversClass(EmailHtmlMessageTwigRenderer::class)]
class EmailHtmlMessageTwigRendererTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testRender(): void
    {
        $twig = $this->createStub(Environment::class);
        $twig->method('render')
            ->willReturn('html');

        $renderer = new EmailHtmlMessageTwigRenderer($twig);

        $model = [
            'items' => [
                [
                    'type' => 'file',
                    'value' => 'file1',
                ],
            ],
        ];

        $expected = new EmailHtmlMessageRendererResult(
            html: 'html',
            attachments: ['file1'],
        );

        $this->assertEquals($expected, $renderer->render($model), 'unexpected result');
    }

}
