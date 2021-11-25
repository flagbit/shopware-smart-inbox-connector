<?php

namespace EinsUndEins\MailMSmartInboxConnector\Tests\Renderer;

use EinsUndEins\MailMSmartInboxConnector\StateMapping\Mapper;
use EinsUndEins\MailMSmartInboxConnector\SchemaOrg\OrderRenderer;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class OrderRendererTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&Mapper
     */
    private $mapping;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&SystemConfigService
     */
    private $config;


    protected function setUp(): void
    {
        $this->mapping = $this->createMock(Mapper::class);
        $this->config = $this->createMock(SystemConfigService::class);
    }

    public function testRendererIsDeactivated(): void
    {
        $this->prepareConfigMock(false);
        $renderer = new OrderRenderer($this->mapping, $this->config);

        self::assertSame('', $renderer->render($this->createOrder([])));
    }

    public function testRenderOrder(): void
    {
        $this->prepareConfigMock(true);
        $renderer = new OrderRenderer($this->mapping, $this->config);

        self::assertSame($this->expectedOrderHtml(), $renderer->render($this->createOrder([])));
    }

    public function testRenderOrderWithDelivery(): void
    {
        $this->prepareConfigMock(true);
        $renderer = new OrderRenderer($this->mapping, $this->config);

        $order = $this->createOrder([
            $this->createDelivery('Delivery1'),
            $this->createDelivery('Delivery2'),
        ]);

        self::assertSame($this->expectedOrderWithDeliveryHtml(), $renderer->render($order));
    }

    private function createOrder(array $deliveries): OrderEntity
    {
        $stateMachineState = $this->createStub(StateMachineStateEntity::class);

        $order = $this->createStub(OrderEntity::class);

        $order->method('getOrderNumber')
            ->willReturn('ordernumber');

        $order->method('getStateMachineState')
            ->willReturn($stateMachineState);

        $deliveryCollection = new OrderDeliveryCollection($deliveries);
        $order->method('getDeliveries')
            ->willReturn($deliveryCollection);

        $returnMap = [[$stateMachineState, 'OrderDelivered']];

        foreach ($deliveries as $delivery) {
            $returnMap[] = [$delivery->getStateMachineState(), 'OrderDelivered'];
        }

        $this->mapping->method('getValueBy')
            ->willReturnMap($returnMap);

        return $order;
    }

    private function createDelivery(string $deliveryName): OrderDeliveryEntity
    {
        $shippingMethod = $this->createStub(ShippingMethodEntity::class);
        $shippingMethod->method('getName')
            ->willReturn($deliveryName);

        $delivery = $this->createStub(OrderDeliveryEntity::class);

        $delivery->method('getTrackingCodes')
            ->willReturn(['code1', 'code2']);

        $delivery->method('getShippingMethod')
            ->willReturn($shippingMethod);

        // Internally used by OrderDeliveryCollection to hash every entry
        $delivery->method('getUniqueIdentifier')
            ->willReturn($deliveryName);

        $delivery->method('getStateMachineState')
            ->willReturn(
                $this->createStub(StateMachineStateEntity::class)
            );

        return $delivery;
    }

    private function prepareConfigMock(bool $returnValue): void
    {
        $this->config->expects(self::atLeast(1))
            ->method('get')
            ->willReturnMap([
                ['MailMSmartInboxConnector.config.enable', null, $returnValue],
                ['core.basicInformation.shopName', null, 'shopname'],
            ]);
    }

    private function expectedOrderHtml(): string
    {
        return <<<'HTML'
<div itemscope itemtype="http://schema.org/Order">
    <div itemprop="merchant" itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="shopname"/>
</div>
    <meta itemprop="orderNumber" content="ordernumber"/>
    <link itemprop="orderStatus" href="http://schema.org/OrderDelivered"/>
</div>
HTML;
    }

    private function expectedOrderWithDeliveryHtml(): string
    {
        return <<<'HTML'
<div itemscope itemtype="http://schema.org/Order">
    <div itemprop="merchant" itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="shopname"/>
</div>
    <meta itemprop="orderNumber" content="ordernumber"/>
    <link itemprop="orderStatus" href="http://schema.org/OrderDelivered"/>
</div><div itemscope itemtype="http://schema.org/ParcelDelivery">
    <div itemprop="carrier" itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="Delivery1"/>
</div>
    <meta itemprop="trackingNumber" content="code1, code2"/>
    <div itemprop="partOfOrder" itemscope itemtype="http://schema.org/Order">
        <div itemprop="merchant" itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="shopname"/>
</div>
    </div>
    <meta itemprop="orderNumber" content="ordernumber"/>
    <link itemprop="orderStatus" href="http://schema.org/OrderDelivered"/>
</div><div itemscope itemtype="http://schema.org/ParcelDelivery">
    <div itemprop="carrier" itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="Delivery2"/>
</div>
    <meta itemprop="trackingNumber" content="code1, code2"/>
    <div itemprop="partOfOrder" itemscope itemtype="http://schema.org/Order">
        <div itemprop="merchant" itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="shopname"/>
</div>
    </div>
    <meta itemprop="orderNumber" content="ordernumber"/>
    <link itemprop="orderStatus" href="http://schema.org/OrderDelivered"/>
</div>
HTML;
    }
}
