<?php

namespace EinsUndEins\PluginTransactionMailExtender\Mapping;

interface Mapping
{
    public function getValueBy(string $key): string;
}
