<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('submit challenge form');
//prepare test data
/** @var Doctrine\DBAL\Connection $connection */
$connection = $I->grabService('doctrine')->getConnection();
$name = 'foo challenge';
$connection->insert( 'challenge', [
    'id'=> 1,
    'competition_id'=> null,
    'type' => 'standard',
    'name' => $name,
    'urlcode' => 'foo-challenge',
    'urlcode_hash' => 'hash',
    'description'=> 'description',
    'image'=> null,
    'valid_from' => (new \DateTime('yesterday'))->format(DATE_ISO8601),
    'valid_to' => (new \DateTime('tomorrow'))->format(DATE_ISO8601),
    'number'=> 0,
    'active'=> 1,
    'created_at' => (new \DateTime('yesterday'))->format(DATE_ISO8601),
]);
$score = 5;
$taskCaption = 'bar?';
$connection->insert( 'challenge_task', [
    'id'=> 1,
    'challenge_id'=> 1,
    'type' => 'quiz-abcd',
    'caption' => $taskCaption,
    'description'=> 'description',
    'data'=> json_encode(['answers' => [['answer' => 'answer', 'checked' => 'true']]]),
    'sorting' => 0,
    'active' => 1,
    'score'=> $score
]);

$I->amGuest('lojza', 'lojza@cme.net', 'password');

$url = '/challenge/foo-challenge';
$I->amOnPage($url);

$I->see($name);
$I->see($score . ' points');
$I->see($taskCaption);
$I->see('evaluate');

$I->followRedirects(false);
$I->sendPost($url, ['form' => ['1' =>  0]]);
$I->seeResponseCodeIs(302);
$I->see($url);
$I->amOnPage($url);
$I->dontSee('evaluate');
$I->see('You have scored 5 points');

// Second form submission is ignored
$I->sendPost($url, ['form' => ['1' =>  0]]);
$I->dontSeeResponseCodeIs(302);


