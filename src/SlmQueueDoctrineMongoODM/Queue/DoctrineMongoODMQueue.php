<?php

namespace SlmQueueDoctrineMongoODM\Queue;

use DateInterval;
use DateTime;
use DateTimeZone;
use Doctrine\ODM\MongoDB\DocumentManager;
use SlmQueue\Job\JobInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueue\Queue\AbstractQueue;
use SlmQueueDoctrineMongoODM\Exception;
use SlmQueueDoctrineMongoODM\Options\DoctrineMongoODMOptions;

class DoctrineMongoODMQueue extends AbstractQueue implements DoctrineMongoODMQueueInterface
{
    const STATUS_PENDING = 1;
    const STATUS_RUNNING = 2;
    const STATUS_DELETED = 3;
    const STATUS_BURIED  = 4;

    const LIFETIME_DISABLED  = 0;
    const LIFETIME_UNLIMITED = -1;

    /**
     * Options for this queue
     *
     * @var DoctrineOptions $options
     */
    protected $options;

    /**
     * Constructor
     *
     * @param DocumentManager $documentManager
     * @param DoctrineMongoODMOptions $options
     * @param string $name
     * @param JobPluginManager $jobPluginManager
     */
    public function __construct(
		DocumentManager $documentManager,
        DoctrineMongoODMOptions $options,
        $name,
        JobPluginManager $jobPluginManager
    ) {
        $this->documentManager = $documentManager;
        $this->options		   = clone $options;

        parent::__construct($name, $jobPluginManager);
    }

    /**
     * @return \SlmQueueDoctrineMongoODM\Options\DoctrineMongoODMOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     *
     * Note : see DoctrineQueue::parseOptionsToDateTime for schedule and delay options
     */
    public function push(JobInterface $job, array $options = array())
    {
        $scheduled = $this->parseOptionsToDateTime($options);

		// TODO: put into factory
		$queryDocument = new \SlmQueueDoctrineMongoODM\Document\DefaultQueue();
		$queryDocument->setQueue($this->getName());
		$queryDocument->setStatus(self::STATUS_PENDING);
		$queryDocument->setCreated(new DateTime(null, new DateTimeZone(date_default_timezone_get())));
		$queryDocument->setData($this->serializeJob($job));
		$queryDocument->setScheduled($scheduled);

		$this->documentManager->persist($queryDocument);
		$this->documentManager->flush($queryDocument);

        $job->setId($queryDocument->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function pop(array $options = array())
    {
        // First run garbage collection
        $this->purge();

		$stmt = $this->documentManager->createQueryBuilder('SlmQueueDoctrineMongoODM\Document\DefaultQueue')
			->hydrate(FALSE)
			->findAndUpdate()
			->field('status')->equals(static::STATUS_PENDING)
			->field('queue')->equals($this->getName())
			->field('scheduled')->lte(new DateTime())
			->field('status')->set(static::STATUS_RUNNING)
			->field('executed')->set(new DateTime)
			->limit(1)
			->getQuery()
			->execute();

		if (!$stmt)
		{
			return NULL;
		}

		// Add job ID to meta data
		return $this->unserializeJob($stmt['data'], array('__id__' => (string) $stmt['_id']));
    }

    /**
     * {@inheritDoc}
     *
     * Note: When $deletedLifetime === 0 the job will be deleted immediately
     */
    public function delete(JobInterface $job, array $options = array())
    {
        if ($this->options->getDeletedLifetime() === static::LIFETIME_DISABLED)
		{
			$this->documentManager->createQueryBuilder('SlmQueueDoctrineMongoODM\Document\DefaultQueue')
				->remove()
				->field('id')->equals($job->getId())
				->getQuery()
				->execute();
        }
		else
		{
			$cursor = $this->documentManager->createQueryBuilder('SlmQueueDoctrineMongoODM\Document\DefaultQueue')
				->hydrate(FALSE)
				->findAndUpdate()
				->field('id')->equals($job->getId())
				->field('status')->equals(static::STATUS_RUNNING)
				->field('status')->set(static::STATUS_DELETED)
				->field('finished')->set(new DateTime())
				->getQuery()
				->execute();

            if ($cursor->count() != 1)
			{
                throw new Exception\LogicException("Race-condition detected while updating item in queue.");
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * Note: When $buriedLifetime === 0 the job will be deleted immediately
     */
    public function bury(JobInterface $job, array $options = array())
    {
        if ($this->options->getBuriedLifetime() === static::LIFETIME_DISABLED)
		{
			$this->documentManager->createQueryBuilder('SlmQueueDoctrineMongoODM\Document\DefaultQueue')
				->remove()
				->field('id')->equals($job->getId())
				->getQuery()
				->execute();
        }
		else
		{
            $message = isset($options['message']) ? $options['message'] : null;
            $trace   = isset($options['trace']) ? $options['trace'] : null;

			$cursor = $this->documentManager->createQueryBuilder('SlmQueueDoctrineMongoODM\Document\DefaultQueue')
				->hydrate(FALSE)
				->findAndUpdate()
				->field('id')->equals($job->getId())
				->field('status')->equals(static::STATUS_RUNNING)
				->field('status')->set( static::STATUS_BURIED)
				->field('finished')->set(new DateTime())
				->field('message')->set($message)
				->field('trace')->set($trace)
				->getQuery()
				->execute();

            if ($cursor->count() != 1)
			{
                throw new Exception\LogicException("Race-condition detected while updating item in queue.");
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function recover($executionTime)
    {
        $executedLifetime = $this->parseOptionsToDateTime(array('delay' => - ($executionTime * 60)));

        $update = 'UPDATE ' . $this->options->getTableName() . ' ' .
            'SET status = ? ' .
            'WHERE executed < ? AND status = ? AND queue = ? AND finished IS NULL';

        $rows = $this->connection->executeUpdate(
            $update,
            array(static::STATUS_PENDING, $executedLifetime, static::STATUS_RUNNING, $this->getName()),
            array(Type::SMALLINT, Type::DATETIME, Type::SMALLINT, Type::STRING)
        );

        return $rows;
    }

    /**
     * Create a concrete instance of a job from the queue
     *
     * @param  int          $id
     * @return JobInterface
     * @throws Exception\JobNotFoundException
     */
    public function peek($id)
    {
        $sql  = 'SELECT * FROM ' . $this->options->getTableName().' WHERE id = ?';
        $row  = $this->connection->fetchAssoc($sql, array($id), array(Type::SMALLINT));

        if (!$row) {
            throw new Exception\JobNotFoundException(sprintf("Job with id '%s' does not exists.", $id));
        }

        // Add job ID to meta data
        return $this->unserializeJob($row['data'], array('__id__' => $row['id']));
    }

    /**
     * Reschedules a specific running job
     *
     * Note : see DoctrineQueue::parseOptionsToDateTime for schedule and delay options
     *
     * @param  JobInterface             $job
     * @param  array                    $options
     * @throws Exception\LogicException
     */
    public function release(JobInterface $job, array $options = array())
    {
        $scheduled = $this->parseOptionsToDateTime($options);

        $update = 'UPDATE ' . $this->options->getTableName() . ' ' .
            'SET status = ?, finished = ? , scheduled = ?, data = ? ' .
            'WHERE id = ? AND status = ?';

        $rows = $this->connection->executeUpdate(
            $update,
            array(
                static::STATUS_PENDING,
                new DateTime(null, new DateTimeZone(date_default_timezone_get())),
                $scheduled,
                $this->serializeJob($job),
                $job->getId(),
                static::STATUS_RUNNING
            ),
            array(Type::SMALLINT, Type::DATETIME, Type::DATETIME, Type::STRING, Type::INTEGER, Type::SMALLINT)
        );

        if ($rows != 1) {
            throw new Exception\LogicException("Race-condition detected while updating item in queue.");
        }
    }

    /**
     * Parses options to a datetime object
     *
     * valid options keys:
     *
     * scheduled: the time when the job will be scheduled to run next
     * - numeric string or integer - interpreted as a timestamp
     * - string parserable by the DateTime object
     * - DateTime instance
     * delay: the delay before a job become available to be popped (defaults to 0 - no delay -)
     * - numeric string or integer - interpreted as seconds
     * - string parserable (ISO 8601 duration) by DateTimeInterval::__construct
     * - string parserable (relative parts) by DateTimeInterval::createFromDateString
     * - DateTimeInterval instance
     *
     * @see http://en.wikipedia.org/wiki/Iso8601#Durations
     * @see http://www.php.net/manual/en/datetime.formats.relative.php
     *
     * @param $options array
     * @return DateTime
     */
    protected function parseOptionsToDateTime($options)
    {
        $now       = new DateTime(null, new DateTimeZone(date_default_timezone_get()));
        $scheduled = clone ($now);

        if (isset($options['scheduled'])) {
            switch (true) {
                case is_numeric($options['scheduled']):
                    $scheduled = new DateTime(
                        sprintf("@%d", (int) $options['scheduled']),
                        new DateTimeZone(date_default_timezone_get())
                    );
                    break;
                case is_string($options['scheduled']):
                    $scheduled = new DateTime($options['scheduled'], new DateTimeZone(date_default_timezone_get()));
                    break;
                case $options['scheduled'] instanceof DateTime:
                    $scheduled = $options['scheduled'];
                    break;
            }
        }

        if (isset($options['delay'])) {
            switch (true) {
                case is_numeric($options['delay']):
                    $delay = new DateInterval(sprintf("PT%dS", abs((int) $options['delay'])));
                    $delay->invert = ($options['delay'] < 0) ? 1 : 0;
                    break;
                case is_string($options['delay']):
                    try {
                        // first try ISO 8601 duration specification
                        $delay = new DateInterval($options['delay']);
                    } catch (\Exception $e) {
                        // then try normal date parser
                        $delay = DateInterval::createFromDateString($options['delay']);
                    }
                    break;
                case $options['delay'] instanceof DateInterval:
                    $delay = $options['delay'];
                    break;
                default:
                    $delay = null;
            }

            if ($delay instanceof DateInterval) {
                $scheduled->add($delay);
            }
        }

        return $scheduled;
    }

    /**
     * Cleans old jobs in the table according to the configured lifetime of successful and failed jobs.
     */
    protected function purge()
    {
        if ($this->options->getBuriedLifetime() > static::LIFETIME_UNLIMITED)
		{
            $options = array('delay' => - ($this->options->getBuriedLifetime() * 60));
            $buriedLifetime = $this->parseOptionsToDateTime($options);

			$this->documentManager->createQueryBuilder('SlmQueueDoctrineMongoODM\Document\DefaultQueue')
				->remove()
				->field('finished')->lt($buriedLifetime)
				->field('status')->equals(static::STATUS_BURIED)
				->field('queue')->equals($this->getName())
				->field('finished')->exists(TRUE)
				->getQuery()
				->execute();
        }

        if ($this->options->getDeletedLifetime() > static::LIFETIME_UNLIMITED)
		{
            $options = array('delay' => - ($this->options->getDeletedLifetime() * 60));
            $deletedLifetime = $this->parseOptionsToDateTime($options);

			$this->documentManager->createQueryBuilder('SlmQueueDoctrineMongoODM\Document\DefaultQueue')
				->remove()
				->field('finished')->lt($deletedLifetime)
				->field('status')->equals(static::STATUS_DELETED)
				->field('queue')->equals($this->getName())
				->field('finished')->exists(TRUE)
				->getQuery()
				->execute();
        }
    }
}
