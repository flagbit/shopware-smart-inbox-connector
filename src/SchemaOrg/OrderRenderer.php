<?php

namespace EinsUndEins\PluginTransactionMailExtender\SchemaOrg;

use EinsUndEins\PluginTransactionMailExtender\Mapping\Mapping;
use EinsUndEins\SchemaOrgMailBody\Model\Order;
use EinsUndEins\SchemaOrgMailBody\Model\ParcelDelivery;
use EinsUndEins\SchemaOrgMailBody\Renderer\OrderRenderer as SchemaOrgOrderRenderer;
use EinsUndEins\SchemaOrgMailBody\Renderer\ParcelDeliveryRenderer;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class OrderRenderer implements Renderer
{
    /**
     * @var Mapping
     */
    private $statusMapping;

    /**
     * @var SystemConfigService
     */
    private $configService;

    public function __construct(Mapping $statusMapping, SystemConfigService $configService)
    {
        $this->statusMapping = $statusMapping;
        $this->configService = $configService;
    }

    public function render(OrderEntity $order): string
    {
        if ($this->isDeactivated()) {
            return '';
        }

        $orderNumber = $order->getOrderNumber() ?? '';
        $technicalStatusName = $this->getOrderStatus($order);
        $orderStatus = $this->statusMapping->getValueBy($technicalStatusName);
        $shopName = $this->getShopName();

        $schemaOrgOrder = new Order($orderNumber, $orderStatus, $shopName);

        $output = (new SchemaOrgOrderRenderer($schemaOrgOrder))->render();

        foreach ($order->getDeliveries() as $delivery) {
            $trackingNumbers = implode(', ', $delivery->getTrackingCodes());
            $deliveryName = $this->getDeliveryName($delivery);

            $schemaOrgDelivery = new ParcelDelivery($deliveryName, $trackingNumbers, $orderNumber, $orderStatus, $shopName);

            $output .= (new ParcelDeliveryRenderer($schemaOrgDelivery))->render();
        }

        return $output;
    }

    private function isDeactivated(): bool
    {
        return ! (bool) $this->configService->get('TransactionMailExtender.config.enable');
    }

    private function getShopName(): string
    {
        // @phpstan-ignore-next-line
        return (string) $this->configService->get('core.basicInformation.shopName');
    }

    private function getOrderStatus(OrderEntity $order): string
    {
        return $order->getStateMachineState() !== null ? $order->getStateMachineState()->getTechnicalName() : '';
    }

    private function getDeliveryName(OrderDeliveryEntity $delivery): string
    {
        return $delivery->getShippingMethod() !== null ? $delivery->getShippingMethod()->getName() : '';
    }
}
