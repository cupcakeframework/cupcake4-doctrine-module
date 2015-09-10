<?php

namespace Cupcake\DoctrineModule\Factory;

use Cupcake\Config\ConfigManager;
use Cupcake\Service\ServiceManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 * @author Ricardo Fiorani
 */
class EntityManagerFactory
{

    /**
     * @param ServiceManager $serviceManager
     * @return EntityManager
     */
    public function __invoke(ServiceManager $serviceManager)
    {
        /* @var $configManager ConfigManager */
        $configManager = $serviceManager->get('ConfigManager');
        $databaseConfig = $configManager->get('database');
        $doctrineConfig = $configManager->get('doctrine');

        $connectionParams = array(
            'dbname' => $databaseConfig['dbname'],
            'user' => $databaseConfig['user'],
            'password' => $databaseConfig['password'],
            'host' => $databaseConfig['host'],
            'driver' => 'pdo_mysql',
            'charset' => 'utf8',
            'driverOptions' => array(
                1002 => 'SET NAMES utf8'
            ),
        );

        $entitiesPaths = $doctrineConfig['entitiesPaths'];
        $proxyDir = $doctrineConfig['proxyDir'];
        $isDevMode = $configManager->get('debug');

        $setupConfig = Setup::createAnnotationMetadataConfiguration($entitiesPaths, $isDevMode, $proxyDir);
        $setupConfig->addCustomStringFunction('rand', 'Mapado\MysqlDoctrineFunctions\DQL\MysqlRand');
        $setupConfig->addCustomStringFunction('round', 'Mapado\MysqlDoctrineFunctions\DQL\MysqlRound');
        $setupConfig->addCustomStringFunction('date', 'Mapado\MysqlDoctrineFunctions\DQL\MysqlDate');
        $setupConfig->addCustomStringFunction('date_format', 'Mapado\MysqlDoctrineFunctions\DQL\MysqlDateFormat');

        $entityManager = EntityManager::create($connectionParams, $setupConfig);
        $platform = $entityManager->getConnection()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        return $entityManager;
    }

}
