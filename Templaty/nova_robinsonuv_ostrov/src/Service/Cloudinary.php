<?php

namespace App\Service;

/**
 * Class Cloudinary
 * @package App\Service
 */
class Cloudinary
{
    /** @var string */
    private $cloudName;
    
    /** @var string */
    private $apiKey;
    
    /** @var string */
    private $apiSecret;

    /**
     * @param string|null $apiKey
     * @param string|null $apiSecret
     * @param string|null $cloudName
     * @param string|null $url Pattern cloudinary://key:secret@name
     */
    public function __construct(string $apiKey = null, string $apiSecret = null, string $cloudName = null, string $url = null)
    {
        if ((empty($apiKey) || empty($apiSecret) || empty($cloudName)) && (empty($url)))
            throw new \InvalidArgumentException('Invalid argument has been entered.');
        
        if (empty($url)) {
            $this->apiKey = $apiKey;
            $this->apiSecret = $apiSecret;
            $this->cloudName = $cloudName;
        } else {
            $setting = preg_split('/(\W+)/', $url);

            if (count($setting) !== 4)
                throw new \InvalidArgumentException('Invalid url pattern has been entered.');

            $this->apiKey = $setting[1];
            $this->apiSecret = $setting[2];
            $this->cloudName = $setting[3];
        }

        \Cloudinary::config([
            'cloud_name' => $this->cloudName,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
        ]);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function parseId(string $url): string
    {
        if (empty($url))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $id = substr($url, strrpos($url, '/') + 1);

        if (($pos = strrpos($id, '.')) !== false) {
            $id = substr($id, 0, $pos);
        }

        return $id;
    }
    
    /**
     * @param string $image Base64 encoded image included image header
     * @param array $options https://cloudinary.com/documentation/image_upload_api_reference
     * @return array
     */
    public function uploadFromString(string $image, array $options = []): array
    {
        if (preg_match('/data:image\/([a-zA-Z]*);base64,([^\"]*)/', $image) !== 1)
            throw new \InvalidArgumentException('Invalid image has been entered.');

        return $this->formatResponse(\Cloudinary\Uploader::upload($image, $options));
    }

    /**
     * @param string $file
     * @param array $options https://cloudinary.com/documentation/image_upload_api_reference
     * @return array
     */
    public function uploadFromFilePath(string $file, array $options = []): array
    {
        if (!is_file($file))
            throw new \InvalidArgumentException('The file does not exist.');

        return $this->formatResponse(\Cloudinary\Uploader::upload($file, $options));
    }

    /**
     * @param string $url
     * @param array $options https://cloudinary.com/documentation/image_upload_api_reference
     * @return array
     */
    public function uploadFromUrl(string $url, array $options = []): array
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false)
            throw new \InvalidArgumentException('Invalid url has been entered.');

        return $this->formatResponse(\Cloudinary\Uploader::upload($url, $options));
    }

    /**
     * @param string $id
     * @param array $options https://cloudinary.com/documentation/image_upload_api_reference
     * @return string Absolute url to the file
     */
    public function get(string $id, array $options = []): string
    {
        if (empty($id))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $optionsData = $options;
        return \Cloudinary::cloudinary_url($id, $optionsData);
    }

    /**
     * @param string $id
     * @param int $width
     * @param int $height
     * @param array $options https://cloudinary.com/documentation/image_upload_api_reference
     * @return string Absolute url to the file
     */
    public function resize(string $id, int $width, int $height, array $options = []): string
    {
        if (empty($id) || ($width < 1) || ($height < 1))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $optionsData = $options;
        $optionsData['width'] = $width;
        $optionsData['height'] = $width;

        return \Cloudinary::cloudinary_url($id, $optionsData);
    }

    /**
     * @param string $id
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @param array $options https://cloudinary.com/documentation/image_upload_api_reference
     * @return string Absolute url to the file
     */
    public function crop(string $id, int $width, int $height, int $x, int $y, array $options = []): string
    {
        if (empty($id) || ($width < 1) || ($height < 1) || ($x < 0) || ($y < 0))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $optionsData = $options;
        $optionsData['width'] = $width;
        $optionsData['height'] = $width;
        $optionsData['x'] = $x;
        $optionsData['y'] = $y;

        return \Cloudinary::cloudinary_url($id, $optionsData);
    }

    /**
     * @param string $id
     */
    public function delete(string $id)
    {
        \Cloudinary\Uploader::destroy($id);
    }
    
    // private ---------------------------------------------------------------------------------------------------------

    /**
     * @param mixed $response
     * @return array
     */
    private function formatResponse($response): array
    {
        return [
            'id' => isset($response['public_id']) ? $response['public_id'] : null,
            'width' => isset($response['width']) ? $response['width'] : null,
            'height' => isset($response['height']) ? $response['height'] : null,
            'format' => isset($response['format']) ? $response['format'] : null,
            'size' => isset($response['bytes']) ? $response['bytes'] : null,
            'url' => isset($response['secure_url']) ? $response['secure_url'] : null,
        ];
    }
    
}
