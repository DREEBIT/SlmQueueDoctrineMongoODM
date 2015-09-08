<?php

namespace SlmQueueDoctrineMongoODM\Factory;

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
    public function createService(ServiceLocatorInterface $serviceLocator, $name = '', $requestedName = '')
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        $config        = $parentLocator->get('Config');
        $queuesOptions = $config['slm_queue']['queues'];
        $options       = isset($queuesOptions[$requestedName]) ? $queuesOptions[$requestedName] : array();
        $queueOptions  = new DoctrineMongoODMOptions($options);

        /** @var $documentManager \Doctrine\ODM\MongoDB\DocumentManager */
        $documentManager  = $parentLocator->get($queueOptions->getConnection());
        $jobPluginManager = $parentLocator->get('SlmQueue\Job\JobPluginManager');

        $queue = new DoctrineMongoODMQueue($documentManager, $queueOptions, $requestedName, $jobPluginManager);

        return $queue;
    }
}
