<?php

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Order;

class OrderExportHandler
{
    private const API_URL = 'http://217.114.1.172/laravel/api/orders';
    private const HTTP_TIMEOUT = 3;

    public static function onSaleOrderSaved(Event $event): void
    {
        if (!Loader::includeModule('sale')) {
            return;
        }

        $order = $event->getParameter('ENTITY');
        if (!$order instanceof Order) {
            return;
        }

        try {
            self::sendOrder($order, (bool)$event->getParameter('IS_NEW'));
        } catch (Throwable $exception) {
            AddMessage2Log(
                sprintf(
                    'Order export failed. ORDER_ID=%s; ERROR=%s',
                    $order->getId(),
                    $exception->getMessage()
                ),
                'order_export'
            );
        }
    }

    private static function sendOrder(Order $order, bool $isNew): void
    {
        $payload = self::buildPayload($order, $isNew);

        $httpClient = new HttpClient([
            'socketTimeout' => self::HTTP_TIMEOUT,
            'streamTimeout' => self::HTTP_TIMEOUT,
            'disableSslVerification' => true,
        ]);
        $httpClient->setHeader('Content-Type', 'application/json', true);
        $httpClient->setHeader('Accept', 'application/json', true);

        $response = $httpClient->post(
            self::API_URL,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $status = (int)$httpClient->getStatus();
        if ($response === false || $status < 200 || $status >= 300) {
            throw new RuntimeException(sprintf(
                'HTTP status %s. Response: %s',
                $status ?: 'no response',
                mb_substr((string)$response, 0, 1000)
            ));
        }
    }

    private static function buildPayload(Order $order, bool $isNew): array
    {
        return [
            'event' => $isNew ? 'order.created' : 'order.updated',
            'order' => [
                'id' => (int)$order->getId(),
                'account_number' => (string)$order->getField('ACCOUNT_NUMBER'),
                'status_id' => (string)$order->getField('STATUS_ID'),
                'price' => (float)$order->getPrice(),
                'currency' => (string)$order->getCurrency(),
                'user_id' => (int)$order->getUserId(),
                'date_insert' => self::formatDate($order->getField('DATE_INSERT')),
                'date_update' => self::formatDate($order->getField('DATE_UPDATE')),
                'properties' => self::getProperties($order),
                'basket' => self::getBasketItems($order),
            ],
        ];
    }

    private static function getBasketItems(Order $order): array
    {
        $items = [];

        foreach ($order->getBasket() as $basketItem) {
            $items[] = [
                'product_id' => (int)$basketItem->getProductId(),
                'name' => (string)$basketItem->getField('NAME'),
                'quantity' => (float)$basketItem->getQuantity(),
                'price' => (float)$basketItem->getPrice(),
                'currency' => (string)$basketItem->getCurrency(),
            ];
        }

        return $items;
    }

    private static function getProperties(Order $order): array
    {
        $properties = [];

        $propertyCollection = $order->getPropertyCollection();
        if ($propertyCollection === null) {
            return $properties;
        }

        foreach ($propertyCollection as $property) {
            $code = (string)$property->getField('CODE');
            if ($code === '') {
                $code = 'PROPERTY_' . $property->getPropertyId();
            }

            $properties[$code] = $property->getValue();
        }

        return $properties;
    }

    private static function formatDate($date): ?string
    {
        if ($date instanceof Bitrix\Main\Type\DateTime) {
            return $date->format('c');
        }

        return $date ? (string)$date : null;
    }
}
