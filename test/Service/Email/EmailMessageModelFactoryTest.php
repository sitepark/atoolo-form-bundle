<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Service\Email\EmailMessageModelFactory;
use Atoolo\Form\Service\FormDataModelFactory;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Clock\MockClock;

#[CoversClass(EmailMessageModelFactory::class)]
class EmailMessageModelFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreate(): void
    {
        $channel = ResourceChannel::create([
            'serverName' => 'test.example.com',
            'tenant' => [
                'name' => 'Test Tenant',
            ],
        ]);

        $formDataModelFactory = $this->createStub(FormDataModelFactory::class);
        $formDataModelFactory->method('create')
            ->willReturn([
                'dummy' => true,
            ]);
        $dateTime = new DateTimeImmutable('2024-09-23 09:38:20');
        $factory = new EmailMessageModelFactory($channel, $formDataModelFactory, new MockClock($dateTime));

        $formDefinition = new FormDefinition(
            schema: [],
            uischema: new Layout(Type::VERTICAL_LAYOUT),
            data: [],
            buttons: [],
            messages: [],
            lang: 'en',
            component: 'test',
            processors: [],
        );

        $data = new stdClass();
        $submission = new FormSubmission('127.0.0.1', $formDefinition, $data);

        $expected = [
            'lang' => 'en',
            'url' => 'https://test.example.com',
            'tenant' => ['name' => 'Test Tenant'],
            'host' => 'test.example.com',
            'date' => $dateTime,
            'items' => ['dummy' => true],
        ];

        $this->assertEquals($expected, $factory->create($submission, true), 'unexpected model');
    }
}
