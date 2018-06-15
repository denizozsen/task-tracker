<?php

namespace Tests\Unit\Service;

use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\LoginService;
use App\Session;
use App\User;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginServiceTest extends TestCase
{
    /** @var LoginService */
    private $loginService;

    /** @var UserRepository|MockObject */
    private $userRepository;

    /** @var SessionRepository|MockObject */
    private $sessionRepository;

    public function setUp()
    {
        parent::setUp();

        $this->userRepository = $this->getMockBuilder(UserRepository::class)
            ->setMethods(['getById', 'getAll', 'getAllMultipleCriteria'])
            ->getMock();
        $this->sessionRepository = $this->getMockBuilder(SessionRepository::class)
            ->setMethods(['getFirst'])
            ->getMock();

        $this->loginService = new LoginService($this->userRepository, $this->sessionRepository);
    }

    //
    // TODO - write more tests to improve coverage of LoginService!
    //

    public function test_getSession_standardUser()
    {
        $token = 'abc12345';

        $user = new User([
            'id'       => 123,
            'name'     => 'Mock',
            'email'    => 'fake@email.com',
            'password' => '12345',
            'role'     => 'standard',

        ]);
        $this->userRepository
            ->method('getById')
            ->willReturn($user);

        $session = new Session([
            'token' => $token,
            'user'  => $user->id
        ]);
        $this->sessionRepository
            ->method('getFirst')
            ->willReturn($session);

        $result = $this->loginService->getSession($token);

        $this->assertArrayHasKey('user', $result);
        $this->assertSame($user, $result['user']);

        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($token, $result['token']);

        $this->assertArrayHasKey('managed_users', $result);
        $this->assertEmpty($result['managed_users']);
    }

    public function test_getSession_manager()
    {
        $token = 'abc12345';

        $user = new User([
            'id'       => 1,
            'name'     => 'Mock',
            'email'    => 'fake@email.com',
            'password' => '12345',
            'role'     => 'manager',

        ]);
        $this->userRepository
            ->method('getById')
            ->willReturn($user);

        $managedUsers = [
            new User(['id'=>2, 'name'=>'Two',  'email'=>'two@email.com',  'password'=>'22',    'role'=>'standard']),
            new User(['id'=>3, 'name'=>'Five', 'email'=>'five@email.com', 'password'=>'55555', 'role'=>'standard']),
        ];
        $this->userRepository
            ->method('getAllMultipleCriteria')
            ->willReturn($managedUsers);

        $session = new Session([
            'token' => $token,
            'user'  => $user->id
        ]);
        $this->sessionRepository
            ->method('getFirst')
            ->willReturn($session);

        $this->userRepository
            ->expects($this->once())
            ->method('getAllMultipleCriteria')
            ->with($this->callback(function (array $criteria) {
                foreach ($criteria as $criterion) {
                    $column   = $criterion[0] ?? null;
                    $operator = $criterion[1] ?? null;
                    $value    = $criterion[2] ?? null;
                    if ($column == 'role' && ($operator != '=' || $value != 'standard')) {
                        return false;
                    }
                }
                return true;
            }));

        $result = $this->loginService->getSession($token);

        $this->assertArrayHasKey('user', $result);
        $this->assertSame($user, $result['user']);

        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($token, $result['token']);

        $this->assertArrayHasKey('managed_users', $result);
        $this->assertSame($managedUsers, $result['managed_users']);
    }

    public function test_getSession_admin()
    {
        $token = 'abc12345';

        $user = new User([
            'id'       => 1,
            'name'     => 'Mock',
            'email'    => 'fake@email.com',
            'password' => '12345',
            'role'     => 'admin',

        ]);
        $this->userRepository
            ->method('getById')
            ->willReturn($user);

        $managedUsers = [
            new User(['id'=>2, 'name'=>'Two',   'email'=>'two@email.com',   'password'=>'22',   'role'=>'standard']),
            new User(['id'=>3, 'name'=>'Three', 'email'=>'three@email.com', 'password'=>'333',  'role'=>'manager']),
            new User(['id'=>4, 'name'=>'Four',  'email'=>'four@email.com',  'password'=>'4444', 'role'=>'admin']),
        ];
        $this->userRepository
            ->method('getAll')
            ->willReturn($managedUsers);

        $session = new Session([
            'token' => $token,
            'user'  => $user->id
        ]);
        $this->sessionRepository
            ->method('getFirst')
            ->willReturn($session);

        $this->userRepository
            ->expects($this->once())
            ->method('getAll')
            ->with($this->callback(function ($column, $operator = null, $value = null, $boolean = 'and') {
                return $column != 'role';
            }));

        $result = $this->loginService->getSession($token);

        $this->assertArrayHasKey('user', $result);
        $this->assertSame($user, $result['user']);

        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($token, $result['token']);

        $this->assertArrayHasKey('managed_users', $result);
        $this->assertSame($managedUsers, $result['managed_users']);
    }
}
