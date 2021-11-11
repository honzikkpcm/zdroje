<?php
$I = new FunctionalTester($scenario);
$I->wantTo('check user is not paired with bad credentials');

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
    'password' => 'BAD_PASSWORD',
    'pair' => true,
]);
$I->seeCurrentUrlEquals('/guest/connect-facebook');
$I->see('password is not valid');

$I->submitForm('form', [
    'password' => '',
    'pair' => true,
]);
$I->seeCurrentUrlEquals('/guest/connect-facebook');
$I->see('password is not valid');

