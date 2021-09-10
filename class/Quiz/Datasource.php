<?php declare(strict_types=1);

namespace Quiz;

use Exception;

class Datasource
{

    /**
     * @var int $userId
     */
    private $userId;

    /**
     * @var int $answerId
     */
    private $answerId;

    /**
     * @var mixed $data
     */
    private $data;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getAnswerId(): int
    {
        return $this->answerId ?? 0;
    }

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Load variables from JSON string
     * @return Datasource
     * @throws Exception
     */
    public function decodeJson(): Datasource
    {
        $this->data = json_decode($this->data, true);
        return $this;
    }

    public function validateData(): Datasource
    {
        if (empty($this->data)) {
            throw new Exception('Can not decode data.');
        }

        if (empty($this->data['userId'])) {
            throw new Exception('User ID is missing.');
        }

        return $this;
    }

    public function setVariables(): Datasource
    {
        $this->userId = $this->data['userId'];
        $this->answerId = $this->data['answerId'] ?? null;
        return $this;
    }
}
