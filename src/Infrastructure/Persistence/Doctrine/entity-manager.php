<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

$ormSetup = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__.'/Entities'],
    isDevMode: config('app.env') === 'dev',
);

$connection = DriverManager::getConnection(config('doctrine'), $ormSetup);

return new EntityManager($connection, $ormSetup);