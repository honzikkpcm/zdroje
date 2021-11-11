<?php
$I = new FunctionalTester($scenario);
$I->wantTo('pair my fb account with email account');

// register
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';
$I->registerGuest($name, $email, $password);
$I->seeResponseCodeIs(200);
$I->clearEmails();
$I->amOnPage('/guest/logout');

$I->loginWithFb($name, $email);

$I->seeCurrentUrlEquals('/guest/connect-facebook');
$I->see('already registered account');

$I->submitForm('form', [
    'password' => $password,
    'pair' => true,
]);
$I->seeCurrentUrlEquals('/guest/profile');
$I->see('account was paired');
