<?php
$I = new FunctionalTester($scenario);
$I->wantTo('change my password');
// register and login
$name = 'staff';
$email = 'staff@cme.net';
$password = 'staff';
$I->createAdmin($name, $email, $password);
$I->amOnPage('/admin/staff/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $password,
]);
$I->seeCurrentUrlEquals('/admin');

$I->amOnPage('/admin/staff/change-password');
$newPassword = 'staffnew';
$I->submitForm('form', [
    'form[currentPassword]' => $password,
	'form[newPassword][first]' => $newPassword,
	'form[newPassword][second]' => $newPassword,
]);
$I->seeCurrentUrlEquals('/admin');
$I->see('successfully changed');

$I->amOnPage('/admin/staff/logout');

$I->amOnPage('/admin/staff/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $newPassword,
]);
$I->seeCurrentUrlEquals('/admin');