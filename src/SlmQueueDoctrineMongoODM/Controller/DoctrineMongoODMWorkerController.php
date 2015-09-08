<?php

namespace SlmQueueDoctrineMongoODM\Controller;

use SlmQueue\Controller\AbstractWorkerController;
use SlmQueue\Controller\Exception\WorkerProcessException;
use SlmQueue\Exception\ExceptionInterface;
use SlmQueueDoctrineMongoODM\Queue\DoctrineQueueInterface;

/**
 * Worker controller
 */
class DoctrineMongoODMWorkerController extends AbstractWorkerController
{
	/**
	 * Recover long running jobs
	 *
	 * @return string
	 * @throws WorkerProcessException
	 */
	public function recoverAction()
	{
		$queueName = $this->params('queue');
		$executionTime = $this->params('executionTime', 0);

		/** @var $queueManager \SlmQueue\Queue\QueuePluginManager */
		$queueManager = $this->getServiceLocator()->get('SlmQueue\Queue\QueuePluginManager');
		$queue = $queueManager->get($queueName);

		if (!$queue instanceof DoctrineQueueInterface)
		{
			return sprintf("\nQueue % does not support the recovering of job\n\n", $queueName);
		}

		try
		{
			$count = $queue->recover($executionTime);
		}
		catch (ExceptionInterface $exception)
		{
			throw new WorkerProcessException("An error occurred", $exception->getCode(), $exception);
		}

		return sprintf(
			"\nWork for queue %s is done, %s jobs were recovered\n\n",
			$queueName,
			$count
		);
	}
}
