<?php declare(strict_types=1);

namespace Quiz;

use Exception;

class User
{
    /**
     * @var Database $db
     */
    private $db;

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var int $credits
     */
    private $credits;

    /**
     * @var int $points
     */
    private $points;

    /**
     * @var string $created
     */
    private $created;


    public function getId(): int
    {
        return $this->id;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getCredits(): int
    {
        return $this->credits;
    }

    /**
     * Class constructor
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Load user from database
     * @param int $userId
     * @return User
     * @throws Exception
     */
    public function load(int $userId): User
    {
        $query = '
            SELECT `id`, `credits`, `points`, `created`
            FROM `user`
            WHERE `id` = ?
        ';
        $data = $this->db->fetchAssoc($query, [$userId]);

        if (!$data) {
            throw new Exception('Unknown user.');
        }

        return $this->hydrate($data);
    }

    /**
     * If user is in trial (first day from registration)
     * @return bool
     */
    public function inTrial(): bool
    {
        return strftime('%F') === $this->created;
    }

    /**
     * Update user's credits
     * @param int $diff
     * @return User
     * @throws Exception
     */
    public function updateCredits(int $diff): User
    {
        $newCredits = $this->credits + $diff;
        if ($newCredits < 0) {
            throw new Exception('Not enough credits.');
        }

        $query = "
            UPDATE `user`
            SET `credits` = ?
            WHERE `id` = ?
        ";
        $this->db->query($query, [$newCredits, $this->id]);

        $this->credits = $newCredits;
        return $this;
    }

    /**
     * Update user's points
     * @param int $diff
     * @return User
     * @throws Exception
     */
    public function updatePoints(int $diff): User
    {
        if ($diff < 0) {
            throw new Exception('Cannot add negative points.');
        }

        $this->points += $diff;

        $query = "
            UPDATE `user`
            SET `points` = ?
            WHERE `id` = ?
        ";
        $this->db->query($query, [$this->points, $this->id]);
        return $this;
    }

    /**
     * Map database result with class poperties
     * @param array $data
     * @return User
     */
    private function hydrate(array $data): User
    {
        $this->id       = (int)$data['id'];
        $this->credits  = (int)$data['credits'];
        $this->points   = (int)$data['points'];
        $this->created  = $data['created'];
        return $this;
    }

    /**
     * Load data for tests
     * @param array $data
     * @return User
     */
    public function testLoad($data): User
    {
        return $this->hydrate($data);
    }
}
