<?php

namespace Scaleplan\Sberbank\DTO;

use Scaleplan\Sberbank\Sberbank;

/**
 * Class CompanyCardRefillStructure
 */
class CardRefillDTO
{
    /**
     * @var string
     */
    private $approvalCode;
    /**
     * @var string
     */
    private $orderNumber;
    /**
     * @var string
     */
    private $panMasked;
    /**
     * @var int
     */
    private $refNum;
    /**
     * @var string
     */
    private $digest;
    /**
     * @var int
     */
    private $currency;
    /**
     * @var string
     */
    private $paymentDate;
    /**
     * @var string
     */
    private $formattedAmount;
    /**
     * @var string
     */
    private $formattedFeeAmount;
    /**
     * @var string
     */
    private $status;

    /**
     * CompanyCardRefillStructure constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key)) {
                continue;
            }

            $this->{$key} = $value;
        }
    }

    /**
     * @return string
     */
    public function getDigest() : string
    {
        return $this->digest;
    }

    /**
     * @param string $digest
     */
    public function setDigest(string $digest) : void
    {
        $this->digest = $digest;
    }

    /**
     * @return string
     *
     * @throws \Scaleplan\Helpers\Exceptions\EnvNotFoundException
     */
    public function toDigestString() : string
    {
        return implode(
                [
                    $this->getStatus(),
                    $this->getFormattedAmount(),
                    $this->getCurrency(),
                    $this->getApprovalCode(),
                    $this->getOrderNumber(),
                    $this->getPanMasked(),
                    $this->getRefNum(),
                    $this->getPaymentDate(),
                    $this->getFormattedFeeAmount(),
                    Sberbank::getSecretToken(),
                ]
            ) . ';';
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status) : void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getFormattedAmount() : string
    {
        return $this->formattedAmount;
    }

    /**
     * @param string $formattedAmount
     */
    public function setFormattedAmount(string $formattedAmount) : void
    {
        $this->formattedAmount = $formattedAmount;
    }

    /**
     * @return int
     */
    public function getCurrency() : int
    {
        return $this->currency;
    }

    /**
     * @param int $currency
     */
    public function setCurrency(int $currency) : void
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getApprovalCode()
    {
        return $this->approvalCode;
    }

    /**
     * @param string $approvalCode
     */
    public function setApprovalCode($approvalCode) : void
    {
        $this->approvalCode = $approvalCode;
    }

    /**
     * @return string
     */
    public function getOrderNumber() : string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber) : void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return string
     */
    public function getPanMasked() : string
    {
        return $this->panMasked;
    }

    /**
     * @param string $panMasked
     */
    public function setPanMasked(string $panMasked) : void
    {
        $this->panMasked = $panMasked;
    }

    /**
     * @return int
     */
    public function getRefNum() : int
    {
        return $this->refNum;
    }

    /**
     * @param int $refNum
     */
    public function setRefNum(int $refNum) : void
    {
        $this->refNum = $refNum;
    }

    /**
     * @return string
     */
    public function getPaymentDate() : string
    {
        return $this->paymentDate;
    }

    /**
     * @param string $paymentDate
     */
    public function setPaymentDate(string $paymentDate) : void
    {
        $this->paymentDate = $paymentDate;
    }

    /**
     * @return string
     */
    public function getFormattedFeeAmount() : string
    {
        return $this->formattedFeeAmount;
    }

    /**
     * @param string $formattedFeeAmount
     */
    public function setFormattedFeeAmount(string $formattedFeeAmount) : void
    {
        $this->formattedFeeAmount = $formattedFeeAmount;
    }
}
