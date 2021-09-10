<?php declare(strict_types=1);

namespace Tests;

use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Quiz\Database;
use Quiz\Subscription;
use Quiz\User;

final class SubscriptionTest extends TestCase
{
    /**
     * @var Database|MockInterface
     */
    private $database;

    /**
     * @var User|MockInterface
     */
    private $user;

    /**
     * @var Subscription
     */
    private $subscription;

    /**
     * @var array
     */
    private $mockData;

    public function setUp()
    {
        parent::setUp();

        /** @var Database|MockInterface */
        $this->database = Mockery::mock(Database::class);
        /** @var User|MockInterface */
        $this->user = Mockery::mock(User::class);

        $this->mockData = [
            'id' => '',
            'start_date' => '',
            'end_date' => '',
            'renew_attempts' => '',
        ];

        $this->subscription = new Subscription($this->database, $this->user);
    }

    public function testLoad()
    {
        $this->user
            ->shouldReceive('getId')
            ->once()
            ->andReturn(123);

        $this->database
            ->shouldReceive('fetchAssoc')
            ->once();

        $this->assertInstanceOf(Subscription::class, $this->subscription->load());
    }

    public function testExists()
    {
        $this->assertFalse($this->subscription->exists());

        $this->mockData['id'] = '123';
        $this->assertTrue($this->subscription->testLoad($this->mockData)->exists());
    }

    public function testIsActive()
    {
        $this->assertFalse($this->subscription->isActive());

        $this->mockData = array_merge($this->mockData, [
            'id' => '123',
            'end_date' => strftime('%F', strtotime('tomorrow')),
        ]);

        $this->assertTrue($this->subscription->testLoad($this->mockData)->isActive());
    }

    public function testIsLastDay()
    {
        $this->assertFalse($this->subscription->isLastDay());

        $this->mockData = array_merge($this->mockData, [
            'id' => '123',
            'end_date' => strftime('%F', strtotime('today')),
        ]);

        $this->assertTrue($this->subscription->testLoad($this->mockData)->isLastDay());
    }

    public function testCreate()
    {
        $this->user
            ->shouldReceive('updateCredits')
            ->with(-1 * Subscription::CREDIT_COST)
            ->once()
            ->andReturn($this->user);

        $this->user
            ->shouldReceive('getId')
            ->once()
            ->andReturn(123);

        $this->database
            ->shouldReceive('query')
            ->once();

        $this->assertInstanceOf(Subscription::class, $this->subscription->create());
    }

    public function testCreateFail()
    {
        $this->user
            ->shouldReceive('updateCredits')
            ->with(-1 * Subscription::CREDIT_COST)
            ->once()
            ->andReturn($this->user);

        $this->user
            ->shouldReceive('getId')
            ->once()
            ->andReturn(123);

        $this->database
            ->shouldReceive('query')
            ->once();

        $this->mockData = array_merge($this->mockData, [
            'id' => '123',
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Subscription already exists.');

        $this->subscription->testLoad($this->mockData)->create();
    }

    public function testRenew()
    {
        $this->user
            ->shouldReceive('updateCredits')
            ->with(-1 * Subscription::CREDIT_COST)
            ->once()
            ->andReturn($this->user);

        $this->database
            ->shouldReceive('query')
            ->twice();

        $this->assertInstanceOf(Subscription::class, $this->subscription->testLoad($this->mockData)->renew());
    }

    public function testRenewFail()
    {
        // Test for max renew attempts.

        $this->mockData = array_merge($this->mockData, [
            'renew_attempts' => Subscription::MAX_RENEW_ATTEMPTS,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Max renew attempts reached.');

        $this->subscription->testLoad($this->mockData)->renew();

    }
}
