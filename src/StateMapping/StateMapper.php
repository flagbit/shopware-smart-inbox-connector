<?php

namespace EinsUndEins\PluginTransactionMailExtender\StateMapping;

use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class StateMapper implements Mapper
{
    /**
     * @var array<string, string>
     */
    private $configService;

    /**
     * @var string
     */
    private static $defaultState = 'OrderProcessing';

    public function __construct(SystemConfigService $configService)
    {
        $this->configService = (array) ($configService->get('TransactionMailExtender.config.statusmapping') ?? []);
    }

    public function getValueBy(?StateMachineStateEntity $state): string
    {
        if ($state === null) {
            return self::$defaultState;
        }

        $key = $this->createStateMappingKey($state);

        return $this->configService[$key] ?? self::$defaultState;
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
