<?php

namespace EinsUndEins\PluginTransactionMailExtender\Tests\Mapping;

use EinsUndEins\PluginTransactionMailExtender\Mapping\StateMapping;
use PHPUnit\Framework\TestCase;

class StateMappingTest extends TestCase
{
    /**
     * @dataProvider provideMappings
     */
    public function testGetValueBy(string $mappingKey, string $expected): void
    {
        $mapping = new StateMapping();

        self::assertSame($expected, $mapping->getValueBy($mappingKey));
    }

    public function provideMappings(): array
    {
        return [
            ['cancelled', 'OrderCancelled'],
            ['completed', 'OrderDelivered'],
            ['shipped', 'OrderInTransit'],
            ['open', 'OrderPaymentDue'],
            ['in_progress', 'OrderProcessing'],
            ['returned', 'OrderReturned'],
            ['UnknownMappingKey', ''],
        ];
    }
}
