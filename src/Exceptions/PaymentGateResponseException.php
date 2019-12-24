<?php

namespace Scaleplan\Sberbank\Exceptions;

/**
 * Class InvalidDigestException
 */
class PaymentGateResponseException extends SberbankException
{
    public const MESSAGE_FIRST = 'Payment gate response error.';
}
