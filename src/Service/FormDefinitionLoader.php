<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Exception\FormNotFoundException;
use Atoolo\Form\Exception\InvalidFormConfiguration;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        #[Autowire(service: 'atoolo_resource.resource_loader')]
        private readonly ResourceLoader $resourceLoader,
        private readonly LabelTranslator $translator,
    ) {}

    public function loadFromResource(ResourceLocation $location, string $component): FormDefinition
    {
        $resource = $this->resourceLoader->load($location);

        $componentConfig = $this->findFormEditorComponent($resource->data->getArray('content.items'), $component);

        if (empty($componentConfig)) {
            throw new FormNotFoundException('Component \'' . $component . '\' not found');
        }

        return $this->loadFromModel($component, $componentConfig['model'] ?? []);
    }

    public function loadFromModel(string $component, array $model): FormDefinition
    {
        if (!isset($model['jsonForms'])) {
            throw new InvalidFormConfiguration('Missing jsonForms definition in component \'' . $component . '\'');
        }

        $jsonForms = $model['jsonForms'];

        if (!isset($jsonForms['schema'])) {
            throw new InvalidFormConfiguration('Missing jsonForms.schema definition in component \'' . $component . '\'');
        }
        if (!isset($jsonForms['uischema'])) {
            throw new InvalidFormConfiguration('Missing jsonForms.uischema definition in component \'' . $component . '\'');
        }

        $buttons = [];
        foreach ($model['bottomBar']['items'] ?? [] as $button) {
            $buttons[$button['value']] = $button['label'];
        }

        $messages = $model['messages'] ?? [];

        if (!isset($model['deliverer']['modelType'])) {
            throw new InvalidFormConfiguration('Missing deliverer definition in component \'' . $component . '\'');
        }

        if ($model['deliverer']['modelType'] !== 'content.form.deliverer.email') {
            throw new InvalidFormConfiguration('Unsupported deliverer \'' . $model['deliverer']['modelType'] . '\' in component \'' . $component . '\'');
        }

        $deliverer = $model['deliverer'];
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

    private function transformProcessorConfig(array $deliverer): array
    {
        $from = [];
        foreach ($deliverer['from'] ?? [] as $address => $name) {
            $from[] = ['address' => $address, 'name' => $name];
        }

        $to = [];
        foreach ($deliverer['to'] ?? [] as $address => $name) {
            $to[] = ['address' => $address, 'name' => $name];
        }

        $cc = [];
        foreach ($deliverer['cc'] ?? [] as $address => $name) {
            $cc[] = ['address' => $address, 'name' => $name];
        }

        $bcc = [];
        foreach ($deliverer['bcc'] ?? [] as $address => $name) {
            $bcc[] = ['address' => $address, 'name' => $name];
        }

        return [
            'from' => $from,
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'subject' => $deliverer['subject'] ?? '',
            'format' => $deliverer['format'] ?? 'html',
            'attachCsv' => $deliverer['attachCsv'] ?? false,
            'showEmpty' => $deliverer['showEmpty'] ?? false,
        ];
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
