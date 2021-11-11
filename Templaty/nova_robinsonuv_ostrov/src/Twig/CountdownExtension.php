<?php

namespace App\Twig;

use Symfony\Component\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CountdownExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $t;

    /**
     * @param TranslatorInterface $t
     */
    public function __construct(TranslatorInterface $t)
    {
        $this->t = $t;
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('countdown', array($this, 'countdownFilter')),
        ];
    }

    /**
     * @param \DateInterval $interval
     * @return string
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function countdownFilter(\DateInterval $interval)
    {
        return $this->t->transChoice('1 day|%count% days', $interval->d)
            . ' ' . $this->t->transChoice('1 hour|%count% hours', $interval->h)
            . ' ' . $this->t->transChoice('1 minute|%count% minutes', $interval->i)
            . ' ' . $this->t->transChoice('1 second|%count% seconds', $interval->s);
    }
}
