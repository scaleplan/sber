<?php

namespace Scaleplan\Sberbank\Exceptions;

/**
 * Class SberbankException
 *
 * @package Scaleplan\Sberbank\Exceptions
 */
class SberbankException extends \Exception
{
    public const MESSAGE = 'Ошибка запроса к Sberbank API.';

    /**
     * SberbankException constructor.
     *
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message ?: static::MESSAGE, $code, $previous);
    }
}
