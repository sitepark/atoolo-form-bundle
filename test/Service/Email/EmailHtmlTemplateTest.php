<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\DomCrawler\Crawler;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Translator;

class EmailHtmlTemplateTest extends TestCase
{
    public function testTemplate(): void
    {

        $translator = new Translator('en');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addResource('array', [
            'email.header' => 'Email header',
            'email.headline' => 'Email headline',
            'email.footer' => 'Email footer',
        ], 'en', 'form');

        $projectDir = __DIR__ . '/../../../';
        $loader = new FilesystemLoader();
        $loader->addPath($projectDir . '/templates', 'AtooloForm');
        $twig = new Environment($loader, [
            'strict_variables' => true,
        ]);
        $twig->addExtension(new TranslationExtension($translator));

        $context = [
            'lang' => 'en',
            'tenant' => [
                'name' => 'Test Tenant',
            ],
            'url' => 'https://test.example.com',
            'host' => 'test.example.com',
            'date' => '25.09.2024',
            'time' => '12:09',
            'items' => [
                ['type' => 'text', 'label' => 'Text', 'value' => 'Test content'],
            ],
        ];


        $html = $twig->render('@AtooloForm/email.html.twig', $context);

        $crawler = new Crawler($html);
        $this->assertEquals(
            'Email header',
            $crawler->filter('.title')->first()->text(),
            'Email header does not match',
        );
        $this->assertEquals(
            'Email headline',
            $crawler->filter('h1.header')->first()->text(),
            'Email header does not match',
        );
        $this->assertEquals(
            'Email footer',
            $crawler->filter('.footer')->first()->text(),
            'Email header does not match',
        );
        $this->assertEquals(
            'Text',
            $crawler->filter('.field .field-label')->first()->text(),
            'Field text does not match',
        );
        $this->assertEquals(
            'Test content',
            $crawler->filter('.field .field-value')->first()->text(),
            'Field label does not match',
        );
    }
}
