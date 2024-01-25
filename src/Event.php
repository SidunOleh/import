<?php

namespace Import;

class Event
{
    public static function send(array $data, string|null $event = null): void 
    {
        ob_end_clean();
    
        if ($event) {
            echo "event: {$event}" . PHP_EOL;
        }

        echo 'data: ' . json_encode($data) . PHP_EOL;
        echo PHP_EOL;
        
        ob_flush();
        flush();
    }
}