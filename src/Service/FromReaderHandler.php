<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Layout;

interface FromReaderHandler
{
    public function startLayout(Layout $layout): void;

    public function endLayout(Layout $layout): void;

    public function control(Control $control, array $schema, string $name, mixed $value): void;
}
