<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Class Setting
 * @package App\Service
 */
class Setting
{
    // flags
    const
        FLAG_LANGUAGE = 1,
        FLAG_SECRET = 2;

    /** @var array */
    private $setting = [];

    /** @var EntityManagerInterface */
    private $em;

    /** @var string */
    private $secret = null;

    /** @var string */
    private $language = null;

    /** @var bool */
    private $strictMode = false;

    /**
     * @param EntityManagerInterface $em
     * @param null|string $secret
     */
    public function __construct(EntityManagerInterface $em, $secret = null, $language = null, $strictMode = false)
    {
        if (isset($secret) && (strlen($secret) < 32))
            throw new \InvalidArgumentException('The secret must be at least 32 characters long.');

        $this->em = $em;
        $this->secret = $secret;
        $this->language = $language;
        $this->strictMode = (bool)$strictMode;

        /** @var \App\Entity\Setting[] $data */
        $data = $this->em->getRepository(\App\Entity\Setting::class)
            ->findAll();

        if ($data) {
            foreach ($data as $dataItem) {
                $this->setting[$dataItem->getKey()] = [
                    'value' => $dataItem->getValue(),
                    'type' => $dataItem->getType(),
                ];
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function exists($name)
    {
        if (empty($name))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $name = (string)$name;
        $nameWithLanguage = $name.'@'.$this->language;

        return (isset($this->setting[$name]) || isset($this->setting[$nameWithLanguage]));
    }

    /**
     * @param string $key
     * @param string $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (empty($key))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $key = (string)$key;
        $nameWithLanguage = $key.'@'.$this->language;

        if (isset($this->setting[$key])) {
            return $this->convertToType($key, $this->setting[$key]['value'], $this->setting[$key]['type']);
        } elseif (isset($this->setting[$nameWithLanguage])) {
            return $this->convertToType($key, $this->setting[$nameWithLanguage]['value'], $this->setting[$nameWithLanguage]['type']);
        } elseif ($this->strictMode) {
            throw new \InvalidArgumentException("Can not find $key key.");
        } else {
            return $default;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $flag
     */
    public function set($key, $value, $flag = 0)
    {
        if (empty($key))
            throw new \InvalidArgumentException('Invalid argument has been entered.');

        $key = (string)$key;

        if ($flag & self::FLAG_LANGUAGE)
            $key .= '@'.$this->language;

        if ($flag & self::FLAG_SECRET) {
            $type = 'secret';
            $value = $this->encrypt($key, $value);
        } else {
            $type = $this->getType($value);

            if (($value === null) || (($type == 'string') && strlen($value) == 0)) {
                $value = null;
            } elseif (is_scalar($value)) {
                $value = (string)$value;
            } elseif (is_array($value)) {
                $value = json_encode($value);
            } else {
                $value = $this->objectToString($value);
            }
        }

        // set locally
        $this->setting[$key] = [
            'value' => $value,
            'type' => $type,
        ];

        /** @var \App\Entity\Setting $data */
        $item = $this->em->getRepository(\App\Entity\Setting::class)
            ->find($key);

        if ($item) {
            $item->setValue($value);
            $item->setType($type);
        } else {
            $item = new \App\Entity\Setting();
            $item->setKey($key);
            $item->setValue($value);
            $item->setType($type);
        }

        $this->em->persist($item);
        $this->em->flush();
    }

    // private methods -------------------------------------------------------------------------------------------------

    /**
     * @param string $key
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function convertToType($key, $value, $type)
    {
        if (($value === null) || (strlen($value) == 0))
            return null;

        switch ($type) {
            case 'string': return $value; break;
            case 'int': return (int)$value; break;
            case 'float': return (float)$value; break;
            case 'bool': return (bool)$value; break;
            case 'object': return $this->stringToObject($value); break;
            case 'array': return json_decode($value, true); break;
            case 'secret': return $this->decrypt($key, $value); break;
        }
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function getType($value)
    {
        if (is_string($value))
            return 'string';
        if (is_int($value))
            return 'int';
        if (is_float($value))
            return 'float';
        if (is_bool($value))
            return 'bool';
        if (is_array($value))
            return 'array';
        if (is_object($value))
            return 'object';

        // use string as default for null values
        return 'string';
    }

    /**
     * @param mixed $obj
     * @return string
     */
    private function objectToString($obj)
    {
        return base64_encode(serialize($obj));
    }

    /**
     * @param string $str
     * @return mixed
     */
    private function stringToObject($str)
    {
        return unserialize(base64_decode($str));
    }

    /**
     * @param string $key
     * @param string $value
     * @return string
     */
    private function encrypt($key, $value)
    {
        if (($value === null) || (strlen($value) == 0))
            return null;

        return openssl_encrypt($value, 'aes-256-cbc', $this->secret, 0, substr(md5($key), 0, 16));
    }

    /**
     * @param string $key
     * @param string $value
     * @return string
     */
    private function decrypt($key, $value)
    {
        return openssl_decrypt($value, 'aes-256-cbc', $this->secret, 0, substr(md5($key), 0, 16));
    }

}