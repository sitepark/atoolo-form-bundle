<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Service\FormDataModelFactory;
use Atoolo\Resource\ResourceChannel;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EmailMessageModelFactory
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $channel,
        private readonly FormDataModelFactory $formDataModelFactory,
    ) {}

    public function create(FormSubmission $submission, bool $includeEmptyFields): array
    {
        $items = $this->formDataModelFactory->create(
            $submission->formDefinition,
            $this->arrayCastRecursive((array) $submission->data),
            $includeEmptyFields,
        );
        return [
            'url' => 'https://' . $this->channel->serverName,
            'locale' => 'de',
            'tenant' => [ 'name' => $this->channel->tenant->name ],
            'host' => $this->channel->serverName,
            'date' => date('d.m.Y'),
            'time' => date('H:i:s'),
            'items' => $items,
        ];
    }

    private function arrayCastRecursive(mixed $array): array
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->arrayCastRecursive($value);
                }
                if ($value instanceof stdClass) {
                    $array[$key] = $this->arrayCastRecursive((array) $value);
                }
            }
        }
        if ($array instanceof stdClass) {
            return $this->arrayCastRecursive((array) $array);
        }
        return $array;
    }
}
