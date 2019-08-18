<?php

namespace Scaleplan\Sberbank\DTO;

use Scaleplan\DTO\DTO;

/**
 * Class ResponseDTO
 *
 * @package Scaleplan\Sberbank\DTO
 */
class ResponseDTO extends DTO
{
    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $orderStatus;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var int
     */
    private $actionCode;

    /**
     * @return int
     */
    public function getActionCode()
    {
        return $this->actionCode;
    }

    /**
     * @param int $actionCode
     */
    public function setActionCode($actionCode) : void
    {
        $this->actionCode = $actionCode;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param int $errorCode
     */
    public function setErrorCode($errorCode) : void
    {
        $this->errorCode = $errorCode;
    }

    /**
     * @return string
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * @param string $orderStatus
     */
    public function setOrderStatus($orderStatus) : void
    {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber) : void
    {
        $this->orderNumber = $orderNumber;
    }
}
