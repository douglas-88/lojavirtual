<?php
require_once("vendor/autoload.php");
use App\Controllers\Admin\{AdminController,LoginController};
use \App\Controllers\Site\HomeController;
use App\Controllers\Admin\Users\UserController;
use App\Controllers\Admin\Categories\CategoryController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Model\Model\User;
use Slim\Container;
use \App\Middlewares\PermissionMiddleware;
use \Slim\Flash\Messages;
/*
use DI\Container;
use DI\ContainerBuilder;
$containerBuilder = new ContainerBuilder();
/** OPCIONAIS: **/
/*
$containerBuilder->useAutowiring(false);
$containerBuilder->addDefinitions(__DIR__ . '/config.php');
$container = $containerBuilder->build();
/** --- **/
/*
return $container;
*/
$container = new Container();

$container->get('settings')->replace([
                                        'displayErrorDetails' => true,
                                        'determineRouteBeforeAppMiddleware' => true,
                                        'debug' => true
                                    ]);

$container["HomeController"] = function(Container $container){
    return new HomeController($container);
};

$container["LoginController"] = function(Container $container){
    return new LoginController($container);
};
$container["AdminController"] = function(Container $container){
    return new AdminController($container);
};

$container["CategoryController"] = function (Container $container){
    return new CategoryController($container);
};

$container["User"] = function(){
    return new User();
};

$container["UserController"] = function(Container $container){
    return new UserController($container);
};

$container["Permission"] = function(Container $container){
    return new PermissionMiddleware($container,$container->get("request"),$container->get("response"),function(){});
};
$container["flash"] = function(){
    return new Messages();
};
return $container;
?>

