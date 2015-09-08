<?php

namespace SlmQueueDoctrineMongoODM\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DateTime;

/**
 * DefaultQueue
 *
 * @ODM\Document(collection="queue_default")
 * @ODM\Indexes({
 *    @ODM\Index(keys={"status"="asc"})
 * })
 */
class DefaultQueue
{
	/**
	 * @var string
	 *
	 * @ODM\Id
	 */
	protected $id;

	/**
	 * @var string
	 *
	 * @ODM\String
	 */
	private $queue;

	/**
	 * @var string
	 *
	 * @ODM\String
	 */
	private $data;

	/**
	 * @var int
	 *
	 * @ODM\Int
	 */
	private $status;

	/**
	 * @var \DateTime
	 *
	 * @ODM\Date
	 */
	private $created;

	/**
	 * @var \DateTime
	 *
	 * @ODM\Date
	 */
	private $scheduled;


	/**
	 * @var \DateTime
	 *
	 * @ODM\Date
	 */
	private $executed;

	/**
	 * @var \DateTime
	 *
	 * @ODM\Date
	 */
	private $finished;

	/**
	 * @var string
	 *
	 * @ODM\String
	 */
	private $message;

	/**
	 * @var string
	 *
	 * @ODM\String
	 */
	private $trace;


	/**
	 * Get id
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Set queue
	 *
	 * @param string $queue
	 *
	 * @return DefaultQueue
	 */
	public function setQueue($queue)
	{
		$this->queue = $queue;

		return $this;
	}


	/**
	 * Get queue
	 *
	 * @return string
	 */
	public function getQueue()
	{
		return $this->queue;
	}


	/**
	 * Set data
	 *
	 * @param string $data
	 *
	 * @return DefaultQueue
	 */
	public function setData($data)
	{
		$this->data = $data;

		return $this;
	}


	/**
	 * Get data
	 *
	 * @return string
	 */
	public function getData()
	{
		return $this->data;
	}


	/**
	 * Set status
	 *
	 * @param int $status
	 *
	 * @return DefaultQueue
	 */
	public function setStatus($status)
	{
		$this->status = $status;

		return $this;
	}


	/**
	 * Get status
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}


	/**
	 * Set created
	 *
	 * @param \DateTime $created
	 *
	 * @return DefaultQueue
	 */
	public function setCreated(DateTime $created)
	{
		$this->created = clone $created;

		return $this;
	}


	/**
	 * Get created
	 *
	 * @return \DateTime
	 */
	public function getCreated()
	{
		if ($this->created)
		{
			return clone $this->created;
		}

		return null;
	}


	/**
	 * Set scheduled
	 *
	 * @param \DateTime $scheduled
	 *
	 * @return DefaultQueue
	 */
	public function setScheduled(DateTime $scheduled)
	{
		$this->scheduled = clone $scheduled;

		return $this;
	}


	/**
	 * Get scheduled
	 *
	 * @return \DateTime
	 */
	public function getScheduled()
	{
		if ($this->scheduled)
		{
			return clone $this->scheduled;
		}

		return null;
	}


	/**
	 * Set executed
	 *
	 * @param \DateTime $executed
	 *
	 * @return DefaultQueue
	 */
	public function setExecuted(DateTime $executed = null)
	{
		$this->executed = $executed ? clone $executed : null;

		return $this;
	}


	/**
	 * Get executed
	 *
	 * @return \DateTime
	 */
	public function getExecuted()
	{
		if ($this->executed)
		{
			return clone $this->executed;
		}

		return null;
	}


	/**
	 * Set finished
	 *
	 * @param \DateTime $finished
	 *
	 * @return DefaultQueue
	 */
	public function setFinished(DateTime $finished = null)
	{
		$this->finished = $finished ? clone $finished : null;

		return $this;
	}


	/**
	 * Get finished
	 *
	 * @return \DateTime
	 */
	public function getFinished()
	{
		if ($this->finished)
		{
			return clone $this->finished;
		}

		return null;
	}


	/**
	 * Set message
	 *
	 * @param string $message
	 *
	 * @return DefaultQueue
	 */
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
	}


	/**
	 * Get message
	 *
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}


	/**
	 * Set trace
	 *
	 * @param string $trace
	 *
	 * @return DefaultQueue
	 */
	public function setTrace($trace)
	{
		$this->trace = $trace;

		return $this;
	}


	/**
	 * Get trace
	 *
	 * @return string
	 */
	public function getTrace()
	{
		return $this->trace;
	}
}
