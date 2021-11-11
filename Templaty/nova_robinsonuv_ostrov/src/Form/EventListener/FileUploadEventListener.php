<?php

namespace App\Form\EventListener;

use App\Service\ImageStorageInterface;
use App\Utils\Format;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class FileUploadEventListener
 * @package App\Form\EventListener
 */
class FileUploadEventListener implements EventSubscriberInterface
{

    /** @var ImageStorageInterface */
    private $imageStorage;

    /**
     * @param ImageStorageInterface $imageStorage
     */
    public function __construct(ImageStorageInterface $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => 'onPostSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        // get file properties
        $ref = new \ReflectionClass($data);
        $properties = $ref->getProperties();
        $fileProperties = [];

        foreach ($properties as $property) {
            if (strpos($property->getDocComment(), '@Assert\File') !== false) {
                $name = ucfirst($property->getName());

                if ($ref->hasMethod("get$name") && $ref->hasMethod("set$name")) {
                    $fileProperties[] = $name;
                }
            }
        }

        // is there anything for upload?
        if (count($fileProperties) === 0) {
            return;
        }

        // process uploads
        foreach ($fileProperties as $fileProperty) {
            $getter = "get$fileProperty";
            $setter = "set$fileProperty";
            /** @var UploadedFile $file */
            $file = $data->$getter();

            if (!empty($file)) {
                $response = $this->imageStorage->upload(Format::convertUploadedFileToBase64($file));

                if (isset($response['id'])) {
                    $data->$setter($response['id']);
                }
            }
        }
    }
}
