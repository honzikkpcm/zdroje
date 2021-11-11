<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class FunctionalTester extends \Codeception\Actor
{
    use _generated\FunctionalTesterActions;


	/**
	 * Define custom actions here
	 */
	public function dump()
	{
		echo $this->grabPageSource();
	}

	public function registerGuest($name, $email, $password)
	{
		$data = [
			'form[name]' => $name,
			'form[email]' => $email,
			'form[plainPassword]' => $password,
			'form[agreeWithTerms]' => true,
		];
		$this->amOnPage('/guest/register');
		$this->seeResponseCodeIs(200);
		$this->submitForm('form', $data);
    }

	public function loginGuest($email, $password)
	{
        $this->amOnPage('/guest/login');
        $this->submitForm('form', [
            'email' => $email,
            'password' => $password,
        ]);
    }

	public function amGuest($name, $email, $password)
	{
	    $this->clearEmails();
		$this->registerGuest($name, $email, $password);

        $emails = $this->grabSentEmails();
        $this->assertCount(1, $emails);
        preg_match('#/guest/verify-account/(\w+)#', $emails[0]->getBody(), $m);
        $token = $m[1];

        $this->amOnPage('/guest/verify-account/' . $token);
        $this->see('Email is confirmed');

        $this->loginGuest($email, $password);
        $this->clearEmails();
    }

	public function loginWithFb($name, $email, $fbId = '123456')
    {
        $code = 'FAKE_CODE';
        // prepare responses from facebook to verify code
        $this->persistService('httpmock.client');
        $mockClient = $this->grabService('httpmock.client');
        $mockClient->addResponse(new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode([
            'access_token'=> 'FAKE_TOKEN',
            'token_type'=> 'USER',
            'expires_in'=>  60,
        ])));
        $mockClient->addResponse(new GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode([
            'id' => $fbId,
            'name' => $name,
            'email' => $email,
        ])));
        $this->amOnPage('/guest/fb/login/check-facebook?code=' . $code);
    }

	public function createAdmin($name='admin', $email='example@example.com', $password='password')
	{
		$encoder = $this->grabService('security.password_encoder');
        $manager = $this->grabService('doctrine')->getManager();
        $user = new \App\Entity\Staff();
        $user->setActive(true);
        $user->setRole(\App\Entity\Staff::ROLE_ADMIN);
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($encoder->encodePassword($user, $password));
        $manager->persist($user);
        $manager->flush();
	}

	public function clearEmails()
    {
        $path = codecept_root_dir() . 'var/test/emails/default/';
        @exec('rm -rf ' . $path);
    }

    public function grabSentEmails()
    {
        $path = codecept_root_dir() . 'var/test/emails/default/*.message';
        $emails = [];
        foreach(glob($path) as $file) {
            $emails[] = unserialize(file_get_contents($file));
        }
        return $emails;
    }
}
