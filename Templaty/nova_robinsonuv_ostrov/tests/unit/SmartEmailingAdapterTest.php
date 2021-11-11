<?php

class SmartEmailingAdapterTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var \App\Service\Setting */
    protected $appServiceSetting;

    /** @var \App\Service\SmartEmailing */
    protected $appServiceSmartEmailing;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $entityManagerInterface;

    /** @var Twig_Environment */
    protected $twig;

    /**
     * @throws Exception
     */
    protected function _before()
    {
        try {
            $this->appServiceSetting = $this->make(\App\Service\Setting::class, ['get' => 1]);
            $this->appServiceSmartEmailing = $this->make(\App\Service\SmartEmailing::class, [
                'to' => null,
                'list' => null,
                'data' => null,
                'addToContactList' => function ($to, $list) {
                    $this->testParamsFunction = 'addToContactList';
                    $this->testParamsTo = $to;
                    $this->testParamsList = $list;
                },
                'deleteFromContactList' => function ($to, $list) {
                    $this->testParamsFunction = 'deleteFromContactList';
                    $this->testParamsTo = $to;
                    $this->testParamsList = $list;
                },
                'sendCustomEmail' => function ($data) {
                    $this->testParamsFunction = 'sendCustomEmail';
                    $this->testParamsData = $data;
                },
            ]);
            $this->entityManagerInterface = $this->make(\Doctrine\ORM\EntityManager::class, [
                'persist' => function ($message) {
                    $this->testParamsMessage = $message;
                },
                'flush' => function () {
                    $this->testParamsFlush = true;
                },
            ]);

            $loader = new Twig_Loader_Array([
                '/Emails/staff_reset_password.html.twig' => 'Reset {{ name }} account. Reset <strong><a href="{{ url }}">password</a></strong>.',
            ]);
            $this->twig = new Twig_Environment($loader);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     */
    protected function _after()
    {
    }

    // tests -----------------------------------------------------------------------------------------------------------

    /**
     */
    public function testSendMessageAddToContactList()
    {
        $message = (new \App\Entity\Message())
            ->setType(\App\Entity\Message::TYPE_GUEST_NOT_CLOSED_CHALLENGES)
            ->setTo('John Black <john@black.com>');

        $messenger = new \App\Service\SmartEmailingAdapter(
            $this->appServiceSmartEmailing,
            $this->twig,
            $this->entityManagerInterface,
            $this->appServiceSetting,
            [
                \App\Service\SmartEmailingAdapter::SETTING_DEFAULT_FROM => 'John Test <john@test.com>',
            ]
        );
        $messenger->send($message);

        // test message update
        $this->assertEquals('SmartEmailing', $message->getDriver());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertEquals('John Test <john@test.com>', $message->getFrom());
        $this->assertEquals('<h1>Body will be generated on the server.</h1>', $message->getBody());
        $this->assertNotNull($message->getSentAt());
        $this->assertTrue($message->isSent());
        // test driver calling
        $this->assertEquals('addToContactList', $this->testParamsFunction);
        $this->assertEquals('john@black.com', $this->testParamsTo);
        $this->assertEquals(1, $this->testParamsList);
    }

    /**
     */
    public function testSendMessageDeleteFromContactList()
    {
        $message = (new \App\Entity\Message())
            ->setType(\App\Entity\Message::TYPE_GUEST_BANNED)
            ->setTo('John Black <john@black.com>');

        $messenger = new \App\Service\SmartEmailingAdapter(
            $this->appServiceSmartEmailing,
            $this->twig,
            $this->entityManagerInterface,
            $this->appServiceSetting,
            [
                \App\Service\SmartEmailingAdapter::SETTING_DEFAULT_FROM => 'John Test <john@test.com>',
            ]
        );
        $messenger->send($message);

        // test message update
        $this->assertEquals('SmartEmailing', $message->getDriver());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertEquals('John Test <john@test.com>', $message->getFrom());
        $this->assertEquals('<h1>Body will be generated on the server.</h1>', $message->getBody());
        $this->assertNotNull($message->getSentAt());
        $this->assertTrue($message->isSent());
        // test driver calling
        $this->assertEquals('deleteFromContactList', $this->testParamsFunction);
        $this->assertEquals('john@black.com', $this->testParamsTo);
        $this->assertEquals(1, $this->testParamsList);
    }

    /**
     */
    public function testSendMessageCustomEmail()
    {
        $message = (new \App\Entity\Message())
            ->setType(\App\Entity\Message::TYPE_STAFF_RESET_PASSWORD)
            ->setTo('John Black <john@black.com>')
            ->setSubject('Test Guest Banned Email')
            ->setParams([
                'name' => 'John',
                'url' => 'http://reset.com?token=ftpa74Dfs625s',
            ]);

        $messenger = new \App\Service\SmartEmailingAdapter(
            $this->appServiceSmartEmailing,
            $this->twig,
            $this->entityManagerInterface,
            $this->appServiceSetting,
            [
                \App\Service\SmartEmailingAdapter::SETTING_DEFAULT_FROM => 'John Test <john@test.com>',
                \App\Service\SmartEmailingAdapter::SETTING_TAG => 'Robinson',
            ]
        );
        $messenger->send($message);

        // test message update
        $this->assertEquals('SmartEmailing', $message->getDriver());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertEquals('John Test <john@test.com>', $message->getFrom());
        $this->assertEquals(
            'Reset John account. Reset <strong><a href="http://reset.com?token=ftpa74Dfs625s">password</a></strong>.',
            $message->getBody());
        $this->assertNotNull($message->getSentAt());
        $this->assertTrue($message->isSent());
        // test driver calling
        $this->assertEquals('sendCustomEmail', $this->testParamsFunction);
        $this->assertEquals(
            'a:5:{s:6:"sender";s:25:"John Test <john@test.com>";s:9:"recipient";'
            .'s:27:"John Black <john@black.com>";s:3:"tag";s:8:"Robinson";s:7:"subject";s:23:"Test Guest Banned Email";'
            .'s:4:"body";s:103:"Reset John account. Reset '
            .'<strong><a href="http://reset.com?token=ftpa74Dfs625s">password</a></strong>.";}', serialize($this->testParamsData));
    }

    /**
     */
    public function testSendMessageStoreMessage()
    {
        $message = (new \App\Entity\Message())
            ->setType(\App\Entity\Message::TYPE_GUEST_NOT_CLOSED_CHALLENGES)
            ->setTo('John Black <john@black.com>');

        $messenger = new \App\Service\SmartEmailingAdapter(
            $this->appServiceSmartEmailing,
            $this->twig,
            $this->entityManagerInterface,
            $this->appServiceSetting,
            [
                \App\Service\SmartEmailingAdapter::SETTING_DEFAULT_FROM => 'John Test <john@test.com>',
                \App\Service\SmartEmailingAdapter::SETTING_STORE => true,
            ]
        );
        $messenger->send($message);

        // test if em methods was called
        $this->assertEquals('App\Entity\Message', get_class($this->testParamsMessage));
        $this->assertTrue($this->testParamsFlush);
        // test message update; message is passing by reference to persist
        $this->assertEquals('SmartEmailing', $this->testParamsMessage->getDriver());
        $this->assertNotNull($this->testParamsMessage->getCreatedAt());
        $this->assertEquals('John Test <john@test.com>', $this->testParamsMessage->getFrom());
        $this->assertEquals('<h1>Body will be generated on the server.</h1>', $this->testParamsMessage->getBody());
        $this->assertNotNull($this->testParamsMessage->getSentAt());
        $this->assertTrue($this->testParamsMessage->isSent());
    }
}