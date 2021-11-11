<?php

namespace App\Service;

/**
 * Class CloudinaryAdapter
 * @package App\Service
 */
class CloudinaryAdapter implements ImageStorageInterface
{
    // defaults
    const
        DEFAULT_QUALITY = 85,
        DEFAULT_SECURED = true,
        DEFAULT_FORMAT = 'jpg';

    // setting
    const
        SETTING_QUALITY = 'quality',
        SETTING_SECURED = 'secured';
    
    /** @var \App\Service\Cloudinary */
    private $cloudinary;

    /** @var int */
    private $quality = self::DEFAULT_QUALITY;

    /** @var bool */
    private $secured = self::DEFAULT_SECURED;

    /**
     * @param \App\Service\Cloudinary $cloudinary
     * @param array $setting
     */
    public function __construct(\App\Service\Cloudinary $cloudinary, array $setting = [])
    {
        if (isset($setting[self::SETTING_QUALITY])) {
            if (($setting[self::SETTING_QUALITY] < 1) || ($setting[self::SETTING_QUALITY] > 100))
                throw new \InvalidArgumentException('Invalid quality setting has been entered.');

            $this->quality = $setting[self::SETTING_QUALITY];
        }
        if (isset($setting[self::SETTING_SECURED])) {
            $this->secured = (bool)$setting[self::SETTING_SECURED];
        }
        
        $this->cloudinary = $cloudinary;
    }

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
    public function upload(string $image, string $name = null): array
    {
        if (preg_match('/data:image\/([a-zA-Z]*);base64,([^\"]*)/', $image) !== 1)
            throw new \InvalidArgumentException('Invalid image has been entered.');

        return $this->cloudinary->uploadFromString($image, [
            'quality' => $this->quality,
            'secure' => $this->secured,
            'format' => self::DEFAULT_FORMAT,
        ]);
    }

    /**
     * @param string $id Identification of the image
     * @return string Returns absolute url to the image
     */
    public function get(string $id): string
    {
        return $this->cloudinary->get($id);
    }

    /**
     * @param string $id Identification of the image
     * @param int $width
     * @param int $height
     * @param array $options
     * @return string Returns absolute url to the image
     */
    public function resize(string $id, int $width, int $height, array $options = []): string
    {
        if (empty($id) || ($width < 1) || ($height < 1))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        return $this->cloudinary->resize($id, $width, $height, $this->parseOptions($options));
    }

    /**
     * @param string $id Identification of the image
     * @param int $width
     * @param int $height
     * @param int $x
     * @param int $y
     * @param array $options
     * @return string Returns absolute url to the image
     */
    public function crop(string $id, int $width, int $height, int $x, int $y, array $options = []): string
    {
        if (empty($id) || ($width < 1) || ($height < 1) || ($x < 0) || ($y < 0))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        return $this->cloudinary->crop($id, $width, $height, $x, $y, $this->parseOptions($options));
    }

    /**
     * @param string $id Identification of the image
     */
    public function delete(string $id): void
    {
        $this->cloudinary->delete($id);
    }

    // private ---------------------------------------------------------------------------------------------------------

    /**
     * @param array $options
     * @return array
     */
    private function parseOptions(array $options): array
    {
        $optionsData = [];
        // secure
        $optionsData['secure'] = $this->secured;

        if (isset($options['gravity']))
            $optionsData['gravity'] = $options['gravity'];
        if (isset($options['crop']))
            $optionsData['crop'] = $options['crop'];

        return $optionsData;
    }

}
