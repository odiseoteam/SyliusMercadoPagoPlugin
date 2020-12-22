<?php

declare(strict_types=1);

namespace Odiseo\SyliusMercadoPagoPlugin\Payum\Action;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use MercadoPago\Item;
use MercadoPago\Payer;
use MercadoPago\Preference;
use MercadoPago\SDK;
use Odiseo\SyliusMercadoPagoPlugin\Payum\MercadoPagoApi;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class CaptureAction implements
    ActionInterface,
    ApiAwareInterface,
    GatewayAwareInterface,
    GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /** @var MercadoPagoApi */
    private $api;

    /** @var CacheManager */
    private $imagineCacheManager;

    public function __construct(CacheManager $imagineCacheManager)
    {
        $this->imagineCacheManager = $imagineCacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute($request): void
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        $this->gateway->execute($status = new GetStatus($payment));

        if ($status->isNew()) {
            /** @var OrderInterface $order */
            $order = $payment->getOrder();

            /** @var CustomerInterface $customer */
            $customer = $order->getCustomer();

            /** @var AddressInterface $billingAddress */
            $billingAddress = $order->getBillingAddress();

//            $items = $order->getItems();

            SDK::setAccessToken($this->api->getAccessToken());

            SDK::setIntegratorId('dev_11586dc9e7f311eab4a00242ac130004');

            $preference = new Preference();

            try {
                $preferenceItems = [];

//                TODO create items for products when MercadoPago fix the API

//                foreach ($items as $item) {
//                    $preferenceItem = new Item();
//
//                    $preferenceItem->__set('id', $item->getId());
//                    $preferenceItem->__set('title', $item->getProductName());
//                    $preferenceItem->__set('quantity', $item->getQuantity());
//                    $preferenceItem->__set('currency_id', $order->getCurrencyCode());
//                    $preferenceItem->__set('unit_price', $item->getUnitPrice() / 100);
//
//                    /** @var ProductInterface $product */
//                    $product = $item->getProduct();
//
//                    if (!$product->getImagesByType('thumbnail')->isEmpty()) {
//                        /** @var ImageInterface $image */
//                        $image = $product->getImagesByType('thumbnail')->first();
//
//                        $path = $this->imagineCacheManager->getBrowserPath(
//                            (string)parse_url($image->getPath() ?: '', PHP_URL_PATH),
//                            'sylius_shop_product_tiny_thumbnail'
//                        );
//                    } elseif ($product->getImages()->first()) {
//                        /** @var ImageInterface $image */
//                        $image = $product->getImages()->first();
//
//                        $path = $this->imagineCacheManager->getBrowserPath(
//                            (string)parse_url($image->getPath() ?: '', PHP_URL_PATH),
//                            'sylius_shop_product_tiny_thumbnail'
//                        );
//                    } else {
//                        $path = '//placehold.it/64x64';
//                    }
//
//                    $preferenceItem->__set('picture_url', $path);
//
//                    $preferenceItems[] = $preferenceItem;
//                }
//
//                if ($order->getShippingTotal() > 0) {
//                    $shipment = new Item();
//
//                    $shipment->__set('id', 'shipping');
//                    $shipment->__set('title', 'Shipping');
//                    $shipment->__set('quantity', 1);
//                    $shipment->__set('currency_id', $order->getCurrencyCode());
//                    $shipment->__set('unit_price', $order->getShippingTotal() / 100);
//
//                    $preferenceItems[] = $shipment;
//                }
//
//                if ($order->getAdjustmentsTotalRecursively('tax') > 0) {
//                    $tax = new Item();
//
//                    $tax->__set('id', 'tax');
//                    $tax->__set('title', 'Tax');
//                    $tax->__set('quantity', 1);
//                    $tax->__set('currency_id', $order->getCurrencyCode());
//                    $tax->__set('unit_price', $order->getAdjustmentsTotalRecursively('tax') / 100);
//
//                    $preferenceItems[] = $tax;
//                }

                $preferenceItem = new Item();

                $orderTotal = $order->getTotal() / 100;

                $preferenceItem->__set('id', $order->getId());
                $preferenceItem->__set('title', 'TOTAL');
                $preferenceItem->__set('quantity', 1);
                $preferenceItem->__set('currency_id', $order->getCurrencyCode());
                $preferenceItem->__set('unit_price', $orderTotal);

                $preferenceItems[] = $preferenceItem;

                $preference->__set('items', $preferenceItems);

                /** Todo try to implement installments
                $preferencePayment = new Payment();

                $channel = $order->getChannel();
                $minimumForInstalments = $channel->getMinimumOrderTotalForInstalments();
                if ($minimumForInstalments) {
                    if ($orderTotal < $minimumForInstalments) {
                        $preferencePayment->__set('installments', 1);
                    }
                }

                $preference->__set('payment_methods', $preferencePayment);
                 **/

                $payer = new Payer();

                $payerFirstName = $customer->getFirstName() ?: $billingAddress->getFirstName();
                $payerLastName = $customer->getLastName() ?: $billingAddress->getLastName();
                $payerEmail = $customer->getEmail() ?: null;
                $payerPhoneNumber = $customer->getPhoneNumber() ?: $billingAddress->getPhoneNumber();

                if ($payerFirstName) {
                    $payer->__set('name', $payerFirstName);
                }
                if ($payerLastName) {
                    $payer->__set('surname', $payerLastName);
                }
                if ($payerEmail) {
                    $payer->__set('email', $payerEmail);
                }
                if ($payerPhoneNumber) {
                    $payer->__set('phone', [
                        'number' => $payerPhoneNumber
                    ]);
                }
                if ($billingAddress instanceof AddressInterface) {
                    $payer->__set('address', [
                        'street_name' => $billingAddress->getStreet(),
                        'zip_code' => $billingAddress->getPostcode(),
                    ]);
                }

                $preference->__set('payer', $payer);

                $preference->__set('external_reference', $order->getNumber());

                $preference->__set('back_urls', [
                    'success' => $request->getToken()->getAfterUrl(),
                    'failure' => $request->getToken()->getAfterUrl(),
                    'pending' => $request->getToken()->getAfterUrl()
                ]);

                // Create notification url with Notify Token
                $notifyToken = $this->tokenFactory->createNotifyToken(
                    $request->getToken()->getGatewayName(),
                    $request->getToken()->getDetails()
                );
                $preference->__set('notification_url', $notifyToken->getTargetUrl().'?source_news=webhooks');

                $preference->__set('auto_return', 'all');

                $status = 400;
                $message = 'KO';
                $preferenceData = null;

                if ($preference->save()) {
                    $status = 200;
                    $message = 'Preference created!';
                    $preferenceData = $preference->toArray();
                }

                $response = [
                    'status' => $status,
                    'message' => $message,
                    'preference' => $preferenceData
                ];
            } catch (\Exception $exception) {
                $response = [
                    'status' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'preference' => null
                ];
            } finally {
                $payment->setDetails($response);
            }

            if ($response['status'] === 200) {
                $initPoint = $this->api->isSandbox()
                    ? $preference->__get('sandbox_init_point')
                    : $preference->__get('init_point')
                ;

                throw new HttpRedirect($initPoint);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
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
