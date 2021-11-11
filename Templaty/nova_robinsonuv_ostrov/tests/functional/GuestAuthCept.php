<?php 
$I = new FunctionalTester($scenario);
$I->wantTo('register local account login  and logout');
// make sure I'm not logged in
$I->amOnPage('/guest/profile');
$I->seeCurrentUrlEquals('/guest/login');
// register
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';
$I->registerGuest($name, $email, $password);
$I->seeResponseCodeIs(200);
$I->clearEmails();

// login
$I->amOnPage('/guest/login');
$I->seeResponseCodeIs(200);
$I->submitForm('form', [
	'email' => $email,
	'password' => $password,
]);
$I->seeCurrentUrlEquals('/guest/profile');
$I->see('profile');
$I->see($email);

// logout
$I->amOnPage('/guest/logout');
// check redirectedFrom
$I->dontSeeCurrentUrlEquals('/guest/logout');
// check user is logged out
$I->amOnPage('/guest/profile');
$I->seeCurrentUrlEquals('/guest/login');
// assure auth logs are stored
/** @var \Doctrine\Common\Persistence\ManagerRegistry $em */
$em = $I->grabService('doctrine');
$I->assertCount(2, $em->getRepository(\App\Entity\GuestEventLog::class)->findAll());

