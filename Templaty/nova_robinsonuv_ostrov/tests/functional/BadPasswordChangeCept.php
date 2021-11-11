<?php
$I = new FunctionalTester($scenario);
$I->wantTo('change my password with bad credentials');

// login required
$I->amOnPage('/guest/change-password');
$I->seeCurrentUrlEquals('/guest/login');

// register
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';
$I->registerGuest($name, $email, $password);
$I->clearEmails();
// login required
$I->amOnPage('/guest/change-password');
$I->seeCurrentUrlEquals('/guest/login');


$I->amOnPage('/guest/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $password,
]);

$I->amOnPage('/guest/change-password');
$newPassword = 'eisenbahnwechsel';
$I->submitForm('form', [
    'form[currentPassword]' => 'BAD PASSWORD',
	'form[newPassword][first]' => $newPassword,
	'form[newPassword][second]' => $newPassword,
]);
$I->seeCurrentUrlEquals('/guest/change-password');
$I->submitForm('form', [
    'form[currentPassword]' => $password,
	'form[newPassword][first]' => $password,
	'form[newPassword][second]' => $password,
]);
$I->seeCurrentUrlEquals('/guest/change-password');
$I->submitForm('form', [
    'form[currentPassword]' => '',
	'form[newPassword][first]' => $newPassword,
	'form[newPassword][second]' => $newPassword,
]);
$I->seeCurrentUrlEquals('/guest/change-password');
$I->submitForm('form', [
    'form[currentPassword]' => $password,
	'form[newPassword][first]' => '',
	'form[newPassword][second]' => '',
]);
$I->seeCurrentUrlEquals('/guest/change-password');
$I->submitForm('form', [
    'form[currentPassword]' => $password,
	'form[newPassword][first]' => $newPassword,
	'form[newPassword][second]' => 'BAD NEW PASSWORD',
]);
$I->seeCurrentUrlEquals('/guest/change-password');