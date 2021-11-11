<?php
$I = new FunctionalTester($scenario);
$I->wantTo('confirm registration email');
// register account
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';

$I->registerGuest($name, $email, $password);
$I->amOnPage('/guest/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $password,
]);
$I->seeCurrentUrlEquals('/guest/profile');
$I->clearEmails();

$I->see('finish registration process');
$I->submitForm('form', []);
$I->see('instructions on how to finish verification process');

$emails = $I->grabSentEmails();
$I->assertCount(1, $emails);
preg_match('#/guest/verify-account/(\w+)#', $emails[0]->getBody(), $m);
$token = $m[1];

$I->amOnPage('/guest/verify-account/' . $token);
$I->see('Email is confirmed');