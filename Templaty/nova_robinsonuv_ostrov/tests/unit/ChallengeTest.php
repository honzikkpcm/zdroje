<?php

use \App\Entity\Challenge;
use \App\Entity\ChallengeTask;
use App\Entity\GuestChallenge;

class ChallengeTest extends \Codeception\Test\Unit
{

    public function testRating()
    {
        $challenge = new Challenge();
        $challenge->setActive(true);
        $challenge->setValidFrom(new \DateTime('yesterday'));
        $challenge->setValidTo( new \DateTime('tomorrow'));

        $tasks = $challenge->getTasks();
        $task = $this->createMock(ChallengeTask::class);
        $task->method('getId')->willReturn(100);
        $task->method('rate')->willReturn(1);
        $tasks->add($task);
        $task = $this->createMock(ChallengeTask::class);
        $task->method('getId')->willReturn(10);
        $task->method('rate')->willReturn(2);
        $tasks->add($task);
        $answers = new GuestChallenge();
        $answers->setData(['answers' => [
            100 => ['answer' => 1],
            10 => ['answer' => 1],
        ]]);
        $challenge->rate($answers);
        $this->assertEquals(3, $answers->getScore());
    }

    public function testGetMaxScore()
    {
        $challenge = new Challenge();
        $tasks = $challenge->getTasks();
        $this->assertEquals(0, $challenge->getMaxScore());
        $task = new ChallengeTask();
        $task->setScore(10);
        $tasks->add($task);
        $task = new ChallengeTask();
        $task->setScore(0);
        $tasks->add($task);
        $task = new ChallengeTask();
        $task->setScore(20);
        $tasks->add($task);
        $this->assertEquals(30, $challenge->getMaxScore());
    }
}