<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Service\Email\CsvGenerator;
use Atoolo\Form\Service\Email\EmailHtmlMessageRenderer;
use Atoolo\Form\Service\Email\EmailMessageModelFactory;
use League\Csv\Exception;
use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

#[AsTaggedItem(index: 'email-sender', priority: 10)]
class EmailSender implements SubmitProcessor
{
    public function __construct(
        private readonly EmailMessageModelFactory $modelFactory,
        private readonly EmailHtmlMessageRenderer $htmlMessageRenderer,
        private readonly CsvGenerator $csvGenerator,
        private readonly MailerInterface $mailer,
    ) {}

    /**
     * @param array{
     *     from: non-empty-array<array{address: string, name: string}>,
     *     to: non-empty-array<array{address: string, name: string}>,
     *     cc?: array<array{address: string, name: string}>,
     *     bcc?: array<array{address: string, name: string}>,
     *     subject?: string,
     *     format?: 'html'|'text',
     *     showEmpty?: bool,
     *     attachCsv?: bool,
     * } $options
     * @throws Html2TextException
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function process(FormSubmission $submission, array $options): FormSubmission
    {
        $rendererModel = $this->modelFactory->create($submission, $options['showEmpty'] ?? false);
        $result = $this->htmlMessageRenderer->render($rendererModel);
        $html = $result->html;
        // TODO: implement e separate renderer for text
        $text = Html2Text::convert($html);

        $email = new Email();
        foreach ($options['from'] as $from) {
            $email->from(new Address($from['address'], $from['name']));
        }
        foreach ($options['to'] as $to) {
            $email->to(new Address($to['address'], $to['name']));
        }
        foreach ($options['cc'] ?? [] as $cc) {
            $email->cc(new Address($cc['address'], $cc['name']));
        }
        foreach ($options['bcc'] ?? [] as $bcc) {
            $email->bcc(new Address($bcc['address'], $bcc['name']));
        }

        $email->subject($options['subject'] ?? $result->subject ?? '');

        if (($options['format'] ?? 'html') === 'html') {
            $email ->html($html);
        }
        $email->text($text);

        foreach ($result->attachments as $attachment) {
            $email->attach($attachment['data'], $attachment['filename'], $attachment['contentType']);
        }

        if ($options['attachCsv'] ?? false) {
            $csvModel = $this->modelFactory->create($submission, true);
            $csv = $this->csvGenerator->generate($csvModel);
            $email->attach($csv, 'data.csv', 'text/csv');
        }

        $this->mailer->send($email);

        return $submission;
    }
}
