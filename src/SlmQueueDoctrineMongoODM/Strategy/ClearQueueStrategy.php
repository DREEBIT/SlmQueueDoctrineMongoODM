<?php

namespace SlmQueueDoctrineMongoODM\Strategy;

use DoctrineModule\Persistence\ObjectManagerAwareInterface;
use SlmQueue\Strategy\AbstractStrategy;
use SlmQueue\Worker\WorkerEvent;
use Zend\EventManager\EventManagerInterface;

class ClearQueueStrategy extends AbstractStrategy
{
	/**
	 * {@inheritDoc}
	 */
	protected $state = '0 jobs processed';


	/**
	 * {@inheritDoc}
	 */
	public function attach(EventManagerInterface $events, $priority = 1)
	{
		$this->listeners[] = $events->attach(
			WorkerEvent::EVENT_PROCESS_IDLE,
			array($this, 'onStopConditionCheck'),
			$priority
		);
	}

	public function onStopConditionCheck(WorkerEvent $event)
	{
		$event->exitWorkerLoop();
	}
}
