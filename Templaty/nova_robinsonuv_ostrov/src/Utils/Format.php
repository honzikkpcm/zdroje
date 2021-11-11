<?php

namespace App\Utils;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Format
 * @package App\Utils
 */
class Format
{
    /**
     * @param UploadedFile $file
     * @return string
     */
    public static function convertUploadedFileToBase64(UploadedFile $file)
    {
        $mimeType = $file->getMimeType();
        $code = base64_encode(file_get_contents($file->getRealPath()));

        return "data:$mimeType;base64,$code";
    }

}
