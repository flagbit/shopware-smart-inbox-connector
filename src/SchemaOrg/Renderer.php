<?php

namespace EinsUndEins\PluginTransactionMailExtender\SchemaOrg;

use Shopware\Core\Checkout\Order\OrderEntity;

interface Renderer
{
    public function render(OrderEntity $order): string;
}
