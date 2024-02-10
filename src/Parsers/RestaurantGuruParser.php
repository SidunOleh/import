<?php

namespace Import\Parsers;

use DOMDocument;
use DOMXPath;
use Exception;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

defined('ABSPATH') or die;

class RestaurantGuruParser extends BaseParser
{
    public function parse(string $url): array
    {
        $response = $this->httpClient->makeRequest('GET', $url);
            
        $doc = new DOMDocument();
        @$doc->loadHTML($response->getBody()->getContents());
        $xpath = new DOMXPath($doc);
        
        $info = [];
        $info['source'] = $url;
        $info['name'] = $this->name($xpath);
        $info['description'] = $this->description($xpath);
        $info['thumbnail'] = $this->thumbnail($xpath);
        $info['address'] = $this->address($xpath);
        $info['geo'] = $this->geo($xpath);
        $info['openingHours'] = $this->openingHours($xpath);
        $info['cuisines'] = $this->cuisines($xpath);
        $info['telephone'] = $this->telephone($xpath);
        $info['url'] = $this->url($xpath);
        $info['features'] = $this->features($xpath);
        $info['social_media'] = $this->socialMedia($xpath);
        $info['photos'] = $this->photos($xpath);
        $info['reviews'] = $this->reviews($xpath, $url);

        return $info;
    }

    private function name(DOMXPath $xpath): string
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        return $data['name'] ?? '';
    }

    private function description(DOMXPath $xpath): string
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        return $data['review']['description'] ?? '';
    }

    private function thumbnail(DOMXPath $xpath): string
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        return $data['image'] ?? '';
    }

    private function address(DOMXPath $xpath): array
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        $address['addressCountry'] = $data['address']['addressCountry'] ?? '';
        $address['addressLocality'] = $data['address']['addressLocality'] ?? '';
        $address['addressRegion'] = $data['address']['addressRegion'] ?? '';
        $address['streetAddress'] = $data['address']['streetAddress'] ?? '';

        return $address;
    }

    private function geo(DOMXPath $xpath): array
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        $geo['latitude'] = $data['geo']['latitude'] ?? '';
        $geo['longitude'] = $data['geo']['longitude'] ?? '';

        return $geo;
    }

    private function openingHours(DOMXPath $xpath): array
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        return $data['openingHours'] ?? [];
    }

    private function cuisines(DOMXPath $xpath): array
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        return $data['servesCuisine'] ?? [];
    }

    private function telephone(DOMXPath $xpath): string
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        return $data['telephone'] ?? [];
    }

    private function url(DOMXPath $xpath): string
    {
        $data = json_decode($xpath->query('.//script[@type="application/ld+json"]')[0]?->textContent, true);

        return $data['url'] ?? [];
    }

    private function features(DOMXPath $xpath): array
    {
        $features = [];

        $nodes = $xpath->query('.//div[@class="features_block"]/div[@class="overflow"]/span');
        foreach ($nodes as $node) {
            $features[] = trim($node->textContent);
        }

        return $features;
    }

    private function socialMedia(DOMXPath $xpath): array
    {
        $socialMedia = [];

        $socialMedia['instagram'] = trim($xpath->query('.//div[@class="instagram"]/a')[0]?->textContent);

        return $socialMedia;
    }

    private function photos(DOMXPath $xpath): array
    {
        $photos = [];

        $imagesCount = $this->config['images_count'] ?? -1;
        if ($imagesCount == 0) {
            return $photos;
        }

        $scripts = $xpath->query('.//script');
        foreach ($scripts as $script) {
            if (preg_match('/var json_arr.*?"photos":(\[.*?}\])/', $script->textContent, $matches)) {
                $photos = json_decode($matches[1], true) ?? [];
            }
        }

        if ($imagesCount != -1) {
            $photos = array_slice($photos, 0, $imagesCount);
        }

        return $photos;
    }

    private function reviews(DOMXPath $xpath, string $url): array
    {
        $reviews = [];

        $reviewsCount = $this->config['reviews_count'] ?? -1;
        if ($reviewsCount == 0) {
            return $reviews;
        } elseif ($reviewsCount == -1) {
            $reviewsCount = trim($xpath->query('.//span[@class="cc"]')[0]?->textContent);
        } else {
            $reviewsCount = $this->config['reviews_count'];
        }

        $requests = function (int $reviewsCount) use($url) {
            $pagesCount = ceil($reviewsCount / 30);
            for ($page=1; $page <= $pagesCount; $page++) { 
                yield $page => new Request('GET', "{$url}/reviews/{$page}", [
                    'X-Requested-With' => 'XMLHttpRequest',
                ]);
            }
        };

        $pool = new Pool($this->httpClient, $requests($reviewsCount), [
            'concurrency' => 100,
            'fulfilled' => function (Response $response) use(&$reviews) {
                $data = json_decode($response->getBody()->getContents(), true);

                if (! $html = $data['html']) {
                    return;
                }
    
                $doc = new DOMDocument();
                @$doc->loadHTML($html);
                $xpath = new DOMXPath($doc);
    
                $review = [];
                $reviewsNodes = $xpath->query('.//div[@class="o_review"]');
                foreach ($reviewsNodes as $reviewsNode) {
                    $review['author_name'] = 
                        $xpath->query('.//a[@class="user_info__name"]', $reviewsNode)[0]?->textContent;
                    if ($img = $xpath->query('.//img', $reviewsNode)[0]) {
                        $review['author_img'] = $img->getAttribute('data-src');
                    } else {
                        $review['author_img'] = '';
                    }
                    $review['text'] = 
                        $xpath->query('.//span[@class="text_full"]', $reviewsNode)[0]?->textContent;
                    $review['stars'] = 
                        $reviewsNode->getAttribute('data-score');
                    $reviews[] = $review;
                }
            },
            'rejected' => function (Exception $e, $page) use($url) {
                error_log(json_encode([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'url' => "{$url}/reviews/{$page}",
                    'time' => date('Y-m-d H:i:s'),
                ]) . PHP_EOL, 3, IMPORT_ROOT . '/logs/error_log');
            },
        ]);

        ($pool->promise())->wait();

        $reviews = array_slice($reviews, 0, $reviewsCount);

        return $reviews;
    }
}
