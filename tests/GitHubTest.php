<?php

namespace duncan3dc\OAuth;

use PHPUnit\Framework\TestCase;

class GitHubTest extends TestCase
{
    protected function callProtectedMethod($methodName, $param)
    {
        $class = new \ReflectionClass(GitHub::class);

        $method = $class->getMethod($methodName);

        $method->setAccessible(true);

        return $method->invokeArgs(new GitHub([]), [$param]);
    }


    public function testGetUrl1()
    {
        $this->assertSame("https://api.github.com/orgs/github/repos", $this->callProtectedMethod("getUrl", "https://api.github.com/orgs/github/repos"));
    }


    public function testGetUrl2()
    {
        $this->assertSame("https://api.github.com/orgs/github/repos", $this->callProtectedMethod("getUrl", "/orgs/github/repos"));
    }


    public function testGetUrl3()
    {
        $this->assertSame("https://api.github.com/orgs/github/repos", $this->callProtectedMethod("getUrl", "orgs/github/repos"));
    }
}
