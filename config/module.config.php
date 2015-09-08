<?php

return array(
	'service_manager' => array(
		'factories' => array(
			'SlmQueueDoctrineMongoODM\Worker\DoctrineMongoODMWorker' => 'SlmQueue\Factory\WorkerFactory',
		)
	),
	'controllers' => array(
		'factories' => array(
			'SlmQueueDoctrineMongoODM\Controller\DoctrineMongoODMWorkerController' => 'SlmQueueDoctrineMongoODM\Factory\DoctrineMongoODMWorkerControllerFactory',
		),
	),
	'router' => array(
		'routes' => array(
			'slm-queue-doctrine-mongo-odm-worker' => array(
				'type' => 'Segment',
				'options' => array(
					'route' => '/queue/doctrine-mongo-odm/:queue',
					'constraints' => array(
						'queue' => '[a-zA-Z0-9]*'
					),
					'defaults' => array(
						'controller' => 'SlmQueueDoctrineMongoODM\Controller\DoctrineMongoODMWorkerController',
						'action' => 'process'
					)
				)
			),
		),
	),
	'console' => array(
		'router' => array(
			'routes' => array(
				'slm-queue-doctrine-mongo-odm-worker' => array(
					'type' => 'Simple',
					'options' => array(
						'route' => 'queue doctrine-mongo-odm <queue> [--timeout=] --start',
						'defaults' => array(
							'controller' => 'SlmQueueDoctrineMongoODM\Controller\DoctrineMongoODMWorkerController',
							'action' => 'process'
						),
					),
				),
				'slm-queue-doctrine-mongo-odm-recover' => array(
					'type' => 'Simple',
					'options' => array(
						'route' => 'queue doctrine-mongo-odm <queue> --recover [--executionTime=]',
						'defaults' => array(
							'controller' => 'SlmQueueDoctrineMongoODM\Controller\DoctrineMongoODMWorkerController',
							'action' => 'recover'
						),
					),
				),
			),
		),
	),
	'slm_queue' => array(
		/**
		 * Worker Strategies
		 */
		'worker_strategies' => array(
			'default' => array(
				'SlmQueueDoctrineMongoODM\Strategy\IdleNapStrategy' => array('nap_duration' => 1),
				'SlmQueueDoctrineMongoODM\Strategy\ClearObjectManagerStrategy'
			),
			'queues' => array(),
		),
		/**
		 * Strategy manager configuration
		 */
		'strategy_manager' => array(
			'invokables' => array(
				'SlmQueueDoctrineMongoODM\Strategy\IdleNapStrategy' => 'SlmQueueDoctrineMongoODM\Strategy\IdleNapStrategy',
				'SlmQueueDoctrineMongoODM\Strategy\ClearObjectManagerStrategy' => 'SlmQueueDoctrineMongoODM\Strategy\ClearObjectManagerStrategy'
			)
		),
	),
	'doctrine' => array(
		'driver' => array(
			'SlmQueueDoctrineMongoODM' => array(
				'class' => 'Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver',
				'paths' => array(__DIR__ . '/../src/SlmQueueDoctrineMongoODM/Document'),
			),
			'odm_default' => array(
				'drivers' => array(
					'SlmQueueDoctrineMongoODM\Document' => 'SlmQueueDoctrineMongoODM',
				)
			)
		)
	),
);
