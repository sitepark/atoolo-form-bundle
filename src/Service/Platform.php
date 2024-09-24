<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use DateTime;
use stdClass;

class Platform
{
    public function datetime(): DateTime
    {
        return new DateTime();
    }

    public function objectToArrayRecursive(mixed $array): array
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = $this->objectToArrayRecursive($value);
                }
                if ($value instanceof stdClass) {
                    $array[$key] = $this->objectToArrayRecursive((array) $value);
                }
            }
        }
        if ($array instanceof stdClass) {
            return $this->objectToArrayRecursive((array) $array);
        }
        return $array;
    }

    /**
     * @throws \JsonException
     */
    public function arrayToObjectRecursive(array $array): object
    {
        $json = json_encode($array, JSON_THROW_ON_ERROR);
        return (object) json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }

}
