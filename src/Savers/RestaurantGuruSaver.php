<?php

namespace Import\Savers;

use Exception;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

defined('ABSPATH') or die;

class RestaurantGuruSaver extends BaseSaver
{
    public function save(array $data): int
    {
        $postId = wp_insert_post([
            'ID' => $this->restaurantIdBySource($data['source']),
            'post_title' => $data['name'],
            'post_content' => $data['description'],
            'post_type' => 'restaurant',
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ]);

        if (! $postId) {
            throw new Exception("Can not create post for {$data['source']}");
        }
    
        carbon_set_post_meta($postId, 'address_country', $data['address']['addressCountry']);
        carbon_set_post_meta($postId, 'address_locality', $data['address']['addressLocality']);
        carbon_set_post_meta($postId, 'address_region', $data['address']['addressRegion']);
        carbon_set_post_meta($postId, 'address_street', $data['address']['streetAddress']);
        carbon_set_post_meta($postId, 'address_full', $data['address']['fullAddress']);
    
        carbon_set_post_meta($postId, 'geo_latitude', $data['geo']['latitude']);
        carbon_set_post_meta($postId, 'geo_longitude', $data['geo']['longitude']);
    
        foreach ($data['openingHours'] as $i => $openingHour) {
            carbon_set_post_meta($postId, "opening_hours[{$i}]/day", explode(' ', $openingHour)[0]);
            carbon_set_post_meta($postId, "opening_hours[{$i}]/hours", explode(' ', $openingHour)[1]);
        }
    
        carbon_set_post_meta($postId, 'contact_telephone', $data['telephone']);
        carbon_set_post_meta($postId, 'contact_website', $data['url']);
        carbon_set_post_meta($postId, 'contact_instagram', $data['social_media']['instagram']);
    
        carbon_set_post_meta($postId, 'others_source', $data['source']);
    
        $photoIds = $this->uploadPhotos($data['photos']);
        foreach ($photoIds as $i => $photoId) {
            update_post_meta($postId, "_gallery|||{$i}|value", $photoId);
        }
    
        if (! empty($data['thumbnail'])) {
            $thumbnail = $this->uploadPhotos([['src' => $data['thumbnail'],]]);
            update_post_meta($postId, '_thumbnail_id', $thumbnail[0]);
        } else {
            foreach ($photoIds as $photoId) {
                if (wp_attachment_is_image($photoId)) {
                    update_post_meta($postId, '_thumbnail_id', $photoId);
                    break;
                }
            }
        }
    
        $this->setСuisines($data['cuisines'], $postId);
        $this->setFeatures($data['features'], $postId);
        $this->setLocation($data['address'], $postId);
        $this->setDishes($data['dishes'], $postId);

        $this->deleteReviews($postId);
        $this->insertReviews($data['reviews'], $postId);

        return $postId;
    }

    private function restaurantIdBySource(string $source): int 
    {
        global $wpdb;
    
        $source = $wpdb->_real_escape($source);
        $postId = $wpdb->get_var("SELECT `post_id` 
            FROM `{$wpdb->prefix}postmeta`
            WHERE `meta_key` = '_others_source'
            AND `meta_value` = '{$source}'");

        return $postId ? $postId : 0;
    }

    private function setСuisines(array $cuisines, int $postId): array 
    {
        $cuisineIds = [];
        foreach ($cuisines as $cuisine) {
            $term = get_term_by('name', $cuisine, 'restaurant_cuisine', ARRAY_A);
            if (! $term ) {
                $term = wp_insert_term($cuisine, 'restaurant_cuisine');
            } 
    
            $cuisineIds[] = $term['term_id'];
        }

        wp_set_post_terms($postId, $cuisineIds, 'restaurant_cuisine');
    
        return $cuisineIds;
    }
    
    private function setFeatures(array $features, int $postId): array 
    {
        $featureIds = [];
        foreach ($features as $feature) {
            $term = get_term_by('name', $feature, 'restaurant_feature', ARRAY_A);
            if (! $term ) {
                $term = wp_insert_term($feature, 'restaurant_feature');
            } 
    
            $featureIds[] = $term['term_id'];
        }

        wp_set_post_terms($postId, $featureIds, 'restaurant_feature');
    
        return $featureIds;
    }
    
    private function setLocation(array $location, int $postId): array 
    {
        $locationIds = [];

        $region = $location['addressRegion'] ?: $location['addressLocality'];
        $city = $location['addressLocality'];

        if (! $region or ! $city) {
            return $locationIds;
        }
    
        $regionTerm = get_term_by('name', $region, 'restaurant_location', ARRAY_A);
        if (! $regionTerm) {
            $regionTerm = wp_insert_term($region, 'restaurant_location');
        } 
        $locationIds[] = $regionTerm['term_id'];
    
        $cityTerm = get_term_by('name', $city, 'restaurant_location', ARRAY_A);
        if (! $cityTerm) {
            $cityTerm = wp_insert_term($city, 'restaurant_location', [
                'parent' => $regionTerm['term_id'],
            ]);
        } 
        $locationIds[] = $cityTerm['term_id'];

        wp_set_post_terms($postId, $locationIds, 'restaurant_location');
    
        return $locationIds;
    }

    private function setDishes(array $dishes, int $postId): array 
    {
        $dishIds = [];
        foreach ($dishes as $dish) {
            $term = get_term_by('name', $dish, 'restaurant_dish', ARRAY_A);
            if (! $term ) {
                $term = wp_insert_term($dish, 'restaurant_dish');
            } 
    
            $dishIds[] = $term['term_id'];
        }

        wp_set_post_terms($postId, $dishIds, 'restaurant_dish');
    
        return $dishIds;
    }
    
    private function deleteReviews(int $postId) 
    {
        global $wpdb;
        $wpdb->delete($wpdb->comments, ['comment_post_ID' => $postId,]);
    }
    
    private function insertReviews(array $reviews, int $postId): array 
    {
        $reviewIds = [];
        foreach ($reviews as $review) {
            $reviewId = wp_insert_comment([
                'comment_author' => $review['author_name'],
                'comment_content' => $review['text'],
                'comment_post_ID' => $postId,
            ]);

            if (! $reviewId) {
                continue;
            }
    
            carbon_set_comment_meta($reviewId, 'stars', $review['stars']);
            carbon_set_comment_meta($reviewId, 'author_img_url', $review['author_img']);
    
            $reviewIds[] = $reviewId;
        }
    
        return $reviewIds;
    }

    private function uploadPhotos(array $photos): array 
    {
        $photoIds = [];

        $requests = function (array $photos) use(&$photoIds) {
            foreach ($photos as $i => $photo) {
                $src = isset($photo['is_video']) ? $photo['srcVideo'] : $photo['src'];
                $name = md5($src) . '.' . end(explode('.', $src));
                if (! $attachmentId = $this->attachmentExists($name)) {
                    yield $i => new Request('GET', $src);
                } else {
                    $photoIds[] = $attachmentId;
                }
            }
        };

        $pool = new Pool($this->httpClient, $requests($photos), [
            'concurrency' => 100,
            'fulfilled' => function (Response $response, $i) use($photos, &$photoIds) {
                $photo = $photos[$i];
                $src = isset($photo['is_video']) ? $photo['srcVideo'] : $photo['src'];
                $name = md5($src) . '.' . end(explode('.', $src));
                $path = wp_upload_dir()['path'] . '/' . $name;

                $file = fopen($path, 'w');
                fwrite($file, $response->getBody()->getContents());
                fclose($file);

                $attachment = [
                    'post_mime_type' => $response->getHeaderLine('Content-Type'),
                    'post_title' => $photo['title'] ?? '',
                    'post_content' => $photo['description'] ?? '',
                ];
                $attachmentId = wp_insert_attachment($attachment, $path);
                
                $attachmentMeta = wp_generate_attachment_metadata($attachmentId, $path);
                wp_update_attachment_metadata($attachmentId, $attachmentMeta);

                if (isset($photo['alt'])) {
                    update_post_meta($attachmentId, '_wp_attachment_image_alt', $photo['alt']);
                }

                $photoIds[] = $attachmentId;
            },
            'rejected' => function (Exception $e, $i) use($photos) {
                $photo = $photos[$i];
                $src = isset($photo['is_video']) ? $photo['srcVideo'] : $photo['src'];
                error_log(json_encode([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'url' => $src,
                    'time' => date('Y-m-d H:i:s'),
                ]) . PHP_EOL, 3, IMPORT_ROOT . '/logs/error_log');
            },
        ]);

        ($pool->promise())->wait();
    
        return $photoIds;
    }
    
    private function attachmentExists(string $name): int|false
    {
        global $wpdb;

        $name = $wpdb->_real_escape($name);
        $attachmentId = $wpdb->get_var("SELECT `ID` 
            FROM `{$wpdb->posts}` 
            WHERE `post_type` = 'attachment'
            AND `post_title` = '{$name}'");
    
        return $attachmentId ? $attachmentId : false;
    }
}