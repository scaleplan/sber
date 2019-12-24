<?php

namespace Scaleplan\Sberbank;

use Psr\Log\LoggerInterface;
use Scaleplan\Cache\RedisCache;
use Scaleplan\Console\AbstractCommand;
use Scaleplan\Sberbank\Events\CompletedEvent;
use Scaleplan\Sberbank\Events\RejectEvent;
use function Scaleplan\DependencyInjection\get_required_container;
use function Scaleplan\Event\dispatch;

/**
 * Class PaymentsCheckDaemonCommand
 */
class SberbankPaymentsCheckCommand extends AbstractCommand
{
    public const SBERBANK_PAYMENTS_KEY = 'sberbank-payments';

    public const SIGNATURE = 'sberbank-payments-check';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Redis
     */
    protected $redis;

    /**
     * SberbankPaymentsCheckCommand constructor.
     *
     * @throws \ReflectionException
     * @throws \Scaleplan\Cache\Exceptions\RedisCacheException
     * @throws \Scaleplan\Console\Exceptions\CommandSignatureIsEmptyException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ContainerTypeNotSupportingException
     * @throws \Scaleplan\DependencyInjection\Exceptions\DependencyInjectionException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ParameterMustBeInterfaceNameOrClassNameException
     * @throws \Scaleplan\DependencyInjection\Exceptions\ReturnTypeMustImplementsInterfaceException
     */
    public function __construct()
    {
        parent::__construct();
        $this->logger = get_required_container(LoggerInterface::class);
        $this->redis = (new RedisCache(true))->getCacheConnect();
    }

    /**
     * Processing loop
     *
     * @throws \Throwable
     */
    public function run() : void
    {
        $this->logger->info('Start checking');
        $paymentsIds = $this->redis->sMembers(static::SBERBANK_PAYMENTS_KEY);
        $sberbank = new Sberbank($this->logger);

        try {
            foreach ($paymentsIds as $paymentId) {

                switch ($sberbank->checkPayment($paymentId)) {
                    case $sberbank::SIGNAL_OK:
                        $this->logger->info(
                            "Payment $paymentId completed"
                        );
                        dispatch(CompletedEvent::class);
                        break;

                    case $sberbank::SIGNAL_REJECT:
                        $this->logger->info(
                            "Payment $paymentId rejected"
                        );
                        dispatch(RejectEvent::class);
                        break;
                }

                $this->redis->sRem(static::SBERBANK_PAYMENTS_KEY, $paymentId);
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
