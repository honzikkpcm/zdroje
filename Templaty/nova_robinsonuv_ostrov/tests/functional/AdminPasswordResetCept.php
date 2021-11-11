<?php
$I = new FunctionalTester($scenario);
$I->wantTo('reset lost password');
// register
$name = 'staff';
$email = 'staff@cme.net';
$password = 'staff';
$I->createAdmin($name, $email, $password);
$I->clearEmails();
// request reset
$I->amOnPage('/admin/staff/login');
$I->submitForm('#reset-wrapper form', [
    'form[email]' => $email,
]);
$I->seeCurrentUrlEquals('/admin/staff/login');

// check non-existent token can not be used
$I->amOnPage('/admin/staff/reset-password/BAD_TOKEN_HASH');
$I->see('not valid');

$emails = $I->grabSentEmails();
$I->assertCount(1, $emails);

preg_match('#/admin/staff/reset-password/(\w+)#', $emails[0]->getBody(), $m);
$token = $m[1];
$I->clearEmails();

$I->amOnPage('/admin/staff/reset-password/' . $token);
$newPassword = 'staffnew';

$I->submitForm('form', [
    'form[newPassword][first]' => $newPassword,
    'form[newPassword][second]' => $newPassword,
]);
$I->seeCurrentUrlEquals('/admin/staff/login');
$I->see('successfully changed');
// login with new password

$I->submitForm('form', [
    'email' => $email,
    'password' => $newPassword,
]);
$I->seeCurrentUrlEquals('/admin');

// check token can not be used twice
$I->amOnPage('/admin/staff/reset-password/' . $token);
$I->see('not valid');