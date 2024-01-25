<?php

namespace Import;

class Event
{
    public static function send(array $data): void 
    {
        ob_end_clean();
    
        echo json_encode($data) . PHP_EOL;
        echo PHP_EOL;
        
        ob_flush();
        flush();
    }
}