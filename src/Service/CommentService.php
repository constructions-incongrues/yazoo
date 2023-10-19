<?php

namespace App\Service;

use Doctrine\DBAL\Connection;


class CommentService
{
    private $connection;


    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    private function executeRawQuery($sql)
    {
        // Fetch the results as an associative array
        return $this->connection->fetchAllAssociative($sql);
    }

    /**
     * Return LUM Comments records
     *
     * @return array
     */
    public function getDatabaseComments(int $startId, int $limit)
    {

        $sql="SELECT CommentID, DiscussionID, AuthUserID, Body FROM mi.LUM_Comment WHERE CommentID>=$startId AND Deleted='0' LIMIT $limit;";
        return $this->executeRawQuery($sql);
    }

}