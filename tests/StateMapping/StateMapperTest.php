<?php

namespace EinsUndEins\SmartInboxConnector\Tests\StateMapping;

use EinsUndEins\SmartInboxConnector\StateMapping\StateMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\StateMachine\StateMachineEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class StateMapperTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&SystemConfigService
     */
    private $config;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&EntityRepositoryInterface
     */
    private $repository;

    protected function setUp(): void
    {
        $this->config = $this->createMock(SystemConfigService::class);
        $this->config->expects(self::once())
            ->method('get')
            ->with('MailMSmartInboxConnector.config.statusmapping')
            ->willReturn([
                'parent.state-state1' => 'state1',
                'parent.state-state2' => 'state2',
                ]);

        $this->repository = $this->createMock(EntityRepositoryInterface::class);
    }

    public function testGetValueBy(): void
    {
        $mapping = new StateMapper($this->config, $this->repository);

        $state = $this->createState('parent.state', 'state2');

        self::assertSame('state2', $mapping->getValueBy($state));
    }

    public function testGetValueByWithLoadingParentState(): void
    {
        $stateMachine = $this->createStub(StateMachineEntity::class);
        $stateMachine->method('getTechnicalName')
            ->willReturn('parent.state');

        $emptyResult = $this->createMock(EntitySearchResult::class);
        $emptyResult->expects(self::once())
            ->method('count')
            ->willReturn(1);
        $emptyResult->expects(self::once())
            ->method('first')
            ->willReturn($stateMachine);

        $this->repository->method('search')
            ->willReturn($emptyResult);

        $mapping = new StateMapper($this->config, $this->repository);

        $state = new StateMachineStateEntity();
        $state->setTechnicalName('state1');
        $state->setStateMachineId('1234567890');

        self::assertSame('state1', $mapping->getValueBy($state));
    }

    /**
     * @dataProvider provideUnknownStates
     */
    public function testDefaultIfStateNotFound(?StateMachineStateEntity $state): void
    {
        $emptyResult = $this->createStub(EntitySearchResult::class);
        $emptyResult->method('count')->willReturn(0);

        $this->repository->method('search')
            ->willReturn($emptyResult);

        $mapping = new StateMapper($this->config, $this->repository);

        self::assertSame('OrderProcessing', $mapping->getValueBy($state));
    }

    public function provideUnknownStates(): iterable
    {
        yield 'Unknwon StateMachineStateEntity' => [$this->createState('parent.state', 'unknown')];
        yield 'No StateMachineEntity' => [$this->createState(null, 'state1')];
        yield 'null state' => [null];
    }

    /**
     * @return MockObject&StateMachineStateEntity
     */
    private function createState(?string $parentState, string $state): StateMachineStateEntity
    {
        $stateMachineState = $this->createStub(StateMachineStateEntity::class);
        $stateMachineState->method('getTechnicalName')
            ->willReturn($state);
        $stateMachineState->method('getStateMachineId')
            ->willReturn('1234567890');

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
