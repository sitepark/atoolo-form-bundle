<?php

declare(strict_types=1);

namespace Atoolo\Form\Processor;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Processor\EmailSender\Options;
use Atoolo\Form\Service\Email\CsvGenerator;
use Atoolo\Form\Service\Email\EmailHtmlMessageRenderer;
use Atoolo\Form\Service\Email\EmailMessageModelFactory;
use Soundasleep\Html2Text;
use Soundasleep\Html2TextException;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * @implements SubmitProcessor<Options>
 */
#[AsTaggedItem(index: 'email-sender')]
class EmailSender implements SubmitProcessor
{
    public function __construct(
        private readonly EmailMessageModelFactory $modelFactory,
        private readonly EmailHtmlMessageRenderer $htmlMessageRenderer,
        private readonly CsvGenerator $csvGenerator,
        private readonly MailerInterface $mailer,
    ) {}

    /**
     * @throws Html2TextException
     * @throws TransportExceptionInterface
     */
    public function process(FormSubmission $submission, SubmitProcessorOptions $options): FormSubmission
    {
        $rendererModel = $this->modelFactory->create($submission, $options->showEmpty);
        $result = $this->htmlMessageRenderer->render($rendererModel);
        $html = $result->html;
        $text = Html2Text::convert($html);

        $email = new Email();
        foreach ($options->from as $from) {
            $email->from($from);
        }
        foreach ($options->to as $to) {
            $email->to($to);
        }
        $email->subject($options->subject);

        if ($options->format === 'html') {
            $email ->html($html);
        }
        $email->text($text);

        foreach ($result->attachments as $attachment) {
            $email->attach($attachment->data, $attachment->filename, $attachment->contentType);
        }

        if ($options->attachCsv) {
            $csvModel = $this->modelFactory->create($submission, true);
            $csv = $this->csvGenerator->generate($csvModel);
            $email->attach($csv, 'data.csv', 'text/csv');
        }

        $this->mailer->send($email);

        return $submission;
    }

}
