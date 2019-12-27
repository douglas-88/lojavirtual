<?php
require_once("vendor/autoload.php");
use App\Controllers\Admin\{AdminController,LoginController};
use Controllers\Site\HomeController;
use App\Controllers\Site\CheckoutController;
use Controllers\Site\SiteCategoryController;
use Controllers\Site\SiteProductController;
use App\Controllers\Site\SiteCartController;
use App\Controllers\Admin\Users\UserController;
use App\Controllers\Admin\Categories\CategoryController;
use App\Controllers\Admin\Products\ProductController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Model\Model\User;
use Slim\Container;
use \App\Middlewares\PermissionMiddleware;
use \Slim\Flash\Messages;


function formatPrice(string $number):string{
    return number_format($number,2,",",".");
}

function checkLogin(bool $inadmin):bool{
    return User::checkLogin($inadmin);
}

function getUserName(){
    $user = User::getFromSession();
    return $user->getdeslogin();
}

$container = new Container();

$container->get('settings')->replace([
                                        'displayErrorDetails' => true,
                                        'determineRouteBeforeAppMiddleware' => true,
                                        'debug' => true,
                                        'addContentLengthHeader' => false
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
$container["ProductController"] = function (Container $container){
    return new ProductController($container);
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

$container["SiteCategoryController"] = function(Container $container){
    return new SiteCategoryController($container);
};

$container["SiteProductController"] = function(Container $container){
    return new SiteProductController($container);
};

$container["SiteCartController"] = function(Container $container){
    return new SiteCartController($container);
};

$container["CheckoutController"] = function(Container $container){
    return new CheckoutController($container);
};


return $container;
?>

