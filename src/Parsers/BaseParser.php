<?php

namespace Import\Parsers;

use Import\Http\Client;

defined('ABSPATH') or die;

abstract class BaseParser implements IParser
{
    protected $httpClient;

    protected array $config;

    public function __construct(Client $httpClient, array $config = [])
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
    }
}