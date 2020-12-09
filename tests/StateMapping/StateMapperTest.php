<?php

namespace EinsUndEins\PluginTransactionMailExtender\Tests\StateMapping;

use EinsUndEins\PluginTransactionMailExtender\StateMapping\StateMapper;
use phpDocumentor\Reflection\Types\Iterable_;
use PHPUnit\Framework\TestCase;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\StateMachine\StateMachineEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class StateMapperTest extends TestCase
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
        $mapping = new StateMapper($this->config);

        $state = $this->createState('parent.state', 'state2');

        self::assertSame('state2', $mapping->getValueBy($state));
    }

    /**
     * @dataProvider provideUnknownStates
     */
    public function testDefaultIfStateNotFound(?StateMachineStateEntity $state): void
    {
        $mapping = new StateMapper($this->config);

        self::assertSame('OrderProcessing', $mapping->getValueBy($state));
    }

    public function provideUnknownStates(): iterable
    {
        yield 'Unknwon StateMachineStateEntity' => [$this->createState('parent.state', 'unknown')];
        yield 'No StateMachineEntity' => [$this->createState(null, 'state1')];
        yield 'null state' => [null];
        yield 'Empty StateMachine' => [$this->createStub(StateMachineStateEntity::class)];
    }

    private function createState(?string $parentState, string $state): StateMachineStateEntity
    {
        $stateMachineState = $this->createStub(StateMachineStateEntity::class);
        $stateMachineState->method('getTechnicalName')
            ->willReturn($state);

        if ($parentState !== null) {
            $stateMachine = $this->createStub(StateMachineEntity::class);
            $stateMachine->method('getTechnicalName')
                ->willReturn($parentState);

            $stateMachineState->method('getStateMachine')
                ->willReturn($stateMachine);
        }

        return $stateMachineState;
    }
}
