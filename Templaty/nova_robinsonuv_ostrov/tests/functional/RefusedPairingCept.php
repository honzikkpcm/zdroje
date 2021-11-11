<?php
$I = new FunctionalTester($scenario);
$I->wantTo('login with fb but refuse to pair accounts');

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
    'pair' => false
]);
$I->seeCurrentUrlEquals('/guest/profile');