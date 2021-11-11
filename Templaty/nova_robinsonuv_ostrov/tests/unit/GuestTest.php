<?php

use \App\Entity\Guest;

class GuestTest extends \Codeception\Test\Unit
{
    public function testPrepareNewUser()
    {
		$guest = new Guest();
		$pass = '3ad7gfda564f64';
		$now = new \DateTime();
		$guest->setRegistrationPassword($pass);
		$this->assertEquals($guest->getPassword(), $pass);
		$this->assertNotNull($guest->getToken());
		$this->asserttrue($now <= $guest->getTokenUpdatedAt() );
		$this->assertFalse($guest->isVerified());
    }

    public function testCreateNewUserFromFb()
    {
        $facebookId = '123id';
        $email = 'email@example.com';
        $name = 'name';
		$guest = Guest::createFromFbData($facebookId, $email, $name);
        $this->assertTrue($guest->isVerified());
        $this->assertEquals($facebookId, $guest->getFacebookId());
        $this->assertEquals($email, $guest->getEmail());
        $this->assertEquals($name, $guest->getName());

        // password should not contain hash
        $this->assertContains(' ', $guest->getPassword());
    }



    public function testLogsHaveEssentialData()
    {
		$guest = new Guest();
		$pass = '3ad7gfda564f64';
		$ip = '127.0.0.1';
		$guest->setRegistrationPassword($pass);

        $log = $guest->createRegistrationLog(['ip' => $ip]);
        $this->assertEquals($log->getGuest(), $guest);
        $this->assertArraySubset([
            'ip' => $ip,
            'email' => $guest->getEmail(),
            'token' => $guest->getToken()
        ], $log->getData());

        $log = $guest->createLoginSuccessLog(['ip' => $ip]);
        $this->assertEquals($log->getGuest(), $guest);
        $this->assertArraySubset(['ip' => $ip], $log->getData());
        $this->assertArrayHasKey('pass_short', $log->getData());

        $log = $guest->createLoginFailureLog(['ip' => $ip]);
        $this->assertEquals($log->getGuest(), $guest);
        $this->assertArraySubset(['ip' => $ip], $log->getData());
        $this->assertArrayHasKey('pass_short', $log->getData());
    }

    public function testEmailConfirmation()
    {
        $guest = new Guest();
        $guest->regenerateToken();
        $guest->verifyEmail();
        $this->assertTrue($guest->isVerified());
        $this->assertFalse($guest->isTokenValid());

        $guest->setBanned(true);
        // banned status can not be changed by confirmation
        $guest->verifyEmail();
        $this->assertTrue($guest->isVerified());
        $this->assertTrue($guest->isBanned());
    }

    public function testPasswordReset()
    {
        $guest = new Guest();
        $guest->verifyEmail();
        $guest->regenerateToken();
        $password = 'someHash';
        $guest->resetPassword($password);
        $this->assertEquals($password, $guest->getPassword());
        $this->assertFalse($guest->isTokenValid());
    }

    public function testTokenValidity()
    {
        $guest = new Guest();
        $this->assertFalse($guest->isTokenValid());

        $guest->regenerateToken();
        $this->assertTrue($guest->isTokenValid());

        $old = new DateTime();
        $old->modify('-6 days');

        $reflection = new \ReflectionClass($guest);
        $property = $reflection->getProperty('tokenUpdatedAt');
        $property->setAccessible(true);
        $property->setValue($guest, $old);

        $this->assertFalse($guest->isTokenValid());
    }
}