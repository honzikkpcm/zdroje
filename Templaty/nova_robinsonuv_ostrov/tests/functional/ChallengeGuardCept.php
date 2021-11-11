<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('not be able to submit inactive challenge form');
//prepare test data
/** @var Doctrine\DBAL\Connection $connection */
$connection = $I->grabService('doctrine')->getConnection();
$name = 'inactive challenge';
$connection->insert( 'challenge', [
    'id'=> 1,
    'competition_id'=> null,
    'type' => 'standard',
    'name' => $name,
    'urlcode' => 'inactive',
    'urlcode_hash' => 'hash',
    'description'=> 'description',
    'image'=> null,
    'valid_from' => (new \DateTime('yesterday'))->format(DATE_ISO8601),
    'valid_to' => (new \DateTime('tomorrow'))->format(DATE_ISO8601),
    'number'=> 0,
    'active'=> 0,
    'created_at' => (new \DateTime('yesterday'))->format(DATE_ISO8601),
]);
$name = 'not started challenge';
$connection->insert( 'challenge', [
    'id'=> 2,
    'competition_id'=> null,
    'type' => 'standard',
    'name' => $name,
    'urlcode' => 'not-started',
    'urlcode_hash' => 'hash2',
    'description'=> 'description',
    'image'=> null,
    'valid_from' => (new \DateTime('tomorrow'))->format(DATE_ISO8601),
    'valid_to' => (new \DateTime('next week'))->format(DATE_ISO8601),
    'number'=> 1,
    'active'=> 1,
    'created_at' => (new \DateTime('yesterday'))->format(DATE_ISO8601),
]);

$I->amOnPage('/challenge/foo');
$I->seeCurrentUrlEquals('/guest/login');

$I->amGuest('lojza', 'lojza@cme.net', 'password');

foreach(['inactive', 'not-started', 'undefined'] as $slug) {

    $I->amOnPage('/challenge/' . $slug);
    $I->seeResponseCodeIs(404);

    $I->sendPost('/challenge/' . $slug, ['foo' => 'bar']);
    $I->seeResponseCodeIs(404);
}

$I->amOnPage('/guest/logout');
$I->registerGuest('unverified', 'unverified@cme.net', 'password');
$I->loginGuest('unverified@cme.net', 'password');

$I->amOnPage('/challenge/foo');
$I->seeResponseCodeIs(403);

$I->sendPost('/challenge/foo', ['foo' => 'bar']);
$I->seeResponseCodeIs(403);


