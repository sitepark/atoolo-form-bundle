<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Atoolo\Form\Service\Email\EmailHtmlMessageRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailHtmlMessageRenderer::class)]
class EmailHtmlMessageRendererTest extends TestCase
{
    public function testFindAttachments(): void
    {
        $model = [
            [
                'type' => 'file',
                'value' => [
                    'filename' => 'file1',
                    'contentType' => 'application/pdf',
                    'data' => 'date',
                    'size' => 4,
                ],
            ],
            [
                'type' => 'text',
                'value' => 'text1',
            ],
        ];

        $renderer = new class extends EmailHtmlMessageRenderer {
            /**
             * @param array $model
             * @return array<EmailMessageModelFileUpload>
             */
            public function render(array $model): EmailHtmlMessageRendererResult
            {
                return new EmailHtmlMessageRendererResult(
                    html: 'html',
                    attachments: $this->findAttachments($model),
                );
            }
        };

        $expected = new EmailHtmlMessageRendererResult(
            html: 'html',
            attachments: [
                [
                    'filename' => 'file1',
                    'contentType' => 'application/pdf',
                    'data' => 'date',
                    'size' => 4,
                ],
            ],
        );


        $this->assertEquals($expected, $renderer->render($model), 'unexpected result');
    }
}
