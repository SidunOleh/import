<?php

namespace Import\Http\Middleware;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Exception\RequestException;
use Import\Http\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use TwoCaptcha\TwoCaptcha;

defined('ABSPATH') or die;

class RestaurantGuruBypassCaptcha
{
    private Client $httpClient;

    private TwoCaptcha $captchaSolver;

    public function __construct(
        Client $httpClient,
        TwoCaptcha $captchaSolver
    )
    {
        $this->httpClient = $httpClient;
        $this->captchaSolver = $captchaSolver;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options = []) use ($handler) {
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) use($request) {
                $dom = new DOMDocument;
                @$dom->loadHTML($response->getBody()->getContents());
                $xpath = new DOMXPath($dom);
                $response->getBody()->rewind();
                if ($this->hasCaptcha($xpath)) {
                    if ($this->bypassCaptcha($xpath, $request->getUri())) {
                        $response = $this->httpClient->request(
                            $request->getMethod(),
                            $request->getUri()
                        );
                    } else {
                        throw new RequestException(
                            'Can not bypass captcha on ' . $request->getUri(), 
                            $request
                        );
                    }
                }
             
                return $response;
            });
        };
    }

    private function hasCaptcha(DOMXPath $xpath): bool
    {
        return (bool) $xpath->query('.//div[@id="captcha"]')->count();
    }

    private function bypassCaptcha(DOMXPath $xpath, string $url): bool
    {
        $siteKey = $xpath->query('..//div[@class="g-recaptcha captcha"]')[0]
            ?->getAttribute('data-sitekey');
        $tr = $xpath->query('..//input[@name="tr"]')[0]
            ?->getAttribute('value');

        $captchaSolution = $this->captchaSolver->recaptcha([
            'sitekey' => $siteKey,
            'url' => $url,
        ]);
    
        $response = $this->httpClient->request('POST', 'https://ru.restaurantguru.com/ajax/ab_check', [
            'form_params' => [
                'ab_g_response' => $captchaSolution->code, 
                'tr' => $tr,
            ],
            'headers' => [
                'Referer' => $url,
            ],
        ]);
        $content = json_decode($response->getBody()->getContents(), true);

        return $content['success'];
    }
}