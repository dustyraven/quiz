<?php declare(strict_types=1);

namespace Tests;

use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Quiz\Database;
use Quiz\Quiz;
use Quiz\User;

final class QuizTest extends TestCase
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
     * @var Quiz
     */
    private $quiz;


    public function setUp()
    {
        parent::setUp();

        /** @var Database|MockInterface */
        $this->database = Mockery::mock(Database::class);
        /** @var User|MockInterface */
        $this->user = Mockery::mock(User::class);

        $this->quiz = new Quiz($this->database, $this->user);
    }

    public function testGetNextQuestion()
    {
        $question = 'Question';
        $answers = [
            ['1' => 'Answer1'],
            ['2' => 'Answer2'],
        ];

        $this->user
            ->shouldReceive('getId')
            ->once()
            ->andReturn(123);

        $this->database
            ->shouldReceive('fetchAssoc')
            ->andReturn([
                'id' => '123',
                'question' => $question,
            ]);

        $this->database
            ->shouldReceive('fetchKeyVal')
            ->andReturn($answers);

        $expected = [
            'question' => $question,
            'answers'  => $answers,
        ];

        $this->assertSame($expected, $this->quiz->getNextQuestion());
    }
}
