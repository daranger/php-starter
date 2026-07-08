<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;

class DbResult implements \IteratorAggregate
{
    private array $data;
    private int $position = 0;

    public function __construct(PDOStatement $stmt)
    {
        $this->data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchObject()
    {
        if (isset($this->data[$this->position])) {
            return (object) $this->data[$this->position++];
        }
        return false;
    }

    public function fetch_object()
    {
        return $this->fetchObject();
    }

    public function fetch_assoc()
    {
        if (isset($this->data[$this->position])) {
            return $this->data[$this->position++];
        }
        return false;
    }

    public function data_seek(int $offset)
    {
        $this->position = $offset;
    }

    public function __get($name)
    {
        if ($name === 'num_rows') {
            return count($this->data);
        }
        return null;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->data);
    }
}

class Db
{
    private static ?PDO $pdo = null;

    public static function getPdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = Container::getInstance()->make(PDO::class);
        }
        return self::$pdo;
    }

    public static function query(string $sql)
    {
        $stmt = self::getPdo()->query($sql);
        return new DbResult($stmt);
    }

    public static function realEscapeString(string $string): string
    {
        $quoted = self::getPdo()->quote($string);
        if (strlen($quoted) >= 2 && str_starts_with($quoted, "'") && str_ends_with($quoted, "'")) {
            return substr($quoted, 1, -1);
        }
        return $string;
    }
}
