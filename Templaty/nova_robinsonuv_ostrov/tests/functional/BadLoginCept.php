<?php
function tryFailedLogin($email, $password, $I) {
	$I->amOnPage('/guest/login');
	$I->seeResponseCodeIs(200);
	$I->submitForm('form', [
		'email' => $email,
		'password' => $password,
	]);
	$I->seeCurrentUrlEquals('/guest/login');
	$I->seeElement('.error');
}

$I = new FunctionalTester($scenario);
$I->wantTo('check user is not logged with bad credentials');
// register
$name = 'Lojza';
$email = 'lojza.mlha@cme.net';
$password = 'eisenbahn';
$I->registerGuest($name, $email, $password);
$I->clearEmails();
$I->amOnPage('/guest/completed');
$I->seeResponseCodeIs(200);
// login
foreach( [
	['', ''],
	[$email, ''],
	['', $password],
	[$email, 'BAD_PASSWORD'],
	['bad.email@cme.net', $password]
] as $attempt ) {
	tryFailedLogin($attempt[0], $attempt[1], $I);
}

// assure auth logs are stored
/** @var \Doctrine\Common\Persistence\ManagerRegistry $em */
$em = $I->grabService('doctrine');
$I->assertCount(1, $em->getRepository(\App\Entity\GuestEventLog::class)->findAll());