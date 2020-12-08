<?php

namespace EinsUndEins\PluginTransactionMailExtender\SchemaOrg;

use EinsUndEins\PluginTransactionMailExtender\Mapping\Mapping;
use EinsUndEins\SchemaOrgMailBody\Model\Order;
use EinsUndEins\SchemaOrgMailBody\Model\ParcelDelivery;
use EinsUndEins\SchemaOrgMailBody\Renderer\OrderRenderer as SchemaOrgOrderRenderer;
use EinsUndEins\SchemaOrgMailBody\Renderer\ParcelDeliveryRenderer;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class OrderRenderer implements Renderer
{
    /**
     * @var Mapping
     */
    private $stateMapping;

    /**
     * @var SystemConfigService
     */
    private $configService;

    public function __construct(Mapping $stateMapping, SystemConfigService $configService)
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
        $technicalStateName = $this->createStateMappingKey($order->getStateMachineState());
        $orderState = $this->stateMapping->getValueBy($technicalStateName);
        $shopName = $this->getShopName();

        $schemaOrgOrder = new Order($orderNumber, $orderState, $shopName);

        $output = (new SchemaOrgOrderRenderer($schemaOrgOrder))->render();

        foreach ($order->getDeliveries() as $delivery) {
            $trackingNumbers = implode(', ', $delivery->getTrackingCodes());
            $deliveryName = $this->getDeliveryName($delivery);
            $deliveryState = $this->stateMapping->getValueBy($this->createStateMappingKey($delivery->getStateMachineState()));

            $schemaOrgDelivery = new ParcelDelivery($deliveryName, $trackingNumbers, $orderNumber, $deliveryState, $shopName);

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

    private function getDeliveryName(OrderDeliveryEntity $delivery): string
    {
        return $delivery->getShippingMethod() !== null ? $delivery->getShippingMethod()->getName() : '';
    }

    private function createStateMappingKey(?StateMachineStateEntity $state): string
    {
        if ($state === null) {
            return '';
        }

        $stateMachine = $state->getStateMachine();
        $stateMachineName = '';
        if ($stateMachine !== null) {
            $stateMachineName = $stateMachine->getTechnicalName();
        }

        return $stateMachineName . '-' . $state->getTechnicalName();
    }
}
