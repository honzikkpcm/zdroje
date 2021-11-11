<?php
$I = new FunctionalTester($scenario);
$I->wantTo('confirm registration email');
// register account
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';

$I->clearEmails();
$I->registerGuest($name, $email, $password);
$I->amOnPage('/guest/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $password,
]);
$I->seeCurrentUrlEquals('/guest/profile');
$I->see('finish registration process');

// check non-existent token can not be used
$I->amOnPage('/guest/verify-account/BAD_TOKEN_HASH');
$I->see('not valid');

$emails = $I->grabSentEmails();
$I->assertCount(1, $emails);
preg_match('#/guest/verify-account/(\w+)#', $emails[0]->getBody(), $m);
$token = $m[1];

$I->amOnPage('/guest/verify-account/' . $token);
$I->see('Email is confirmed');

// check that token can not be used twice
$I->amOnPage('/guest/verify-account/' . $token);
$I->see('not valid');

$I->amOnPage('/guest/profile');
$I->dontSee('finish registration process');