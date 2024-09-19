<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Service\JsonSchemaValidator\Constraint;
use Atoolo\Form\Service\JsonSchemaValidator\Extended\Draft202012Extended;
use Atoolo\Form\Service\JsonSchemaValidator\FormatConstraint;
use InvalidArgumentException;
use LogicException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Validator;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class JsonSchemaValidator
{
    private Validator $validator;

    /**
     * @param iterable $constraints array<string, SubmitProcessor>
     */
    public function __construct(#[AutowireIterator('atoolo_form.jsonSchemaConstraint')] iterable $constraints)
    {
        $this->validator = new Validator();
        $draft = new Draft202012Extended();
        $this->validator->parser()->addDraft($draft);
        $this->validator->parser()->setDefaultDraftVersion($draft->version());

        foreach ($constraints as $constraint) {
            $this->registerConstraint($constraint);
        }
    }

    private function registerConstraint(Constraint $constraint): void
    {
        if ($constraint instanceof FormatConstraint) {
            $this->registerFormatConstraint($constraint);
        } else {
            throw new InvalidArgumentException('Unknown constraint type ' . get_class($constraint));
        }
    }

    private function registerFormatConstraint(FormatConstraint $constraint): void
    {
        $formatResolver = $this->validator->parser()->getFormatResolver();
        if ($formatResolver === null) {
            throw new LogicException('No format resolver found');
        }

        $formatResolver->registerCallable($constraint->getType(), $constraint->getName(), function ($data, $schema) use ($constraint) {
            return $constraint->check($data, $schema);
        });
    }


    public function validate(array $schema, stdClass $data): void
    {
        $schemaJson = $this->arrayToObjectRecursive($schema);

        $result = $this->validator->validate($data, $schemaJson);

        if (!$result->isValid()) {
            throw $this->errorsToValidationFailedException($data, $result->error());
        }
    }

    private function errorsToValidationFailedException(stdClass $data, ValidationError $validationError): ValidationFailedException
    {
        $list = new ConstraintViolationList();
        $formatter = new ErrorFormatter();
        $errors = $formatter->format($validationError, false, function (ValidationError $error) use ($formatter) {
            $schema = $error->schema()->info();

            $path = $schema->path();
            $args = $error->args();
            if (isset($args['missing'][0])) {
                $path[] = $args['missing'][0];
            }

            return [
                'message' => $formatter->formatErrorMessage($error),
                'path' => implode('/', $path),
                'args' => $error->args(),
                'constraint' => $error->keyword(),
            ];
        });
        foreach ($errors as $error) {
            $v = new ConstraintViolation(

                $error['message'],
                null,
                $error['args'],
                null,
                $error['path'],
                '',
                null,
                null,
                $error['constraint'] === 'require' ? new \Symfony\Component\Validator\Constraints\NotBlank() : null,
            );
            $list->add($v);
        }
        throw new ValidationFailedException($data, $list);
    }

    /**
     * @throws \JsonException
     */
    private function arrayToObjectRecursive(array $array): object
    {
        $json = json_encode($array, JSON_THROW_ON_ERROR);
        return (object) json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }
}
