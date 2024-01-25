<?php

namespace Import\Savers;

defined('ABSPATH') or die;

interface ISaver
{
    public function save(array $data): int|bool;
}