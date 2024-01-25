<?php

namespace Import\Parsers;

defined('ABSPATH') or die;

interface IParser
{
    public function parse(string $url): array;
}