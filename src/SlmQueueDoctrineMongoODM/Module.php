<?php

namespace SlmQueueDoctrineMongoODM;

use Zend\Loader;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature;

/**
 * SlmQueueDoctrine
 */
class Module implements
    Feature\AutoloaderProviderInterface,
    Feature\ConfigProviderInterface,
    Feature\ConsoleBannerProviderInterface,
    Feature\ConsoleUsageProviderInterface,
    Feature\DependencyIndicatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function getAutoloaderConfig()
    {
        return array(
            Loader\AutoloaderFactory::STANDARD_AUTOLOADER => array(
                Loader\StandardAutoloader::LOAD_NS => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleBanner(AdapterInterface $console)
    {
        return 'SlmQueueDoctrineMongoODM';
    }

    /**
     * {@inheritDoc}
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return array(
            'queue doctrine-mongo-odm <queueName> --start' => 'Process the jobs',
            'queue doctrine-mongo-odm <queueName> --recover [--executionTime=]' => 'Recover long running jobs',

            array('<queueName>', 'Queue\'s name to process'),
            array('<executionTime>', 'Time (in minutes) after which the job gets recovered'),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getModuleDependencies()
    {
        return array('DoctrineMongoODMModule', 'SlmQueue');
    }
}
