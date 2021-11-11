<?php
$I = new FunctionalTester($scenario);
$I->wantTo('reset lost password');
// register account
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';
$I->clearEmails();
$I->registerGuest($name, $email, $password);

// only confirmed account can reset passwords
$I->amOnPage('/guest/request-password-reset');
$I->submitForm('form', [
    'form[email]' => $email,
]);
$I->see('Please confirm email');

// confirm email
$emails = $I->grabSentEmails();
$I->assertCount(1, $emails);
preg_match('#/guest/verify-account/(\w+)#', $emails[0]->getBody(), $m);
$token = $m[1];
$I->clearEmails();

$I->amOnPage('/guest/verify-account/' . $token);
$I->see('Email is confirmed');

// request reset
$I->amOnPage('/guest/request-password-reset');
$I->submitForm('form', [
    'form[email]' => $email,
]);
$I->seeCurrentUrlEquals('/guest/completed');

// check non-existent token can not be used
$I->amOnPage('/guest/reset-password/BAD_TOKEN_HASH');
$I->see('not valid');

$emails = $I->grabSentEmails();
$I->assertCount(1, $emails);
preg_match('#/guest/reset-password/(\w+)#', $emails[0]->getBody(), $m);
$token = $m[1];
$I->clearEmails();

$I->amOnPage('/guest/reset-password/' . $token);
$newPassword = 'eisenbahnwechsel';
$I->submitForm('form', [
    'form[newPassword][first]' => $newPassword,
    'form[newPassword][second]' => $newPassword,
]);
$I->seeCurrentUrlEquals('/guest/login');
$I->see('successfully changed');
// login with new password

$I->submitForm('form', [
    'email' => $email,
    'password' => $newPassword,
]);
$I->seeCurrentUrlEquals('/guest/profile');

// check token can not be used twice
$I->amOnPage('/guest/reset-password/' . $token);
$I->see('not valid');