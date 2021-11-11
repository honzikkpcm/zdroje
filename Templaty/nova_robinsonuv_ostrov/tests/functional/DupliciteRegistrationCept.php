<?php
function register($name, $I) {
	$I->registerGuest($name, 'lojza.mlha@cme.net', 'eisenbahn');
    $I->clearEmails();
}

$I = new FunctionalTester($scenario);
$I->wantTo('verify that email can not be registered multiple times');
register('Lojza', $I);
$I->amOnPage('/guest/completed');
$I->seeResponseCodeIs(200);
register('Vilem', $I);
$I->seeCurrentUrlEquals('/guest/register');
$I->seeElement('//form//ul');