<?php

namespace App\Form\DataTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class DateDataTransformer
 * @package App\Form\ModelTransformer
 */
class DateDataTransformer implements DataTransformerInterface
{
    // format
    const DATE_FORMAT = 'd.m.Y';

    /**
     * @param string $data
     * @return string
     */
    public function transform($data): string
    {
        if (!empty($data)) {
            $date = new \DateTime($data);
            return $date->format(self::DATE_FORMAT);
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
