<?php declare(strict_types=1);

namespace Tests;

use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Quiz\Database;
use Quiz\User;

final class UserTest extends TestCase
{
    /**
     * @var Database|MockInterface
     */
    private $database;

    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $mockData;

    public function setUp()
    {
        parent::setUp();

        /** @var Database|MockInterface */
        $this->database = Mockery::mock(Database::class);

        $this->mockData = [
            'id' => '',
            'credits' => '',
            'points' => '',
            'created' => '',
        ];

        $this->user = new User($this->database);
    }

    public function testLoad()
    {
        $this->database
            ->shouldReceive('fetchAssoc')
            ->once()
            ->andReturn($this->mockData);

        $this->assertInstanceOf(User::class, $this->user->load(123));
    }

    public function testInTrial()
    {
        $this->assertFalse($this->user->inTrial());

        $this->mockData['created'] = strftime('%F');
        $this->assertTrue($this->user->testLoad($this->mockData)->inTrial());
    }

    public function testUpdateCredits()
    {
        $this->database
            ->shouldReceive('query')
            ->once();

        $this->assertInstanceOf(User::class, $this->user->updateCredits(100));
    }

    public function testUpdateCreditsFail()
    {
        $this->database
            ->shouldReceive('query')
            ->once();

        $this->mockData['credits'] = 5;

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Not enough credits.');

        $this->user->testLoad($this->mockData)->updateCredits(-10);
    }

    public function testUpdatePoints()
    {
        $this->database
            ->shouldReceive('query')
            ->once();

        $this->assertInstanceOf(User::class, $this->user->updatePoints(10));
    }

    public function testUpdatePointsFail()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Cannot add negative points.');

        $this->user->updatePoints(-10);
    }
}
