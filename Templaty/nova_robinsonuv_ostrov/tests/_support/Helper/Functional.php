<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Functional extends \Codeception\Module
{
    public function followRedirects(bool $follow) {
        $this->getModule('Symfony')->client->followRedirects($follow);
    }

    public function sendPost($url, $data) {
        $this->getModule('Symfony')->_loadPage('POST', $url, $data);
    }
}
