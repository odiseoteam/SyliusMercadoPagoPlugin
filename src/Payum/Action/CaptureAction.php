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
use Sylius\Bundle\PayumBundle\Request\GetStatus;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Core\Model\ProductInterface;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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

            $items = $order->getItems();

            SDK::setAccessToken($this->api->getAccessToken());

            $preference = new Preference();

            try {
                $preferenceItems = [];

                foreach ($items as $item) {
                    $preferenceItem = new Item();
                    $preferenceItem->__set('title', $item->getProductName());
                    $preferenceItem->__set('quantity', $item->getQuantity());
                    $preferenceItem->__set('currency_id', $order->getCurrencyCode());
                    $preferenceItem->__set('unit_price', $item->getUnitPrice() / 100);

                    /** @var ProductInterface $product */
                    $product = $item->getProduct();

                    if (!$product->getImagesByType('thumbnail')->isEmpty()) {
                        /** @var ImageInterface $image */
                        $image = $product->getImagesByType('thumbnail')->first();

                        $path = $this->imagineCacheManager->getBrowserPath(
                            parse_url($image->getPath(), PHP_URL_PATH),
                            'sylius_shop_product_tiny_thumbnail'
                        );
                    } elseif ($product->getImages()->first()) {
                        /** @var ImageInterface $image */
                        $image = $product->getImages()->first();

                        $path = $this->imagineCacheManager->getBrowserPath(
                            parse_url($image->getPath(), PHP_URL_PATH),
                            'sylius_shop_product_tiny_thumbnail'
                        );
                    } else {
                        $path = '//placehold.it/64x64';
                    }

                    $preferenceItem->__set('picture_url', $path);

                    $preferenceItems[] = $preferenceItem;
                }

                $preference->__set('items', $preferenceItems);

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
                        'area_code' => '',
                        'number' => $payerPhoneNumber
                    ]);
                }
                if ($billingAddress) {
                    $payer->__set('address', [
                        'street_name' => $billingAddress->getStreet(),
                        'street_number' => '',
                        'zip_code' => $billingAddress->getPostcode(),
                    ]);
                }

                $preference->__set('payer', $payer);

                $preference->__set('back_urls', array(
                    "success" => $request->getToken()->getAfterUrl(),
                    "failure" => $request->getToken()->getAfterUrl(),
                    "pending" => $request->getToken()->getAfterUrl(),
                ));

                $preference->__set('auto_return', "all");

                $status = 400;
                $message = 'KO';
                $preferenceId = null;

                if ($preference->save()) {
                    $status = 200;
                    $message = 'Preference created!';
                    $preferenceId = $preference->__get('id');
                }

                $response = [
                    'status' => $status,
                    'message' => $message,
                    'preference_id' => $preferenceId
                ];
            } catch (\Exception $exception) {
                $response = [
                    'status' => $exception->getCode(),
                    'message' => $exception->getMessage()
                ];
            } finally {
                $payment->setDetails($response);
            }

            if ($response['status'] === 200) {
                throw new HttpRedirect($preference->__get('init_point'));
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
