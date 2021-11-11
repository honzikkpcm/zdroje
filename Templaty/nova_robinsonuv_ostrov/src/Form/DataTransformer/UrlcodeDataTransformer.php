<?php

namespace App\Form\DataTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class UrlcodeModelTransformer
 * @package App\Form\ModelTransformer
 */
class UrlcodeDataTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $data
     * @return string
     */
    public function transform($data): string
    {
        return (string) $data;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function reverseTransform($data): string
    {
        return (string) $data;
    }
}
