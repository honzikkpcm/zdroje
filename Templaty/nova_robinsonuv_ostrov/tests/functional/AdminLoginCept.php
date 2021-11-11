<?php
$tryFailedLogin = function ($email, $password, $I) {
	$I->amOnPage('/admin/staff/login');
	$I->seeResponseCodeIs(200);
	$I->submitForm('form', [
		'email' => $email,
		'password' => $password,
	]);
	$I->seeCurrentUrlEquals('/admin/staff/login');
	$I->seeElement('.alert-error');
};

$I = new FunctionalTester($scenario);
$I->wantTo('try login workflow');
// register
$name = 'staff';
$email = 'staff@cme.net';
$password = 'staff';
$I->createAdmin($name, $email, $password);
// bad login
foreach( [
	['', ''],
	[$email, ''],
	['', $password],
	[$email, 'BAD_PASSWORD'],
	['BAD@example.com', $password]
] as $attempt ) {
	$tryFailedLogin($attempt[0], $attempt[1], $I);
}

$I->amOnPage('/admin');
$I->seeCurrentUrlEquals('/admin/staff/login');

$I->amOnPage('/admin/article');
$I->seeCurrentUrlEquals('/admin/staff/login');
$I->submitForm('form', [
    'email' => $email,
    'password' => $password,
]);
$I->seeCurrentUrlEquals('/admin/article');