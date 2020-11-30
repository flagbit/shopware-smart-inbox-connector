<?php

namespace EinsUndEins\PluginTransactionMailExtender\Tests\Mapping;

use EinsUndEins\PluginTransactionMailExtender\Mapping\StatusMapping;
use PHPUnit\Framework\TestCase;

class StatusMappingTest extends TestCase
{
    /**
     * @dataProvider provideMappings
     */
    public function testGetValueBy(string $mappingKey, string $expected): void
    {
        $mapping = new StatusMapping();

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
