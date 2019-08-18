<?php

namespace Scaleplan\Sberbank;

use Psr\Log\LoggerInterface;
use Scaleplan\DTO\DTO;
use Scaleplan\Http\RemoteResponse;
use Scaleplan\Http\Request;
use Scaleplan\HttpStatus\HttpStatusCodes;
use Scaleplan\Main\App;
use Scaleplan\Sberbank\Constants\Currencies;
use Scaleplan\Sberbank\DTO\CardRefillDTO;
use Scaleplan\Sberbank\DTO\ErrorDTO;
use Scaleplan\Sberbank\DTO\OrderDTO;
use Scaleplan\Sberbank\DTO\ResponseDTO;
use Scaleplan\Sberbank\DTO\SberbankResponseDTO;
use Scaleplan\Sberbank\Exceptions\InvalidDigestException;
use Scaleplan\Sberbank\Exceptions\PaymentGateResponseException;
use Scaleplan\Sberbank\Exceptions\SberbankException;
use Scaleplan\Sberbank\Exceptions\UnprocessableCurrencyException;
use function Scaleplan\Helpers\get_required_env;

/**
 * Class Sberbank
 *
 * @package Scaleplan\Sberbank
 */
class Sberbank
{
    public const ISO_RUB_CODE = 643;

    public const PAYMENT_STATUS_SUCCESSFUL = 2;
    public const HOLD_STATUS_SUCCESSFUL    = 1;
    public const STATUS_REGISTERED         = 0;
    public const STATUS_CANCELED           = 3;
    public const STATUS_REFUND             = 4;
    public const STATUS_ISSUING_BANK_AUTH  = 5;
    public const STATUS_AUTH_REJECTED      = 6;

    public const ORDER_NOT_FOUND_ERROR_CODE = 6;

    public const SIGNAL_OK     = 1;
    public const SIGNAL_WAIT   = 0;
    public const SIGNAL_REJECT = 2;

    public const ALLOW_CURRENCY = [
        Currencies::RUB,
    ];

    public const PAYMENT_TTL = 1200;

    public const DEFAULT_CURRENCY = Currencies::RUB;

    public const PAYMENT_HOLD = true;

    public const OPERATION_REGISTER_PRE_AUTH = 'registerPreAuth.do';
    public const OPERATION_REGISTER          = 'register.do';
    public const OPERATION_REVERSE           = 'reverse.do';

    public const CALLBACK_OPERATION_REVERSED  = 'reversed';
    public const CALLBACK_OPERATION_DECLINED  = 'declinedByTimeout';
    public const CALLBACK_OPERATION_DEPOSITED = 'deposited';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Sberbank constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $sberbankOrderId
     *
     * @return RemoteResponse
     * @throws \Scaleplan\Helpers\Exceptions\EnvNotFoundException
     * @throws \Exception
     */
    public function revertHoldPayment(string $sberbankOrderId) : RemoteResponse
    {
        $dto = new OrderDTO();
        $dto->setUserName(get_required_env('SBERBANK_USER'));
        $dto->setPassword(get_required_env('SBERBANK_PASSWORD'));
        $dto->setOrderId($sberbankOrderId);
        $dto->setLanguage(App::getLang());

        return $this->callSberbankApi(static::OPERATION_REVERSE, $dto);
    }

    /**
     * Обращение к API Сбербанка
     *
     * @param $operation
     * @param DTO $dto
     *
     * @return RemoteResponse
     *
     * @throws \Exception
     */
    protected function callSberbankApi($operation, DTO $dto) : RemoteResponse
    {
        try {
            $dto->validate();
            $request = new Request(get_required_env('SBERBANK_API_URL') . $operation,
                [
                    'form_params' => $dto->toFullArray(),
                ]
            );
            $request->setDtoClass(ResponseDTO::class);

            $response = $request->send();

            $this->responseCheck($request, $response);

            return $response;
        } catch (SberbankException $e) {
            $errorDTO = new ErrorDTO();
            $errorDTO->setUrl(get_required_env('SBERBANK_API_URL') . $operation);
            $errorDTO->setParams($dto->toFullArray());
            if (!empty($response)) {
                $errorDTO->setResponse($response->getResult()->toFullArray());
                $errorDTO->setHttpStatusCode($response->getHttpCode());
            }
            $this->logger->error($e->getMessage(), $errorDTO->toFullArray());

            throw $e;
        }
    }

    /**
     * @param Request $request
     * @param RemoteResponse $response
     *
     * @throws SberbankException
     */
    protected function responseCheck(Request $request, RemoteResponse $response) : void
    {
        /** @var ResponseDTO $responseDTO */
        $responseDTO = $response->getResult();
        if (!$responseDTO) {
            throw new SberbankException(
                'Sberbank API call fails. ' . json_encode($request->getParams(), JSON_UNESCAPED_UNICODE),
                HttpStatusCodes::HTTP_BAD_REQUEST
            );
        }

        if ($responseDTO->getErrorCode()) {
            throw new SberbankException(
                'Sberbank API call returns an error: ' . $responseDTO->getErrorCode(),
                $response->getHttpCode()
            );
        }
    }

    /**
     * @param $request
     *
     * @return SberbankResponseDTO
     * @throws \Exception
     */
    public function checkSuccessPayment(Request $request) : SberbankResponseDTO
    {
        $sberbankOrderId = $request->getParam('orderId');
        $dto = new OrderDTO();
        $dto->setUserName(get_required_env('SBERBANK_USER'));
        $dto->setPassword(get_required_env('SBERBANK_PASSWORD'));
        $dto->setOrderId($sberbankOrderId);

        $response = $this->callSberbankApi('getOrderStatusExtended.do', $dto);
        /** @var ResponseDTO $responseDTO */
        $responseDTO = $response->getResult();

        if (!$responseDTO->getOrderNumber() || !$responseDTO->getOrderStatus()) {
            $sberbankResponseDTO = new SberbankResponseDTO();
            $sberbankResponseDTO->setSuccess(false);
            $sberbankResponseDTO->setPaymentId($responseDTO->getOrderNumber());
            $sberbankResponseDTO->setMessage("Sberbank API returns wrong response. Order #$sberbankOrderId. "
                . json_encode($response->getResult(), JSON_UNESCAPED_UNICODE));

            return $sberbankResponseDTO;
        }

        if (!\in_array(
            $responseDTO->getOrderStatus(),
            [self::PAYMENT_STATUS_SUCCESSFUL, self::HOLD_STATUS_SUCCESSFUL], false)
        ) {
            $sberbankResponseDTO = new SberbankResponseDTO();
            $sberbankResponseDTO->setSuccess(false);
            $sberbankResponseDTO->setPaymentId($responseDTO->getOrderNumber());
            $sberbankResponseDTO->setMessage("Payment fails but TYP was called:(order $sberbankOrderId)");

            return $sberbankResponseDTO;
        }

        $sberbankResponseDTO = new SberbankResponseDTO();
        $sberbankResponseDTO->setSuccess(true);
        $sberbankResponseDTO->setPaymentId($responseDTO->getOrderNumber());

        return $sberbankResponseDTO;
    }

    /**
     * @param CardRefillDTO $dto
     *
     * @throws InvalidDigestException
     * @throws UnprocessableCurrencyException
     * @throws \Scaleplan\Helpers\Exceptions\EnvNotFoundException
     */
    public function refillConfirm(CardRefillDTO $dto) : void
    {
        $this->checkSberbankResponse($dto);
    }

    /**
     * @param CardRefillDTO $dto
     *
     * @throws InvalidDigestException
     * @throws UnprocessableCurrencyException
     * @throws \Scaleplan\Helpers\Exceptions\EnvNotFoundException
     */
    private function checkSberbankResponse(CardRefillDTO $dto) : void
    {
        $computedHash = hash_hmac(
            'sha256',
            $dto->toDigestString(),
            static::getSecretToken()
        );
        if (strtoupper($computedHash) !== $dto->getDigest()) {
            $this->refund($dto->getOrderNumber());

            throw new InvalidDigestException();
        }

        if ($dto->getCurrency() !== static::ISO_RUB_CODE) {
            $this->refund($dto->getOrderNumber());

            throw new UnprocessableCurrencyException();
        }
    }

    /**
     * @return string
     *
     * @throws \Scaleplan\Helpers\Exceptions\EnvNotFoundException
     */
    public static function getSecretToken() : string
    {
        return get_required_env('SBERBANK_TOKEN');
    }

    /**
     * @param string $sberbankOrderId
     *
     * @throws \Scaleplan\Helpers\Exceptions\EnvNotFoundException
     * @throws \Exception
     */
    public function refund(string $sberbankOrderId) : void
    {
        $dto = new OrderDTO();
        $dto->setUserName(get_required_env('SBERBANK_USER'));
        $dto->setPassword(get_required_env('SBERBANK_PASSWORD'));
        $dto->setOrderId($sberbankOrderId);
        $dto->setLanguage(App::getLang());
        $this->callSberbankApi(self::OPERATION_REVERSE, $dto);

    }

    /**
     * @param string $sberbankOrderId
     *
     * @return int
     *
     * @throws \Exception
     */
    public function checkPayment(string $sberbankOrderId) : int
    {
        try {
            $dto = new OrderDTO();
            $dto->setUserName(get_required_env('SBERBANK_USER'));
            $dto->setPassword(get_required_env('SBERBANK_PASSWORD'));
            $dto->setOrderId($sberbankOrderId);
            $response = $this->callSberbankApi('getOrderStatusExtended.do', $dto);
            /** @var ResponseDTO $responseDTO */
            $responseDTO = $response->getResult();
        } catch (\Exception $e) {
            if ($e->getCode() === static::ORDER_NOT_FOUND_ERROR_CODE) {
                return static::SIGNAL_REJECT;
            }

            throw $e;
        }

        if (!$responseDTO->getOrderNumber() || !\is_int($responseDTO->getOrderStatus())) {
            throw new PaymentGateResponseException();
        }

        switch ($responseDTO->getOrderStatus()) {
            case static::STATUS_REGISTERED:
            case static::STATUS_ISSUING_BANK_AUTH:
                // если нам сказали ждать, но дали ответ, что обработки не будет, рассматриваем это как отказ
                if (\is_int($responseDTO->getActionCode()) && $responseDTO->getActionCode() !== 0) {
                    return static::SIGNAL_REJECT;
                }
                return static::SIGNAL_WAIT;

            case static::HOLD_STATUS_SUCCESSFUL:
            case static::PAYMENT_STATUS_SUCCESSFUL:
                return static::SIGNAL_OK;

            case static::STATUS_CANCELED:
            case static::STATUS_REFUND:
            case static::STATUS_AUTH_REJECTED:
                return static::SIGNAL_REJECT;

            default:
                throw new PaymentGateResponseException();
        }
    }
}
