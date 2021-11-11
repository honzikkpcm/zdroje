<?php
$I = new FunctionalTester($scenario);
$I->wantTo('change my password');
// register and login
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';
$I->registerGuest($name, $email, $password);
$I->clearEmails();
$I->amOnPage('/guest/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $password,
]);
$I->seeCurrentUrlEquals('/guest/profile');

$I->amOnPage('/guest/change-password');
$newPassword = 'eisenbahnwechsel';
$I->submitForm('form', [
    'form[currentPassword]' => $password,
	'form[newPassword][first]' => $newPassword,
	'form[newPassword][second]' => $newPassword,
]);
$I->seeCurrentUrlEquals('/guest/profile');
$I->see('successfully changed');

$I->amOnPage('/guest/logout');

$I->amOnPage('/guest/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $newPassword,
]);
$I->seeCurrentUrlEquals('/guest/profile');