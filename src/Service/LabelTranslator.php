<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

class LabelTranslator
{

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {}

    public function translate(array &$data, array $fields): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->translate($data[$key], $fields);
            } elseif (in_array($key, $fields, true)) {
                $data[$key] = $this->translateLabel($value);
            }
        }
        return $data;
    }

    public function translateLabel(?string $label): ?string
    {
        if ($label == null) {
            return null;
        }

        preg_match('/\$\{([^}]+)}/', $label, $matches);
        if ($matches) {
            $key = $matches[1] ?? '';
            $translated = $this->translator->trans($key, domain: 'form');
            return $translated === '' ? $label : $translated;
        }

        return $label;
    }
}
