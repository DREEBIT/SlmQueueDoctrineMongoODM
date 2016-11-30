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
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        $worker = $container->get(\SlmQueueDoctrineMongoODM\Worker\DoctrineMongoODMWorker::class);
        $queuePluginManager = $container->get(\SlmQueue\Queue\QueuePluginManager::class);

        return new DoctrineMongoODMWorkerController($worker, $queuePluginManager);
    }


    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator->getServiceLocator(), DoctrineMongoODMWorkerController::class);
    }
}