<?php

namespace Import\Importer;

use Exception;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Import\Http\Client;
use Import\Http\Middleware\RestaurantGuruBypassCaptcha;
use Import\Parsers\RestaurantGuruParser;
use Import\Savers\RestaurantGuruSaver;
use TwoCaptcha\TwoCaptcha;

defined('ABSPATH') or die;

class ImporterFactory
{
    public static function create(string $url, array $config = []): Importer
    {
        if (! $components = parse_url($url)) {
            throw new Exception("Malformed url: {$url}");
        }

        if (preg_match('/restaurantguru\.com/', $components['host'])) {
            return self::createRestaurantGuruImporter($config);
        }

        throw new Exception("No importer was found for {$url}");
    }

    private static function createRestaurantGuruImporter(array $config = []): Importer
    {
        $stack = HandlerStack::create();
        $stack->push(Middleware::retry(function ($retries, $request, $response = null) {
            if ($response and $response->getStatusCode() == 200) {
                return false;
            } else {
                return $retries < 3;
            }
        }, function ($retries, $response) {
            return $retries * 1000;
        }));
        $stack->push(new RestaurantGuruBypassCaptcha(
            new Client, 
            new TwoCaptcha($config['twocaptcha_key'] ?? '')
        ));
        $httpClient = new Client([
            'handler' => $stack,
        ]);

        $parser = new RestaurantGuruParser( $httpClient, [
            'reviews_count' => $config['reviews_count'] ?? -1, 
            'images_count' => $config['images_count'] ?? -1,
        ]);
        $saver = new RestaurantGuruSaver($httpClient);

        return new Importer($parser, $saver);
    }
}