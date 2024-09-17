<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Element;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Service\DataUrlParser;
use Atoolo\Form\Service\FormDataModelFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(FormDataModelFactory::class)]
class FormDataModelFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $dataUrlParser = $this->createStub(DataUrlParser::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $formDataModelFactory = new FormDataModelFactory($dataUrlParser, $translator);

        $formDefinition = new FormDefinition(
            schema : $this->getSchema(),
            uischema: $this->getUiSchema(),
            data: [],
            buttons: null,
            messages: null,
            component: '',
            processors: null,
        );

        $data =  [
            "field-11" => true,
            "field-10" => true,
            "field-1" => "df",
            "field-12" => [
            ],
            "field-13" => "8cd05a5b-cbfe-4f49-bcf5-f38a5409f7aa",
            "field-14" => "0a2e796f-3db9-449b-8861-6e15c11778a0",
        ];

        $model = $formDataModelFactory->create($formDefinition, $data, true);

        print_r($model);
    }

    private function getSchema(): array
    {
        return [
            "type" => "object",
            "properties" => [
                "field-1" => [
                    "type" => "string",
                    "title" => "Single-line text field",
                ],
                "field-2" => [
                    "type" => "string",
                    "title" => "File upload",
                    "acceptedFileNames" => [
                        "*.png",
                        "*.jpg",
                    ],
                    "maxFileSize" => 2000000,
                    "acceptedContentTypes" => ["image/*"],
                    "format" => "data-url",
                ],
                "field-4" => [
                    "type" => "string",
                    "title" => "email",
                    "format" => "email",
                ],
                "field-5" => [
                    "type" => "string",
                    "title" => "Phone number",
                    "format" => "phone",
                ],
                "field-6" => [
                    "type" => "integer",
                    "title" => "Figures",
                ],
                "field-7" => [
                    "type" => "string",
                    "title" => "Date",
                    "format" => "date",
                ],
                "field-8" => [
                    "type" => "string",
                    "title" => "Multiline text field",
                ],
                "field-9" => [
                    "type" => "string",
                    "title" => "Rich text input field",
                    "format" => "html",
                    "allowedElements" => [
                        "p",
                        "strong",
                        "em",
                        "li",
                        "ul",
                        "ol",
                    ],
                ],
                "field-10" => [
                    "type" => "boolean",
                    "title" => "Checkbox",
                ],
                "field-11" => [
                    "type" => "boolean",
                ],
                "field-12" => [
                    "type" => "array",
                    "title" => "Checkbox group",
                    "items" => [
                        "oneOf" => [
                            [
                                "const" => "ad5bf874-90b4-4e97-8aaa-2dde2622e918",
                                "title" => "Dog",
                            ],
                            [
                                "const" => "28ae045f-557b-48d9-9d68-eb62f49c34d7",
                                "title" => "Cat",
                            ],
                            [
                                "const" => "598b8184-80e7-43f2-b6cd-982ca22f6cf0",
                                "title" => "Mouse",
                            ],
                        ],
                    ],
                    "uniqueItems" => true,
                ],
                "field-13" => [
                    "type" => "string",
                    "title" => "Radio buttons",
                    "oneOf" => [
                        [
                            "const" => "e78099f0-1b5f-40e3-8fa1-eb71247afe11",
                            "title" => "Dog",
                        ],
                        [
                            "const" => "8cd05a5b-cbfe-4f49-bcf5-f38a5409f7aa",
                            "title" => "Cat",
                        ],
                        [
                            "const" => "6b58b515-6626-4b5a-b1e1-4965a9d38e80",
                            "title" => "Mouse",
                        ],
                    ],
                ],
                "field-14" => [
                    "type" => "string",
                    "title" => "Selection list",
                    "oneOf" => [
                        [
                            "const" => "7384956d-a19d-4967-88e2-3d3c900d5214",
                            "title" => "Dog",
                        ],
                        [
                            "const" => "0a2e796f-3db9-449b-8861-6e15c11778a0",
                            "title" => "Cat",
                        ],
                        [
                            "const" => "843024f7-2f5a-48a0-9f88-2a9c4aa30d18",
                            "title" => "Mouse",
                        ],
                    ],
                ],
                "contact" => [
                    "type" => "object",
                    "properties" => [
                        "salutation" => [
                            "type" => "string",
                            "enum" => [
                                "salutationFemale",
                                "salutationMale",
                                "salutationDiverse",
                                "salutationNotSpecified",
                            ],
                        ],
                        "firstname" => [
                            "type" => "string",
                        ],
                        "lastname" => [
                            "type" => "string",
                        ],
                        "street" => [
                            "type" => "string",
                        ],
                        "housenumber" => [
                            "type" => "string",
                        ],
                        "postalcode" => [
                            "type" => "string",
                        ],
                        "city" => [
                            "type" => "string",
                        ],
                        "phone" => [
                            "type" => "string",
                            "format" => "phone",
                        ],
                        "mobile" => [
                            "type" => "string",
                            "format" => "phone",
                        ],
                        "email" => [
                            "type" => "string",
                            "format" => "email",
                        ],
                    ],
                ],
                "webAccountContact" => [
                    "type" => "object",
                    "properties" => [
                        "firstname" => [
                            "type" => "string",
                        ],
                        "lastname" => [
                            "type" => "string",
                        ],
                        "street" => [
                            "type" => "string",
                        ],
                        "housenumber" => [
                            "type" => "string",
                        ],
                        "postalcode" => [
                            "type" => "string",
                        ],
                        "city" => [
                            "type" => "string",
                        ],
                        "phone" => [
                            "type" => "string",
                            "format" => "phone",
                        ],
                        "mobile" => [
                            "type" => "string",
                            "format" => "phone",
                        ],
                        "email" => [
                            "type" => "string",
                            "format" => "email",
                        ],
                    ],
                ],
                "field-17" => [
                    "type" => "string",
                    "title" => "Field in other group",
                ],
            ],
            "required" => ["field-1"],
            "errorMessage" => [
                "required" => [
                    "field-1" => "The single-line text field must be specified",
                ],
                "acceptedFileNames" => [
                    "field-2" => "This file is not kind",
                ],
                "maxFileSize" => [
                    "field-2" => "This file is not kind",
                ],
                "acceptedContentTypes" => [
                    "field-2" => "This file is not kind",
                ],
            ],
        ];
    }

    public function getUiSchema(): Layout
    {
        $uischema = [
            "type" => "VerticalLayout",
            "elements" => [
                [
                    "label" => "Field group Legend",
                    "type" => "Group",
                    "options" => [
                        "hideLegend" => true,
                    ],
                    "elements" => [
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-1",
                            "label" => "Single-line text field",
                            "options" => [
                                "autocomplete" => "name",
                                "spaceAfter" => true,
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-2",
                            "label" => "File upload",
                            "options" => [
                                "spaceAfter" => true,
                            ],
                        ],
                        [
                            "type" => "Annotation",
                            "htmlLabel" => [
                                "entities" => [[
                                    "start" => 36,
                                    "link" => [
                                        "resourceUrl" => "/rubrik/studiengang.php",
                                        "modelType" => "content.link.link",
                                        "label" => "Study program",
                                        "url" => "/rubrik/studiengang.php",
                                    ],
                                    "end" => 78,
                                    "modelType" => "html.richText.internalLink",
                                    "inner" => [
                                        "normalized" => true,
                                        "modelType" => "html.richText",
                                        "text" => "link",
                                    ],
                                ]],
                                "normalized" => true,
                                "modelType" => "html.richText",
                                "text" => "Note with <strong>html</strong> and <a href=\"/rubrik/studiengang.php\">link</a>.",
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-4",
                            "label" => "email",
                            "options" => [
                                "autocomplete" => "email",
                                "asReplyTo" => true,
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-5",
                            "label" => "Phone number",
                            "options" => [
                                "autocomplete" => "tel",
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-6",
                            "label" => "Figures",
                            "options" => [
                                "autocomplete" => "off",
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-7",
                            "label" => "Date",
                            "options" => [
                                "autocomplete" => "name",
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-8",
                            "label" => "Multiline text field",
                            "options" => [
                                "autocomplete" => "off",
                                "multi" => true,
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-9",
                            "label" => "Rich text input field",
                            "options" => [
                                "multi" => true,
                                "html" => true,
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-10",
                            "label" => "Checkbox",
                            "options" => [
                                "label" => "Checkbox",
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-11",
                            "options" => [],
                            "htmlLabel" => [
                                "entities" => [[
                                    "start" => 36,
                                    "link" => [
                                        "resourceUrl" => "/rubrik/studiengang.php",
                                        "modelType" => "content.link.link",
                                        "label" => "Study program",
                                        "url" => "/rubrik/studiengang.php",
                                    ],
                                    "end" => 78,
                                    "modelType" => "html.richText.internalLink",
                                    "inner" => [
                                        "normalized" => true,
                                        "modelType" => "html.richText",
                                        "text" => "link",
                                    ],
                                ]],
                                "normalized" => true,
                                "modelType" => "html.richText",
                                "text" => "An <strong>html</strong> label with <a href=\"/rubrik/studiengang.php\">link</a>.",
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-12",
                            "label" => "Checkbox group",
                            "options" => [],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-13",
                            "label" => "Radio buttons",
                            "options" => [
                                "format" => "radio",
                            ],
                        ],
                        [
                            "type" => "Control",
                            "scope" => "#/properties/field-14",
                            "label" => "Selection list",
                            "options" => [],
                        ],
                        [
                            "type" => "Group",
                            "options" => [],
                            "elements" => [
                                [
                                    "type" => "Control",
                                    "label" => "\${field.salutation.label}",
                                    "scope" => "#/properties/contact/properties/salutation",
                                    "options" => [
                                        "format" => "radio",
                                    ],
                                ],
                                [
                                    "type" => "HorizontalLayout",
                                    "elements" => [
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.firstname.label}",
                                            "scope" => "#/properties/contact/properties/firstname",
                                            "options" => [
                                                "autocomplete" => "given-name",
                                            ],
                                        ],
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.lastname.label}",
                                            "scope" => "#/properties/contact/properties/lastname",
                                            "options" => [
                                                "autocomplete" => "family-name",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "type" => "HorizontalLayout",
                                    "elements" => [
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.street.label}",
                                            "scope" => "#/properties/contact/properties/street",
                                            "options" => [
                                                "autocomplete" => "address-line1",
                                            ],
                                        ],
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.housenumber.label}",
                                            "scope" => "#/properties/contact/properties/housenumber",
                                            "options" => [
                                                "autocomplete" => "on",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "type" => "HorizontalLayout",
                                    "elements" => [
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.postalCode.label}",
                                            "scope" => "#/properties/contact/properties/postalcode",
                                            "options" => [
                                                "autocomplete" => "postal-code",
                                            ],
                                        ],
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.city.label}",
                                            "scope" => "#/properties/contact/properties/city",
                                            "options" => [
                                                "autocomplete" => "address-level2",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "type" => "Control",
                                    "label" => "\${field.tel.label}",
                                    "scope" => "#/properties/contact/properties/phone",
                                    "options" => [
                                        "autocomplete" => "tel-national",
                                    ],
                                ],
                                [
                                    "type" => "Control",
                                    "label" => "\${field.mobile.label}",
                                    "scope" => "#/properties/contact/properties/mobile",
                                    "options" => [
                                        "autocomplete" => "on",
                                    ],
                                ],
                                [
                                    "type" => "Control",
                                    "label" => "\${field.email.label}",
                                    "scope" => "#/properties/contact/properties/email",
                                    "options" => [
                                        "autocomplete" => "email",
                                    ],
                                ],
                            ],
                        ],
                        [
                            "type" => "Group",
                            "options" => [],
                            "elements" => [
                                [
                                    "type" => "HorizontalLayout",
                                    "elements" => [
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.webAccount.firstname.label}",
                                            "scope" => "#/properties/webAccountContact/properties/firstname",
                                            "options" => [
                                                "autocomplete" => "given-name",
                                            ],
                                        ],
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.webAccount.lastname.label}",
                                            "scope" => "#/properties/webAccountContact/properties/lastname",
                                            "options" => [
                                                "autocomplete" => "family-name",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "type" => "HorizontalLayout",
                                    "elements" => [
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.webAccount.street.label}",
                                            "scope" => "#/properties/webAccountContact/properties/street",
                                            "options" => [
                                                "autocomplete" => "address-line1",
                                            ],
                                        ],
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.webAccount.housenumber.label}",
                                            "scope" => "#/properties/webAccountContact/properties/housenumber",
                                            "options" => [
                                                "autocomplete" => "on",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "type" => "HorizontalLayout",
                                    "elements" => [
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.webAccount.postalCode.label}",
                                            "scope" => "#/properties/webAccountContact/properties/postalcode",
                                            "options" => [
                                                "autocomplete" => "postal-code",
                                            ],
                                        ],
                                        [
                                            "type" => "Control",
                                            "label" => "\${field.webAccount.city.label}",
                                            "scope" => "#/properties/webAccountContact/properties/city",
                                            "options" => [
                                                "autocomplete" => "address-level2",
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    "type" => "Control",
                                    "label" => "\${field.webAccount.tel.label}",
                                    "scope" => "#/properties/webAccountContact/properties/phone",
                                    "options" => [
                                        "autocomplete" => "tel-national",
                                    ],
                                ],
                                [
                                    "type" => "Control",
                                    "label" => "\${field.webAccount.mobile.label}",
                                    "scope" => "#/properties/webAccountContact/properties/mobile",
                                    "options" => [
                                        "autocomplete" => "on",
                                    ],
                                ],
                                [
                                    "type" => "Control",
                                    "label" => "\${field.webAccount.email.label}",
                                    "scope" => "#/properties/webAccountContact/properties/email",
                                    "options" => [
                                        "autocomplete" => "email",
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    "label" => "Second group",
                    "type" => "Group",
                    "options" => [
                        "hideLegend" => false,
                    ],
                    "elements" => [[
                        "type" => "Control",
                        "scope" => "#/properties/field-17",
                        "label" => "Field in other group",
                        "options" => [
                            "autocomplete" => "name",
                        ],
                    ]],
                ],
                [
                    "type" => "Annotation",
                    "htmlLabel.text" => "Further information",
                ],
            ],
        ];

        return $this->deserializeUiSchema($uischema);
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

}
