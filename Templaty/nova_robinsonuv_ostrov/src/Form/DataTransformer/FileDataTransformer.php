<?php

namespace App\Form\DataTransformer;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class FileDataTransformer
 * @package App\Form\ModelTransformer
 */
class FileDataTransformer implements DataTransformerInterface
{
    /**
     * @param mixed $data
     * @return null
     */
    public function transform($data)
    {
        return null;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    public function reverseTransform($data)
    {
        return $data;
    }
}
