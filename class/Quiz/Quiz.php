<?php declare(strict_types=1);

namespace Quiz;

use Exception;

/**
 * Main class
 */
class Quiz
{
    const MAX_ANSWERS_PER_DAY = 10;
    const POINTS_CORRECT_ANSWER = 10;
    const BONUS_THIRD_ANSWER = 20;

    /**
     * @var Database $db
     */
    private $db;

    /**
     * @var User $user
     */
    private $user;

    /**
     * Class constructor
     */
    public function __construct(Database $db, User $user)
    {
        $this->db   = $db;
        $this->user = $user;
    }

    public function canAnswerMoreQuestions(): bool
    {
        return $this->getUserAnswersToday() < static::MAX_ANSWERS_PER_DAY;
    }

    /**
     * Get count of user's answers for today
     * @return int
     */
    private function getUserAnswersToday(): int
    {
        $query = "
            SELECT COUNT(`id`)
            FROM `user_answer`
            WHERE `user_id` = ?
            AND `date_answered` = CURDATE()
        ";
        return (int)$this->db->fetchValue($query, [$this->user->getId()]);
    }

    /**
     * Set user's getAnswerPoints
     * @param int $answer_id
     * @return int - points gained
     * @throws Exception
     */
    public function setUserAnswer(int $answer_id): int
    {
        $answersToday = $this->getUserAnswersToday();

        if ($answersToday >= static::MAX_ANSWERS_PER_DAY) {
            throw new Exception('Cannot add more answers for today.');
        }

        $query = "
            SELECT `quiz_question_id`, `correct`
            FROM `quiz_answer`
            WHERE `id` = ?
        ";
        list($question_id, $correct) = $this->db->fetchNum($query, [$answer_id]);

        $points = $correct ?
            (($answersToday + 1) % 3 ? static::POINTS_CORRECT_ANSWER : static::BONUS_THIRD_ANSWER) :
            0;

        $query = "
            INSERT INTO `user_answer`
            SET
                `user_id` = ?,
                `quiz_question_id` = ?,
                `quiz_answer_id` = ?,
                `date_answered` = CURDATE(),
                `points` = ?
        ";
        $this->db->query($query, [
            $this->user->getId(),
            $question_id,
            $answer_id,
            $points,
        ]);

        return $points;
    }

    /**
     * Get next question
     * @return array
     * @throws Exception
     */
    public function getNextQuestion(): array
    {
        $query = "
            SELECT `id`, `question`
            FROM `quiz_question`
            WHERE `id` NOT IN (
                SELECT `quiz_question_id`
                FROM `user_answer`
                WHERE `user_id` = ?
                  AND `date_answered` = CURDATE()
            )
            ORDER BY RAND();
        ";
        $question = $this->db->fetchAssoc($query, [$this->user->getId()]);

        if (empty($question)) {
            throw new Exception('No more questions for today.');
        }

        $query = "
            SELECT `id`, `answer`
            FROM `quiz_answer`
            WHERE `quiz_question_id` = ?
        ";
        $answers = $this->db->fetchKeyVal($query, [$question['id']]);

        return [
            'question' => $question['question'],
            'answers'  => $answers,
        ];
    }
}
