<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {

    $routes->connect('/', ['controller' => 'Pages', 'action' => 'intro']);
    $routes->connect('/idp-info', ['controller' => 'Pages', 'action' => 'idpInfo']);
    $routes->connect('/sep-info', ['controller' => 'Pages', 'action' => 'sepInfo']);
    $routes->connect('/sep-info-metadata', ['controller' => 'Pages', 'action' => 'sepMetadata']);

    $routes->connect('/PrivateAccess', ['controller' => 'Pages', 'action' => 'PrivateAccess']);
    $routes->connect('/ExternalLogin', ['controller' => 'Pages', 'action' => 'ExternalLogin']);
    $routes->connect('/ExternalLogout', ['controller' => 'Pages', 'action' => 'ExternalLogout']);
    $routes->connect('/SeP/Konfigurace.xml', ['controller' => 'Pages', 'action' => 'SePConfiguration']);

    $routes->connect('/example/step1', ['controller' => 'Pages', 'action' => 'exampleStep1']);
    $routes->connect('/example/step2', ['controller' => 'Pages', 'action' => 'exampleStep2']);
    $routes->connect('/example/step3', ['controller' => 'Pages', 'action' => 'ExternalLogin']);
    $routes->connect('/example/test', ['controller' => 'Pages', 'action' => 'test']);
});
