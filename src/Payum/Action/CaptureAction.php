<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum\Action;

use MercadoPago\Item;
use MercadoPago\Preference;
use MercadoPago\SDK;
use Odiseo\SyliusMercadoPagoPlugin\Payum\MercadoPagoApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Payum\Core\Request\Capture;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

final class CaptureAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var MercadoPagoApi
     */
    private $api;

    /**
     * @var EngineInterface
     */
    private $twig;

    public function __construct(EngineInterface $twig)
    {
        $this->twig = $twig;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        /** @var OrderInterface $order */
        $order = $payment->getOrder();

        $items = $order->getItems();

        SDK::setAccessToken($this->api->getAccessToken());

        //dd(1);
        $preference = new Preference();

        try {
            $preferenceItems = [];
            foreach ($items as $item) {
                $preferenceItem = new Item();
                $preferenceItem->__set('title', $item->getProductName());
                $preferenceItem->__set('quantity', $item->getQuantity());
                $preferenceItem->__set('unit_price', $item->getUnitPrice() / 100);

                $preferenceItems[] = $preferenceItem;
            }

            $preference->__set('items', $preferenceItems);

            $preference->__set('back_urls', array(
                "success" => $request->getToken()->getAfterUrl(),
                "failure" => $request->getToken()->getAfterUrl(),
                "pending" => $request->getToken()->getAfterUrl(),
            ));
            $preference->__set('auto_return', "all");

            $status = 400;
            $message = 'KO';

            if ($preference->save()) {
                $status = 200;
                $message = 'OK';
            }

            $response = [
                'status' => $status,
                'message' => $message
            ];
        } catch (\Exception $exception) {
            $response = [
                'status' => 400,
                'message' => $exception->getMessage()
            ];
        } finally {
            $payment->setDetails($response);
        }

        if ($response['status'] === 200) {
            $viewResponse = $this->twig->renderResponse('bundles/SyliusShopBundle/Checkout/obtain_pay_button.html.twig', [
                'preference' => $preference,
                'order' => $order
            ]);

            throw new HttpResponse($viewResponse);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
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
