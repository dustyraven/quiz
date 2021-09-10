<?php declare(strict_types=1);

namespace Quiz;

use Exception;

class Subscription
{
    const CREDIT_COST = 10;
    const MAX_RENEW_ATTEMPTS = 3;
    const SUBSCRIPTION_DAYS = 7;

    /**
     * @var Database $db
     */
    private $db;

    /**
     * @var User $user
     */
    private $user;

    /**
     * @var int $id
     */
    private $id;

    /**
     * @var string $startDate
     */
    private $startDate;

    /**
     * @var string $endDate
     */
    private $endDate;

    /**
     * @var int $renewAttempts
     */
    private $renewAttempts;

    /**
     * Class constructor
     */
    public function __construct(Database $db, User $user)
    {
        $this->db = $db;
        $this->user = $user;
    }

    /**
     * Load user's subscription from database
     * @return Subscription
     */
    public function load(): Subscription
    {
        $query = '
            SELECT `id`, `start_date`, `end_date`, `renew_attempts`
            FROM `user_subscription`
            WHERE `user_id` = ?
            ORDER BY `end_date` DESC
            LIMIT 1
        ';
        $data = $this->db->fetchAssoc($query, [$this->user->getId()]);

        return $data ? $this->hydrate($data) : $this;
    }

    /**
     * Return true if the subscription exists.
     * @return bool
     */
    public function exists(): bool
    {
        return (bool)$this->id;
    }

    /**
     * Return true if the subscription is active.
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->exists() && strtotime($this->endDate) >= strtotime('today');
    }

    /**
     * Return true if it's the last day of subscription.
     * @return bool
     */
    public function isLastDay(): bool
    {
        return $this->exists() && strtotime($this->endDate) === strtotime('today');
    }

    /**
     * Create new subscription
     * @return Subscription
     * @throws Exception
     */
    public function create(): Subscription
    {
        if ($this->exists()) {
            throw new Exception('Subscription already exists.');
        }

        $this->billUser();

        $query = "
            INSERT INTO `user_subscription`
            SET
                `user_id` = ?,
                `start_date` = CURDATE(),
                `end_date` = CURDATE() + INTERVAL ? DAY,
                `renew_attempts` = 0
        ";
        $this->db->query($query, [
            $this->user->getId(),
            static::SUBSCRIPTION_DAYS
        ]);

        return $this;
    }

    /**
     * Try to renew the subscription
     * @return Subscription
     * @throws Exception
     */
    public function renew(): Subscription
    {
        if ($this->renewAttempts >= static::MAX_RENEW_ATTEMPTS) {
            throw new Exception('Max renew attempts reached.');
        }

        $query = "
            UPDATE `user_subscription`
            SET `renew_attempts` = `renew_attempts` + 1
            WHERE `id` = ?
        ";
        $this->db->query($query, [$this->id]);
        $this->renewAttempts++;

        $this->billUser();

        $this->endDate = strftime('%F', strtotime($this->endDate . ' + ' . static::SUBSCRIPTION_DAYS . ' day'));

        $query = "
            UPDATE `user_subscription`
            SET
                `end_date` = ?,
                `renew_attempts` = 0
            WHERE `id` = ?
        ";
        $this->db->query($query, [
            $this->endDate,
            $this->id
        ]);

        $this->renewAttempts = 0;

        return $this;
    }

    /**
     * @return Subscription
     * @throws Exception
     */
    private function billUser(): Subscription
    {
        $this->user->updateCredits(-1 * static::CREDIT_COST);

        return $this;
    }

    /**
     * Map database result with class poperties
     * @param array $data
     * @return Subscription
     */
    private function hydrate(array $data): Subscription
    {
        $this->id            = (int)$data['id'];
        $this->startDate     = $data['start_date'];
        $this->endDate       = $data['end_date'];
        $this->renewAttempts = (int)$data['renew_attempts'];
        return $this;
    }

    /**
     * Load data for tests
     * @param array $data
     * @return Subscription
     */
    public function testLoad($data): Subscription
    {
        return $this->hydrate($data);
    }
}
