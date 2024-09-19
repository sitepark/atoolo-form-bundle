<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Service\FormDefinitionLoader;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormDefinitionLoader::class)]
class FormDefinitionLoaderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testLoad(): void
    {
        $resourceLoader = $this->createStub(ResourceLoader::class);

        $resourceLoader->method('load')->willReturn(new Resource(
            '/test',
            '',
            '',
            '',
            ResourceLanguage::default(),
            new DataBag(['content' => [
                "type" => "ROOT",
                "id" => "ROOT",
                "items" => [[
                    "type" => "main",
                    "id" => "main",
                    "items" => [[
                        "type" => "implicitSection",
                        "model" => [
                            "implicit" => true
                        ],
                        "id" => "implicitSection-1",
                        "items" => [[
                            "type" => "formContainer",
                            "id" => "formEditor-1",
                            "model" => [
                                "headline" => "Form heading",
                                "https" => true,
                                "consent" => false,
                                "items" => [[
                                    "id" => "fieldset-1",
                                    "type" => "fieldset",
                                    "legend" => "Field group Legend",
                                    "hideLegend" => false,
                                    "items" => [[
                                        "id" => "field-1",
                                        "name" => "field-1",
                                        "label" => "Field labeling",
                                        "autocomplete" => "off",
                                        "description" => null,
                                        "type" => "field.text",
                                        "required" => false,
                                        "spaceAfter" => false
                                    ]]
                                ]],
                                "bottomBar" => [
                                    "items" => [[
                                        "type" => "button.submit",
                                        "id" => "button-1",
                                        "label" => "submit",
                                        "value" => "submit",
                                        "action" => "submit"
                                    ]]
                                ],
                                "deliverer" => [
                                    "modelType" => "content.form.deliverer.email",
                                    "from" => [
                                        "test@email.com" => "Sender"
                                    ],
                                    "to" => [
                                        "test@email.com" => "asdf"
                                    ],
                                    "subject" => "Subject of E-Mail",
                                    "type" => "email",
                                    "format" => "plain",
                                    "attachCsv" => false,
                                    "showEmpty" => false
                                ],
                                "messages" => [
                                    "success" => [
                                        "headline" => "Confirmation",
                                        "text" => "Text"
                                    ]
                                ]
                            ]
                        ]]
                    ]]
                ]]
            ]])
        ));

        $loader = new FormDefinitionLoader($resourceLoader);

        $definition = $loader->loadFromResource(ResourceLocation::of('/test.php'), 'formEditor-1');
        print_r($definition);
    }
}
