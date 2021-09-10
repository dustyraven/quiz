<?php declare(strict_types=1);

namespace Quiz;

use PDO;

/**
 * Simple PDO wrapper
 */
class Database
{
    /**
     * @var PDO $dbh
     */
    private $dbh;

    /**
     * @var string $dbHost
     */
    private $dbHost = 'localhost';

    /**
     * @var string $dbUser
     */
    private $dbUser = 'linkmob';

    /**
     * @var string $dbPassword
     */
    private $dbPassword = 'somepassword';

    /**
     * @var string $dbUser
     */
    private $dbBase = 'quiz';


    public function getPDO(): PDO
    {
        return $this->dbh;
    }

    public function __construct()
    {
        if (!$this->dbh) {
            $this->connect();
        }
    }

    private function connect()
    {
        $this->dbh = new PDO(
            'mysql:host=' . $this->dbHost . ';dbname=' . $this->dbBase,
            $this->dbUser,
            $this->dbPassword
        );
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function exec(string $query): int
    {
        return $this->dbh->exec($query);
    }

    public function query(string $query, array $params = []): bool
    {
        $stmt = $this->dbh->prepare($query);
        return $stmt->execute($params);
    }

    public function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchKeyVal(string $query, array $params = []): array
    {
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function fetchAssoc(string $query, array $params = []): array
    {
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function fetchNum(string $query, array $params = []): array
    {
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_NUM) ?: [];
    }

    public function fetchValue(string $query, array $params = []): string
    {
        $stmt = $this->dbh->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
