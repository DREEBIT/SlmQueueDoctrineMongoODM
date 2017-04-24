<?php

namespace SlmQueueDoctrineMongoODM\Factory;

use Interop\Container\ContainerInterface;
use SlmQueue\Job\JobPluginManager;
use SlmQueueDoctrineMongoODM\Options\DoctrineMongoODMOptions;
use SlmQueueDoctrineMongoODM\Queue\DoctrineMongoODMQueue;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * DoctrineQueueFactory
 */
class DoctrineMongoODMQueueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = NULL)
    {
        $config = $container->get('config');
        $queuesOptions = $config['slm_queue']['queues'];
        $options = array_merge_recursive(
            isset($queuesOptions[$requestedName]) ? $queuesOptions[$requestedName] : [],
            $options
        );
        $queueOptions = new DoctrineMongoODMOptions($options);

        /** @var $documentManager \Doctrine\ODM\MongoDB\DocumentManager */
        $documentManager = $container->get($queueOptions->getConnection());
        $jobPluginManager = $container->get(JobPluginManager::class);

        $queue = new DoctrineMongoODMQueue($documentManager, $queueOptions, $requestedName, $jobPluginManager);

        return $queue;
    }


    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $name = '', $requestedName = '')
    {
        return $this($serviceLocator->getServiceLocator(), $requestedName);
    }
}
