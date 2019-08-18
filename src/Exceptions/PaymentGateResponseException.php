<?php

namespace Scaleplan\Sberbank\Exceptions;

/**
 * Class InvalidDigestException
 *
 * @package App\Services\Exception
 */
class PaymentGateResponseException extends SberbankException
{
    public const MESSAGE_FIRST = 'Payment gate response error.';
}
