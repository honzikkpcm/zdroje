<?php
$I = new FunctionalTester($scenario);
$I->wantTo('use my fb account');
$I->amOnPage('/guest/login');
$I->followRedirects(false);
$I->click('Facebook login');
$I->seeResponseCodeIs(302);
$I->see('Redirecting to https://www.facebook.com');
// simulate fb redirect
$I->followRedirects(true);
$code = 'FAKE_CODE';
$email = 'lojza.mlha@cme.net';
$name = 'lojza mlha';
$I->loginWithFb($name, $email);

$I->seeCurrentUrlEquals('/guest/connect-facebook');
$I->see('Register');
$I->submitForm('form', [
    'agreeWithTerms' => 1
]);
$I->seeCurrentUrlEquals('/guest/profile');
$I->see('creating new account');

$I->wantTo('make sure password reset is disabled');
$I->clearEmails();
$I->amOnPage('/guest/request-password-reset');
$I->submitForm('form', [
    'form[email]' => $email,
]);
$I->seeResponseCodeIs(200);

$emails = $I->grabSentEmails();
$I->assertCount(1, $emails);
$I->assertFalse((bool) preg_match('#/guest/reset-password/(\w+)#', $emails[0]->getBody()));
$I->asserttrue((bool) preg_match('#/guest/login#', $emails[0]->getBody()));
$I->clearEmails();
