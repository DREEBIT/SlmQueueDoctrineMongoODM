<?php

namespace SlmQueueDoctrineMongoODM\Options;

use SlmQueueDoctrineMongoODM\Queue\DoctrineMongoODMQueue;
use Zend\Stdlib\AbstractOptions;

/**
 * DoctrineOptions
 */
class DoctrineMongoODMOptions extends AbstractOptions
{
    /**
     * Name of the registered doctrine connection service
     *
     * @var string
     */
    protected $connection = 'doctrine.documentmanager.odm_default';

    /**
     * Table name which should be used to store jobs
     *
     * @var string
     */
    protected $collectionName = 'queue_default';

    /**
     * how long to keep deleted (successful) jobs (in minutes)
     *
     * @var int
     */
    protected $deletedLifetime = DoctrineMongoODMQueue::LIFETIME_DISABLED;

    /**
     * how long to keep buried (failed) jobs (in minutes)
     *
     * @var int
     */
    protected $buriedLifetime = DoctrineMongoODMQueue::LIFETIME_DISABLED;

    /**
     * Set the name of the doctrine connection service
     *
     * @param  string $connection
     * @return void
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the connection service name
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param  int  $buriedLifetime
     * @return void
     */
    public function setBuriedLifetime($buriedLifetime)
    {
        $this->buriedLifetime = (int) $buriedLifetime;
    }

    /**
     * @return int
     */
    public function getBuriedLifetime()
    {
        return $this->buriedLifetime;
    }

    /**
     * @param  int  $deletedLifetime
     * @return void
     */
    public function setDeletedLifetime($deletedLifetime)
    {
        $this->deletedLifetime = (int) $deletedLifetime;
    }

    /**
     * @return int
     */
    public function getDeletedLifetime()
    {
        return $this->deletedLifetime;
    }

    /**
     * @param  string $collectionName
     * @return void
     */
    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }
}
