<?php

namespace EinsUndEins\MailMSmartInboxConnector\SchemaOrg;

use EinsUndEins\MailMSmartInboxConnector\StateMapping\Mapper;
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
     * @var Mapper
     */
    private $stateMapping;

    /**
     * @var SystemConfigService
     */
    private $configService;

    public function __construct(Mapper $stateMapping, SystemConfigService $configService)
    {
        $this->stateMapping = $stateMapping;
        $this->configService = $configService;
    }

    public function render(OrderEntity $order): string
    {
        if ($this->isDeactivated()) {
            return '';
        }

        $orderNumber = $order->getOrderNumber() ?? '';
        $orderState = $this->stateMapping->getValueBy($order->getStateMachineState());
        $shopName = $this->getShopName();

        $schemaOrgOrder = new Order($orderNumber, $orderState, $shopName);

        $output = (new SchemaOrgOrderRenderer($schemaOrgOrder))->render();

        foreach ($order->getDeliveries() as $delivery) {
            $trackingNumbers = implode(', ', $delivery->getTrackingCodes());
            $deliveryName = $this->getDeliveryName($delivery);
            $deliveryState = $this->stateMapping->getValueBy($delivery->getStateMachineState());

            $schemaOrgDelivery = new ParcelDelivery($deliveryName, $trackingNumbers, $orderNumber, $deliveryState, $shopName);

            $output .= (new ParcelDeliveryRenderer($schemaOrgDelivery))->render();
        }

        return $output;
    }

    private function isDeactivated(): bool
    {
        return ! (bool) $this->configService->get('MailMSmartInboxConnector.config.enable');
    }

    private function getShopName(): string
    {
        // @phpstan-ignore-next-line
        return (string) $this->configService->get('core.basicInformation.shopName');
    }

    private function getDeliveryName(OrderDeliveryEntity $delivery): string
    {
        return $delivery->getShippingMethod() !== null ? $delivery->getShippingMethod()->getName() : '';
    }
}
