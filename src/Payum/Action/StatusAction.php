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

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var GetStatusInterface $getStatus */
        $getStatus = $request;

        /** @var array $details */
        $details = ArrayObject::ensureArrayObject($getStatus->getModel());

        if (count($details) === 0 || !isset($details['preference'])) {
            $getStatus->markNew();

            return;
        }

        $status = null;

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->query['collection_status'])) {
            $status = $httpRequest->query['collection_status'];
        }

        if (isset($details['payment']['status']) && !$status) {
            $status = $details['payment']['status'];
        }

        if (!$status) {
            $getStatus->markNew();

            return;
        }

        if ('approved' === $status) {
            $getStatus->markCaptured();
        } elseif ('rejected' === $status) {
            $getStatus->markFailed();
        } elseif ('pending' === $status || 'in_process' === $status) {
            $getStatus->markPending();
        } else {
            $getStatus->markCanceled();
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
