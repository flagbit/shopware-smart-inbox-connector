<?php

namespace EinsUndEins\PluginTransactionMailExtender\Mapping;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class StateMapping implements Mapping
{
    /**
     * @var SystemConfigService
     */
    private $configService;

    public function __construct(SystemConfigService $configService)
    {
        $this->configService = $configService->get('TransactionMailExtender.config.statusmapping') ?? [];
    }

    public function getValueBy(string $key): string
    {
        return $this->configService[$key] ?? 'OrderProcessing';
    }
}
