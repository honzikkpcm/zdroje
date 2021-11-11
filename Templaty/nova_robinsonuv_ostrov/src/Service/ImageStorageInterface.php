<?php

namespace App\Service;

/**
 * Class ImageStorageInterface
 * @package App\Service
 */
interface ImageStorageInterface
{
    /**
     * @param string $image Base64 encoded image
     * @param string|null $name
     * @return array
     * <code>
     * return [
     *     'id' => 'fs45f65s4ga4ga1fsdafsd45f4sdfa5sd4f5s',
     *     'name' => 'sample.jpg',
     *     'contentType' => 'image/jpeg',
     *     'width' => 50,
     *     'height' => 150,
     *     'url' => 'https://cloud-red.cloudinary/fs45f65s4ga4ga1fsdafsd45f4sdfa5sd4f5s'
     * ];
     * </code>
     */
    public function upload(string $image, string $name = null): array;

    /**
     * @param string $id Identification of the image
     * @return string Returns absolute url to the image
     */
    public function get(string $id): string;

    /**
     * @param string $id Identification of the image
     * @param int $width
     * @param int $height
     * @param array $options
     * @return string Returns absolute url to the image
     */
    public function resize(string $id, int $width, int $height, array $options = []): string;
    
    /**
     * @param string $id Identification of the image
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @param array $options
     * @return string Returns absolute url to the image
     */
    public function crop(string $id, int $width, int $height, int $x, int $y, array $options = []): string;
    
    /**
     * @param string $id Identification of the image
     */
    public function delete(string $id): void;
}
