<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\GetStatusInterface;

final class StatusAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request): void
    {
        /** @var GetStatusInterface $request */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var ArrayObject $payment */
        $payment = $request->getModel();
        $paymentDetails = $payment->toUnsafeArray();

        if (empty($paymentDetails) || !$paymentDetails['preference']) {
            $request->markNew();

            return;
        }

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (!isset($httpRequest->query['collection_status'])) {
            $request->markNew();

            return;
        }

        $status = $httpRequest->query['collection_status'];

        if ($status === 'approved') {
            $request->markCaptured();
        } elseif ($status === 'rejected') {
            $request->markFailed();
        } elseif ($status === 'pending') {
            $request->markPending();
        } else {
            $request->markCanceled();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
