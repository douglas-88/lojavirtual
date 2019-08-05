<?php
session_start();
require_once("App/bootstrap.php");

use App\Controllers\Admin\{AdminController,LoginController,UserController};
use Hcode\DB\Sql;
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use Slim\App;
use Slim\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Dotenv\Dotenv;

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

$app = new App($container);

$app->get('/',"HomeController:index");

$app->group("/admin", function(App $app){
    
    $app->get("[/]", "AdminController:index")->setName("home_admin")->add("Permission:Verify");
    $app->get('/login',"LoginController:index")->setName("login_form");
    $app->get('/logout',"LoginController:logout")->setName("login_out");
    $app->get("/forgot","LoginController:forgot")->setName("forgot_form");
    $app->get("/forgot/sent","LoginController:forgotSent")->setName("forgot-sent");
    $app->get("/forgot/reset/{code}","LoginController:forgotReset")->setName("forgot-reset");

    $app->post("/login","LoginController:login")->setName("login_post");
    $app->post("/forgot","LoginController:forgotPost")->setName("forgot_post");
    $app->post("/forgot/reset","LoginController:forgotResetPost")->setName("forgot-resetPost");
    
});

//USERS ROUTE GROUP:
$app->group("/admin/users", function(App $app) {

    $app->get("[/]", "UserController:index")->setName("users-home");
    $app->get("/create", "UserController:create")->setName("user-formCreate");
    $app->get("/{iduser}/delete", "UserController:delete");
    $app->get("/{iduser}", "UserController:update")->setName("user-formUpdate");

    $app->post("/create","UserController:postCreate")->setName("user-postCreate");
    $app->post("/{iduser}","UserController:postUpdate")->setName("user-postUpdate");
    
})->add("Permission:Verify");


$app->run();
?>
