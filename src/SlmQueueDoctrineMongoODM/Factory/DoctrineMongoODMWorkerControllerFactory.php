<?php

namespace SlmQueueDoctrineMongoODM\Factory;

use SlmQueueDoctrineMongoODM\Controller\DoctrineMongoODMWorkerController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * WorkerFactory
 */
class DoctrineMongoODMWorkerControllerFactory implements FactoryInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$serviceLocator = $serviceLocator->getServiceLocator();
		$worker = $serviceLocator->get('SlmQueueDoctrineMongoODM\Worker\DoctrineMongoODMWorker');
		$queuePluginManager = $serviceLocator->get('SlmQueue\Queue\QueuePluginManager');

		return new DoctrineMongoODMWorkerController($worker, $queuePluginManager);
	}
}
