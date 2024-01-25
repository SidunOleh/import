<?php

namespace Import\Importer;

use Import\Parsers\IParser;
use Import\Savers\ISaver;

defined('ABSPATH') or die;

class Importer
{
    private IParser $parser;

    private ISaver $saver;

    public function __construct(
        IParser $parser,
        ISaver $saver
    )
    {
        $this->parser = $parser;
        $this->saver = $saver;
    }

    public function import(string $url): int
    {
        $data = $this->parser->parse($url);

        $id = $this->saver->save($data);

        return $id;
    }
}