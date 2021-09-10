<?php declare(strict_types=1);

namespace Tests;

use Exception;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Quiz\Database;
use Quiz\QuizCron;
use Quiz\User;

final class QuizCronTest extends TestCase
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
     * @var QuizCron
     */
    private $quizCron;


    public function setUp()
    {
        parent::setUp();

        /** @var Database|MockInterface */
        $this->database = Mockery::mock(Database::class);
        /** @var User|MockInterface */
        $this->user = Mockery::mock(User::class);

        $this->quizCron = new QuizCron($this->database, $this->user);
    }

    public function testLoadTopUsers()
    {
        $this->database
            ->shouldReceive('fetchAll')
            ->once();

        $this->assertInstanceOf(QuizCron::class, $this->quizCron->loadTopUsers());
    }

    public function testGiveBonus()
    {
        $this->database
            ->shouldReceive('fetchAll')
            ->once()
            ->andReturn([[
                'user_id' => '123',
                'points' => '456'
            ]]);

        $this->quizCron->loadTopUsers();

        $this->user
            ->shouldReceive('load')
            ->with(123)
            ->andReturn($this->user);

        $this->user
            ->shouldReceive('updatePoints')
            ->with(QuizCron::BONUS_POINTS)
            ->andReturn($this->user);

        $this->assertInstanceOf(QuizCron::class, $this->quizCron->giveBonus());
    }
}
