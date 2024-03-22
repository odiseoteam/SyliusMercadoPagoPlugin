<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum\Action;

use MercadoPago\Payment;
use MercadoPago\SDK;
use Odiseo\SyliusMercadoPagoPlugin\Payum\MercadoPagoApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

final class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private MercadoPagoApi $api;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Notify $notify */
        $notify = $request;

        $details = ArrayObject::ensureArrayObject($notify->getModel());

        $content = file_get_contents('php://input');
        if ($content !== false) {
            /** @var array $data */
            $data = json_decode($content, true);

            $this->log($data);

            SDK::setAccessToken($this->api->getAccessToken());
            SDK::setIntegratorId('dev_11586dc9e7f311eab4a00242ac130004');

            if ('payment' == $data['type']) {
                /**
                 * @psalm-suppress MixedArrayAccess
                 *
                 * @var Payment $payment
                 */
                $payment = Payment::find_by_id($data['data']['id']);

                $paymentArray = $payment->toArray();
                $details['payment'] = $paymentArray;
            }
        }

        throw new HttpResponse('OK', Response::HTTP_OK);
    }

    private function log(array $data): void
    {
        $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

        $this->logger->info(sprintf(
            '---- Mercado Pago----\ User: %s - %s\nAttempt: %s',
            $remoteAddress,
            date('F j, Y, g:i a'),
            json_encode($data),
        ));
    }

    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof MercadoPagoApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . MercadoPagoApi::class);
        }

        $this->api = $api;
    }
}
