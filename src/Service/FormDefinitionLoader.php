<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Exception\FormNotFoundException;
use Atoolo\Form\Exception\InvalidFormConfiguration;
use Atoolo\Form\Processor\EmailSender\Options;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Address;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class FormDefinitionLoader
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_loader')] private readonly ResourceLoader $resourceLoader,
        private readonly LabelTranslator $translator,
    ) {}

    public function load(ResourceLocation $location, string $component): FormDefinition
    {
        $resource = $this->resourceLoader->load($location);

        $componentConfig = $this->findFormEditorComponent($resource->data->getArray('content.items'), $component);

        if (empty($componentConfig)) {
            throw new FormNotFoundException('Component \'' . $component . '\' not found');
        }

        if (!isset($componentConfig['model']['jsonForms'])) {
            throw new InvalidFormConfiguration('Missing jsonForms definition in component \'' . $component . '\'');
        }

        $jsonForms = $componentConfig['model']['jsonForms'];

        if (!isset($jsonForms['schema'])) {
            throw new InvalidFormConfiguration('Missing jsonForms.schema definition in component \'' . $component . '\'');
        }
        if (!isset($jsonForms['uischema'])) {
            throw new InvalidFormConfiguration('Missing jsonForms.uischema definition in component \'' . $component . '\'');
        }

        $buttons = [];
        foreach ($componentConfig['model']['bottomBar']['items'] ?? [] as $button) {
            $buttons[$button['value']] = $button['label'];
        }

        $messages = $componentConfig['model']['messages'] ?? [];

        if (!isset($componentConfig['model']['deliverer']['modelType'])) {
            throw new InvalidFormConfiguration('Missing deliverer definition in component \'' . $component . '\'');
        }

        if ($componentConfig['model']['deliverer']['modelType'] !== 'content.form.deliverer.email') {
            throw new InvalidFormConfiguration('Unsupported deliverer \'' . $componentConfig['model']['deliverer']['modelType'] . '\' in component \'' . $component . '\'');
        }

        $deliverer = $componentConfig['model']['deliverer'];
        $processor = [
            'email-sender' => $this->transformProcessorConfig($deliverer),
        ];

        $schema = $this->translator->translate($jsonForms['schema'], ['label', 'title']);
        $uiSchema = $this->translator->translate($jsonForms['uischema'], ['label']);

        return new FormDefinition(
            schema: $schema,
            uischema: $this->deserializeUiSchema($uiSchema),
            data: null,
            buttons: $buttons,
            messages: $messages,
            component: $component,
            processors: $processor,
        );
    }

    private function transformProcessorConfig(array $deliverer): Options
    {
        $from = [];
        foreach ($deliverer['from'] ?? [] as $address => $name) {
            $from[] = new Address($address, $name);
        }

        $to = [];
        foreach ($deliverer['to'] ?? [] as $address => $name) {
            $to[] =new Address($address, $name);
        }

        return new Options(
            $from,
            $to,
            $deliverer['subject'] ?? '',
            $deliverer['format'] ?? 'html',
            $deliverer['attachCsv'] ?? false,
            $deliverer['showEmpty'] ?? false,
        );
    }

    private function deserializeUiSchema(array $data): Layout
    {
        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $discriminator = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);

        $encoders = [new JsonEncoder()];
        $normalizers = [ new ArrayDenormalizer(), new BackedEnumNormalizer(), new ObjectNormalizer(
            classMetadataFactory: $classMetadataFactory,
            propertyTypeExtractor: new PhpDocExtractor(),
            classDiscriminatorResolver: $discriminator,
        )];
        return (new Serializer($normalizers, $encoders))->denormalize($data, Layout::class);
    }

    private function translateUiSchemaLabel(Layout $layout): Layout {

    }

    private function findFormEditorComponent(array $items, string $component): array
    {
        foreach ($items as $item) {
            if ($item['type'] === 'formContainer' && $item['id'] === $component) {
                return $item;
            }
            if ($item['items']) {
                $result = $this->findFormEditorComponent($item['items'], $component);
                if ($result) {
                    return $result;
                }
            }
        }
        return [];
    }
}
