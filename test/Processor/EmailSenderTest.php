<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Processor;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Processor\EmailSender;
use Atoolo\Form\Service\Email\CsvGenerator;
use Atoolo\Form\Service\Email\EmailHtmlMessageRenderer;
use Atoolo\Form\Service\Email\EmailMessageModelFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[CoversClass(EmailSender::class)]
class EmailSenderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSend(): void
    {
        $modelFactory = $this->createStub(EmailMessageModelFactory::class);
        $result = new EmailHtmlMessageRendererResult(
            subject: 'test',
            html: '<p>test</p>',
            attachments: [(object) [
                'filename' => 'text.txt',
                'contentType' => 'text/plain',
                'data' => 'text',
            ]],
        );
        $htmlMessageRenderer = $this->createStub(EmailHtmlMessageRenderer::class);
        $htmlMessageRenderer->method('render')
            ->willReturn($result);
        $csvGenerator = $this->createStub(CsvGenerator::class);
        $csvGenerator->method('generate')
            ->willReturn('csv');
        $mailer = $this->createMock(MailerInterface::class);

        $emailSender = new EmailSender(
            $modelFactory,
            $htmlMessageRenderer,
            $csvGenerator,
            $mailer,
        );

        $submission = new FormSubmission(
            '12.34.56.78',
            $this->createStub(FormDefinition::class),
            new stdClass(),
        );

        $expected = (new Email())
            ->subject('test')
            ->html('<p>test</p>')
            ->text('test')
            ->from(new Address('from@example.com', 'From'))
            ->to(new Address('to@example.com', 'To'))
            ->cc(new Address('cc@example.com', 'Cc'))
            ->bcc(new Address('bcc@example.com', 'Bcc'))
            ->attach('text', 'text.txt', 'text/plain')
            ->attach('csv', 'data.csv', 'text/csv')
        ;

        $mailer->expects($this->once())
            ->method('send')
            ->with($expected);

        $emailSender->process($submission, [
            'attachCsv' => true,
            'from' => [[
                'address' => 'from@example.com',
                'name' => 'From',
            ]],
            'to' => [[
                'address' => 'to@example.com',
                'name' => 'To',
            ]],
            'cc' => [[
                'address' => 'cc@example.com',
                'name' => 'Cc',
            ]],
            'bcc' => [[
                'address' => 'bcc@example.com',
                'name' => 'Bcc',
            ]],
        ]);
    }
}
