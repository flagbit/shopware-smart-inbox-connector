<?php

namespace EinsUndEins\MailMSmartInboxConnector\StateMapping;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class StateMapper implements Mapper
{
    /**
     * @var array<string, string>
     */
    private $configService;

    /**
     * @var EntityRepositoryInterface
     */
    private $repository;

    /**
     * @var string
     */
    private static $defaultState = 'OrderProcessing';

    public function __construct(SystemConfigService $configService, EntityRepositoryInterface $stateMachineRepository)
    {
        $this->repository = $stateMachineRepository;
        $this->configService = (array) ($configService->get('MailMSmartInboxConnector.config.statusmapping') ?? []);
    }

    public function getValueBy(?StateMachineStateEntity $state): string
    {
        if ($state === null) {
            return self::$defaultState;
        }

        $key = $this->createStateMappingKey($state);

        return $this->configService[$key] ?? self::$defaultState;
    }

    private function createStateMappingKey(StateMachineStateEntity $state): string
    {
        $this->loadStateMachine($state);

        $stateMachine = $state->getStateMachine();
        $stateMachineName = '';

        if ($stateMachine !== null) {
            $stateMachineName = $stateMachine->getTechnicalName();
        }

        return $stateMachineName . '-' . $state->getTechnicalName();
    }

    private function loadStateMachine(StateMachineStateEntity $state): void
    {
        if ($state->getStateMachine() === null) {
            $result = $this->repository->search(new Criteria([
                $state->getStateMachineId()
            ]), Context::createDefaultContext());

            if (count($result) === 1) {
                $state->setStateMachine($result->first());
            }
        }
    }
}
