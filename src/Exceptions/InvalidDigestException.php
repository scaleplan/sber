<?php

namespace Scaleplan\Sberbank\Exceptions;

/**
 * Class InvalidDigestException
 *
 * @package Scaleplan\Sberbank\Exceptions
 */
class InvalidDigestException extends SberbankException
{
    public const MESSAGE = 'Неверный дайджест ответа от Сбербанка.';
}
