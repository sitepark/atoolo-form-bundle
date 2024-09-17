<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Service\FormReader;
use Atoolo\Form\Service\FromReaderHandler;
use Atoolo\Form\Service\JsonSchemaValidator\Extended\Draft202012Extended;
use Atoolo\Resource\ResourceLocation;
use JsonSchema\Validator;
use Opis\JsonSchema\Errors\ErrorFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormReader::class)]
class FormReaderTest extends TestCase
{

    public function testDebug2(): void
    {

        $data = new \stdClass();
        //$data->animal = "doc";
        $data->upload = "data irgendwas";

        $schemaJson = <<<'JSON'
{
    "type": "object",
    "properties": {
        "animal": {
            "type": "string",
            "format" : "date"
        },
        "upload" : {
            "type" : "string",
            "format" : "data-url"
        }
    }
}
JSON;

        $validator = new \Opis\JsonSchema\Validator();
        //print_r($valudator);
        $draft = new Draft202012Extended();
        $validator->parser()->addDraft($draft);
        $validator->parser()->setDefaultDraftVersion($draft->version());
        print_r($validator->parser()->supportedDrafts());
        print_r($validator->parser()->defaultDraftVersion());
        $validator->parser()->getFormatResolver()->registerCallable('string', 'data-url', function ($data) {
            return false;
        });
        //$validator->resolver()->registerRaw(json_decode($schemaJson));
        $result = $validator->validate($data, $schemaJson);

        if (!$result->isValid()) {
            echo "NOT VALID\n";
            print_r((new ErrorFormatter())->format($result->error()));
        }
    }
    public function testDebug(): void
    {
        $araryData = ["doc", "cat"];
        $arraySchema = [
            'type' => 'array',
            "items" => [
                "oneOf" => [
                    ["const" => "doc", "title" => "Doc"],
                    ["const" => "cat", "title" => "Cat"],
                    ["const" => "mouse", "title" => "Mouse"]
                ]
            ]
        ];

        $stringData = "doc";
        $stringSchema = [
            'type' => 'string',
            "oneOf" => [
                ["const" => "doc", "title" => "Doc"],
                ["const" => "cat", "title" => "Cat"],
                ["const" => "mouse", "title" => "Mouse"]
            ]
        ];
        $validator = new Validator();
        $validator->validate($stringData, $stringSchema);

        if (!$validator->isValid()) {
            foreach ($validator->getErrors() as $error) {
                print_r($error);
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testLoad(): void
    {
        $uischema = new Layout(Type::GROUP);
        $control = new Control('#/properties/field-1');
        $schema = [
            'type' => 'object',
            'properties' => [
                'field-1' => [
                    'type' => 'string',
                ],
            ],
        ];
        $uischema->elements[] = $control;

        $formDefinition = new FormDefinition(
            $schema,
            $uischema,
            null,
            ResourceLocation::of('/test.php'),
            'form-1',
        );

        $data = [
            'field-1' => 'value',
        ];

        $handler = $this->createMock(FromReaderHandler::class);
        $handler->expects($this->once())
            ->method('startLayout')
            ->with($uischema);
        $handler->expects($this->once())
            ->method('control')
            ->with($control, ['type' => 'string'], 'field-1', 'value');
        $handler->expects($this->once())
            ->method('endLayout')
            ->with($uischema);

        $reader = new FormReader($formDefinition, $data, $handler);
        $reader->read();
    }
}
