<?php

namespace Import\Savers;

use Import\Http\Client;

defined('ABSPATH') or die;

abstract class BaseSaver implements ISaver
{
    protected $httpClient;

    public function __construct(Client $httpClient)
    {
        $this->httpClient = $httpClient;
    }
}