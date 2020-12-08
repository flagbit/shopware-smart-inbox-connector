<?php

namespace EinsUndEins\PluginTransactionMailExtender\Mapping;

class StateMapping implements Mapping
{
    /**
     * @var array<string, string>
     */
    private $map;

    public function __construct()
    {
        // Temporary solution
        $map = [
            'OrderCancelled' => 'cancelled',
            'OrderDelivered' => 'completed',
            'OrderInTransit' => 'shipped',
            'OrderPaymentDue' => 'open',
            'OrderPickupAvailable' => '',
            'OrderProblem' => '',
            'OrderProcessing' => 'in_progress',
            'OrderReturned' => 'returned',
        ];

        $this->map = array_flip($map);
    }

    public function getValueBy(string $key): string
    {
        if (isset($this->map[$key])) {
            return $this->map[$key];
        }

        return '';
    }
}
