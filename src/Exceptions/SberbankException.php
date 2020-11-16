<?php

namespace Scaleplan\Sberbank\Exceptions;

use function Scaleplan\Translator\translate;

/**
 * Class SberbankException
 *
 * @package Scaleplan\Sberbank\Exceptions
 */
class SberbankException extends \Exception
{
    public const MESSAGE = 'sber.api-error';

    /**
     * SberbankException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     *
     * @throws \ReflectionException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ContainerTypeNotSupportingException
     * @throws \Scaleplan\DependencyInjection\Exceptions\DependencyInjectionException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ParameterMustBeInterfaceNameOrClassNameException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ReturnTypeMustImplementsInterfaceException
     */
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct(
            $message ?: translate(static::MESSAGE) ?: static::MESSAGE,
            $code,
            $previous
        );
    }
}
