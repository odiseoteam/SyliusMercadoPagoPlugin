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
use Symfony\Component\HttpFoundation\Response;

final class NotifyAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var MercadoPagoApi */
    private $api;

    /**
     * {@inheritdoc}
     */
    public function execute($request): void
    {
        /** @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $content = file_get_contents("php://input");
        if ($content !== false) {
            $data = json_decode($content, true);
            $this->log($data);

            SDK::setAccessToken($this->api->getAccessToken());
            SDK::setIntegratorId('dev_11586dc9e7f311eab4a00242ac130004');

            if ('payment' == $data['type']) {
                /** @var Payment $payment */
                $payment = Payment::find_by_id($data['data']['id']);

                $paymentArray = $payment->toArray();
                $details['payment'] = $paymentArray;
            }
        }

        throw new HttpResponse('OK', Response::HTTP_OK);
    }

    /**
     * @param array $data
     */
    private function log(array $data): void
    {
        // Todo use a better way to log the information
        $log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
            "Attempt: ".(json_encode($data)).PHP_EOL.
            "-------------------------".PHP_EOL;

        file_put_contents(__DIR__.'/../../../../../../var/log/mercado_pago_log_'.date("j.n.Y").'.txt', $log, FILE_APPEND);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setApi($api): void
    {
        if (!$api instanceof MercadoPagoApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . MercadoPagoApi::class);
        }

        $this->api = $api;
    }
}
