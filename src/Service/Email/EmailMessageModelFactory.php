<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Service\FormDataModelFactory;
use Atoolo\Resource\ResourceChannel;
use JsonException;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EmailMessageModelFactory
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $channel,
        private readonly FormDataModelFactory $formDataModelFactory,
        private readonly ClockInterface $clock,
    ) {}

    /**
     * @param FormSubmission $submission
     * @param bool $includeEmptyFields
     * @return EmailMessageModel
     * @throws JsonException
     */
    public function create(FormSubmission $submission, bool $includeEmptyFields): array
    {
        /** @var array<string,mixed> $data */
        $data = json_decode(json_encode($submission->data, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        $items = $this->formDataModelFactory->create(
            $submission->formDefinition,
            $data,
            $includeEmptyFields,
        );
        $dateTime = $this->clock->now();
        return [
            'lang' => $submission->formDefinition->lang,
            'url' => 'https://' . $this->channel->serverName,
            'tenant' => [ 'name' => $this->channel->tenant->name ],
            'host' => $this->channel->serverName,
            'date' => $dateTime,
            'items' => $items,
        ];
    }
}
