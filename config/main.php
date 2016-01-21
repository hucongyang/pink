<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'timeZone'=>'Asia/Chongqing',
    'name' => '石榴裙系统',
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.extensions.*',
    ),
    'defaultController' => 'Index',
    'modules' => array(
        // uncomment the following to enable the Gii tool
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => '123456',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            'ipFilters' => array('127.0.0.1', '::1'),
        ),
    ),
    // application components
    'components' => array(
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => true,
        ),
        // uncomment the following to use a MySQL database

        'db' => array(
            'connectionString' => 'mysql:host=127.0.0.1;dbname=pink',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => 'yjcsxdl',
            'charset' => 'utf8',
        //'tablePrefix' => 'tbl_',
        ),
        'session' => array(
            'class' => 'CDbHttpSession',
            'autoStart' => true,
            'sessionTableName' => 'tg_session',
            'autoCreateSessionTable' => false,
            'connectionID' => 'db',
        ),
        'errorHandler' => array(
        // use 'site/error' action to display errors
        //'errorAction'=>'site/error',
        ),
        'urlManager' => array(
            'urlFormat' => 'path',
            'rules' => array(
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'trace, info',
                    'categories' => 'system.*',
                ),
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            // uncomment the following to show log messages on web pages
            /*
              array(
              'class'=>'CWebLogRoute',
              ),
             */
            ),
        ),
        'curl' => array(
            'class' => 'ext.Curl',
            'options' => array() //.. additional curl options ../
        )
    ),
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => require(dirname(__FILE__) . '/params.php'),
);