<?php

namespace Scaleplan\Sberbank\Exceptions;

/**
 * Class PaymentReverseException
 *
 * @package Scaleplan\Sberbank\Exceptions
 */
class PaymentReverseException extends SberbankException
{
    public const MESSAGE = 'sber.payment-cancel-error';
}
