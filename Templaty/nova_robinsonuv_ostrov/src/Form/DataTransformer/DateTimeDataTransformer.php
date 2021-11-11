<?php

namespace App\Form\DataTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class DateTimeDataTransformer
 * @package App\Form\ModelTransformer
 */
class DateTimeDataTransformer implements DataTransformerInterface
{
    // format
    const DATETIME_FORMAT = 'd.m.Y H:i';

    /**
     * @param string $data
     * @return string
     */
    public function transform($data): string
    {
        if (!empty($data)) {
            $date = new \DateTime($data);
            return $date->format(self::DATETIME_FORMAT);
        } elseif (empty($data)) {
            return ''; // empty string
        } else {
            return $data;
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public function reverseTransform($data): string
    {
        return $data;
    }
}
