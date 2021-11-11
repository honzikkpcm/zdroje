<?php

use \App\Entity\Challenge;
use \App\Entity\ChallengeTask;
use App\Entity\GuestChallenge;

class GuestChallengeTest extends \Codeception\Test\Unit
{

    public function testStoringAnswerData()
    {
        $answers = new GuestChallenge();
        $data = ['foo' => 1];
        $answers->setTaskAnswer(10, []);
        $answers->setTaskAnswer(100, $data);
        $this->assertEquals($data, $answers->getTaskAnswer(100));
    }
}