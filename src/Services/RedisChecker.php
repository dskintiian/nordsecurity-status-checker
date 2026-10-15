<?php

namespace Nordsec\StatusChecker\Services;

use Exception;
use Predis\Client;

class RedisChecker implements StatusCheckerInterface
{
    private $name;
    private $configuration;
    private $client;
    private $critical = true;

    public function __construct($name, $configuration = [], $client = null)
    {
        $this->name = $name;
        $this->configuration = $configuration;
        $this->client = $client;
    }

    public function setCritical(bool $critical): StatusCheckerInterface
    {
        $this->critical = $critical;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function needsOutput(): bool
    {
        return true;
    }

    public function isCritical(): bool
    {
        return $this->critical;
    }

    public function checkStatus(): string
    {
        try {
            switch (true) {
                case $this->client instanceof \Redis:
                    $this->client->randomKey();
                    break;
                case $this->client instanceof Client:
                    $this->client->randomkey();
                    break;
                default:
                    $cacheClient = new Client(
                        $this->configuration['parameters'],
                        $this->configuration['options'],
                    );
                    $cacheClient->connect();
                    $cacheClient->randomkey();
            }
        } catch (Exception $exception) {
            return static::STATUS_FAIL;
        }

        return static::STATUS_OK;
    }
}
