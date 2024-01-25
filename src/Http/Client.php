<?php

namespace Import\Http;

use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Http\Message\ResponseInterface;

defined('ABSPATH') or die;

class Client extends GuzzleHttpClient
{
    const USER_AGENTS = [
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36',
        'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/115.0',
        'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/116.0',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 Edg/115.0.1901.188',
    ];

    public function makeRequest(string $method, string $url, array $options = []): ResponseInterface
    {
        $options['headers']['User-Agent'] = $options['headers']['User-Agent'] ?? self::USER_AGENTS[rand(0, count(self::USER_AGENTS)-1)];
        $options['headers']['Accept-Encoding'] = $options['headers']['Accept-Encoding'] ?? 'gzip, deflate, b';
        $options['headers']['Accept'] = $options['headers']['Accept'] ?? 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8';
        $options['headers']['Referer'] = $options['headers']['Referer'] ?? 'https://www.google.com';

        return $this->request($method, $url, $options);
    }
}