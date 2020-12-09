<?php

namespace EinsUndEins\PluginTransactionMailExtender\Tests\Mapping;

use EinsUndEins\PluginTransactionMailExtender\Mapping\Mapping;
use EinsUndEins\PluginTransactionMailExtender\Mapping\StateMapping;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class StateMappingTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&SystemConfigService
     */
    private $config;

    protected function setUp(): void
    {
        $this->config = $this->createMock(SystemConfigService::class);
        $this->config->expects(self::once())
            ->method('get')
            ->with('TransactionMailExtender.config.statusmapping')
            ->willReturn([
                'parent.state-state1' => 'state1',
                'parent.state-state2' => 'state2',
                ]);
    }

    public function testGetValueBy(): void
    {
        $mapping = new StateMapping($this->config);

        self::assertSame('state2', $mapping->getValueBy('parent.state-state2'));
    }

    public function testDefaultIfStateNotFound(): void
    {
        $mapping = new StateMapping($this->config);

        self::assertSame('OrderProcessing', $mapping->getValueBy('unknown'));
    }
}
