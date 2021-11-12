<?php

namespace EinsUndEins\SmartInboxConnector\StateMapping;

use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

interface Mapper
{
    public function getValueBy(?StateMachineStateEntity $state): string;
}
