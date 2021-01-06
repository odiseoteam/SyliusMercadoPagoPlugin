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

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (count($details) === 0 || !isset($details['preference'])) {
            $request->markNew();

            return;
        }

        $status = null;

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->query['collection_status'])) {
            $status = $httpRequest->query['collection_status'];
        }

        if (!$status && isset($details['payment']) && isset($details['payment']['status'])) {
            $status = $details['payment']['status'];
        }

        if (!$status) {
            $request->markNew();

            return;
        }

        if ('approved' === $status) {
            $request->markCaptured();
        } elseif ('rejected' === $status) {
            $request->markFailed();
        } elseif ('pending' === $status || 'in_process' === $status) {
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
