<?php

namespace Brexis\LaravelSSO\Test;

use Mockery;
use Illuminate\Auth\EloquentUserProvider;
use Brexis\LaravelSSO\SSOGuard;
use Brexis\LaravelSSO\ClientBrokerManager;

class SSOGuardTest extends TestCase
{
    protected $provider;

    public function setUp()
    {
        parent::setUp();
        $this->broker = Mockery::mock(ClientBrokerManager::class);
        $this->provider = Mockery::mock(EloquentUserProvider::class);
        $this->guard = new SSOGuard($this->provider, $this->broker);
    }

    public function testShouldRetunUserByCheckingEmail()
    {
        $this->broker->shouldReceive('profile')->andReturn([
            'id' => 1,
            'email' => 'admin@test.com'
        ]);

        $this->provider->shouldReceive('retrieveByCredentials')
             ->with(['email' => 'admin@test.com'])
             ->andReturn((object) ['id' => 1]);

        $user = $this->guard->user();

        $this->assertEquals($user->id, 1);
    }

    public function testShouldRetunUserByCheckingUsername()
    {
        $this->app['config']->set('laravel-sso.broker_client_username', 'username');

        $this->broker->shouldReceive('profile')->andReturn([
            'id' => 1,
            'username' => 'admin'
        ]);

        $this->provider->shouldReceive('retrieveByCredentials')
             ->with(['username' => 'admin'])
             ->andReturn((object) ['id' => 1]);

        $user = $this->guard->user();

        $this->assertEquals($user->id, 1);
    }

    public function testShouldCheckUser()
    {
        $this->broker->shouldReceive('profile')->andReturn([
            'id' => 1,
            'email' => 'admin@test.com'
        ]);

        $this->provider->shouldReceive('retrieveByCredentials')
             ->with(['email' => 'admin@test.com'])
             ->andReturn((object) ['id' => 1]);

        $this->assertTrue($this->guard->check());
    }

    public function testShouldCheckNullUser()
    {
        $this->broker->shouldReceive('profile')->andReturn([
            'id' => 1,
            'email' => 'admin@test.com'
        ]);

        $this->provider->shouldReceive('retrieveByCredentials')
             ->with(['email' => 'admin@test.com'])
             ->andReturn(null);

        $this->assertFalse($this->guard->check());
    }

    public function testShouldReturnUserId()
    {
        $user = Models\User::create([
            'username' => 'admin', 'email' => 'admin@admin.com',
            'password' => bcrypt('secret')
        ]);

        $this->broker->shouldReceive('profile')->andReturn([
            'id' => 1,
            'email' => 'admin@test.com'
        ]);

        $this->provider->shouldReceive('retrieveByCredentials')
             ->with(['email' => 'admin@test.com'])
             ->andReturn($user);

        $this->assertEquals($this->guard->id(), $user->id);
    }

    public function testShouldAttemptToConnectButFail()
    {
        $credentials = ['foo' => 'bar'];
        $this->broker->shouldReceive('login')->with([
            'foo' => 'bar',
            'remember' => true
        ])->andReturn(false);

        $this->assertFalse($this->guard->attempt($credentials, true));
    }

    public function testShouldAttemptToConnectAndSucceed()
    {
        $user = new class {
            use \Brexis\LaravelSSO\Support\SSOUser;
            public $id = 1;
        };

        $credentials = ['email' => 'admin@test.com'];
        $this->broker->shouldReceive('login')->with([
            'email' => 'admin@test.com',
            'remember' => true
        ])->andReturn(['email' => 'admin@test.com']);

        $this->provider->shouldReceive('retrieveByCredentials')
             ->with(['email' => 'admin@test.com'])
             ->andReturn($user);

        $this->assertNotFalse($this->guard->attempt($credentials, true));
        $this->assertTrue($this->guard->check());
        $this->assertEquals($this->guard->user()->id, 1);
        $this->assertEquals($this->guard->user()->getPayload(), ['email' => 'admin@test.com']);
    }

    public function testShouldLogout()
    {
        $this->broker->shouldReceive('logout')->andReturn(true);
        $this->broker->shouldReceive('profile')->andReturn([]);

        $this->assertNull($this->guard->user());
    }
}
