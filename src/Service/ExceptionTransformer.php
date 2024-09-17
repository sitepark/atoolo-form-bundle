<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Phpro\ApiProblem\ApiProblemInterface;
use Phpro\ApiProblem\Http\ValidationApiProblem;
use Phpro\ApiProblemBundle\Transformer\ExceptionTransformerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

#[AutoconfigureTag('phpro.api_problem.exception_transformer')]
class ExceptionTransformer implements ExceptionTransformerInterface
{
    public function transform(Throwable $exception): ApiProblemInterface
    {
        if ($exception instanceof ValidationFailedException) {
            return new ValidationApiProblem($exception->getViolations());
        }

        throw new \LogicException('Unaccepted exception ' . get_class($exception));
    }

    public function accepts(Throwable $exception): bool
    {
        return $exception instanceof ValidationFailedException;
    }
}
