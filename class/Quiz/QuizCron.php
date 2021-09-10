<?php declare(strict_types=1);

namespace Quiz;

class QuizCron
{
    const BONUS_POINTS = 20;
    const TOP_USERS_COUNT = 3;

    /**
     * @var Database $db
     */
    private $db;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @var array $topUsers
     */
    private $topUsers;

    /**
     * Class constructor
     */
    public function __construct(Database $db, User $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * Load users with most points for the current week
     * @return QuizCron
     */
    public function loadTopUsers(): QuizCron
    {
        $query = "
            SELECT
                `user_id`,
                SUM(`points`) AS `points`
            FROM `user_answer`
            WHERE `date_answered` > NOW() - INTERVAL 7 DAY
            GROUP BY `user_id`
            ORDER BY `points` DESC
            LIMIT " . static::TOP_USERS_COUNT;

        $this->topUsers = array_map('intval', array_column($this->db->fetchAll($query), 'user_id'));
        return $this;
    }

    /**
     * Give bonus points to the top users
     * @return QuizCron
     */
    public function giveBonus(): QuizCron
    {
        foreach ($this->topUsers as $userId) {
            $this->user->load($userId)->updatePoints(static::BONUS_POINTS);
        }
        return $this;
    }
}
