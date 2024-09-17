<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Service\Email\CsvGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Throwable;

#[CoversClass(CsvGenerator::class)]
class CsvGeneratorTest extends TestCase
{
    public function testGenerate(): void
    {
        $csvGenerator = new CsvGenerator();
        $model = [];

        $csv = $csvGenerator->generate($model);
        echo $csv;
    }
}
