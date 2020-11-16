<?php

namespace Scaleplan\Sberbank\DTO;

/**
 * Class SberbankResponseDTO
 *
 * @package Scaleplan\Sberbank\DTO
 */
class SberbankResponseDTO
{
    /**
     * @var bool
     */
    private $success;

    /**
     * @var string
     */
    private $paymentId;

    /**
     * @var string
     */
    private $message;

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     */
    public function setSuccess($success) : void
    {
        $this->success = $success;
    }

    /**
     * @return string
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * @param string $paymentId
     */
    public function setPaymentId($paymentId) : void
    {
        $this->paymentId = $paymentId;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message) : void
    {
        $this->message = $message;
    }
}
