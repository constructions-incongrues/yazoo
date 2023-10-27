<?php

namespace App\Service;

use Doctrine\DBAL\Connection;


class DatabaseService
{
    private $connection;
    private $entityManager;

    /*
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    */

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function executeRawQuery($sql)
    {
        // Fetch the results as an associative array
        return $this->connection->fetchAllAssociative($sql);
    }

    public function executePreparedStatement($sql, array $parameters = [])
    {
        $stmt = $this->connection->prepare($sql);

        // Bind parameters to the prepared statement
        foreach ($parameters as $parameterName => $value) {
            $stmt->bindValue($parameterName, $value);
        }

        $stmt->executeStatement();

        // Fetch the results as an associative array
        //return $stmt->fetchAllAssociative();
    }

}
