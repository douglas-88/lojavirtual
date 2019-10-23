<?php
session_start();
require_once("App/bootstrap.php");

use App\Controllers\Admin\{AdminController,LoginController,UserController};
use Model\DB\Sql;
use Model\Page;
use Model\PageAdmin;
use Model\Model\User;
use Slim\App;
use Slim\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Dotenv\Dotenv;

$dotenv = Dotenv::create(__DIR__);
$dotenv->load();

$app = new App($container);

$app->get('/',"HomeController:index")->setName("Home");
$app->get('/categories/{id}',"SiteCategoryController:index");
$app->get('/produtos/{url}',"SiteProductController:show");

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

    $app->get("[/]", "UserController:index")->setName("users-home");//Lista Usuários
    $app->get("/create", "UserController:create")->setName("user-formCreate");//Formulário para Criar
    $app->get("/{iduser}/delete", "UserController:delete");//Deleta Usuários
    $app->get("/{iduser}", "UserController:update")->setName("user-formUpdate");//Formulário para Editar

    $app->post("/create","UserController:postCreate")->setName("user-postCreate");
    $app->post("/{iduser}","UserController:postUpdate")->setName("user-postUpdate");
    
})->add("Permission:Verify");

//CATEGORIES ROUTE GROUP:
$app->group("/admin/categorias",function(App $app){
    $app->get("[/]","CategoryController:index")->setName("category-home");//Lista Categorias
    $app->get("/create","CategoryController:create")->setName("category_formCreate");//Formulário para Criar
    $app->get("/{idcategory}","CategoryController:update")->setName("category-formUpdate");
    $app->get("/{idcategory}/delete", "CategoryController:delete");//Deleta Categorias
    $app->get("/{idcategory}/produtos", "CategoryController:listProduct")->setName("list_product_cat");
    $app->get("/{idcategory}/produtos/{idproduct}/add", "CategoryController:addProduct");//Deleta Categorias
    $app->get("/{idcategory}/produtos/{idproduct}/remove", "CategoryController:removeProduct");//Deleta Categorias

    $app->post("/create","CategoryController:postCreate")->setName("category_postCreate");
    $app->post("/{idcategory}","CategoryController:postUpdate")->setName("category-postUpdate");
})->add("Permission:Verify");

//PRODUCTS ROUTE GROUP:
$app->group("/admin/produtos",function(App $app){
    $app->get("[/]","ProductController:index")->setName("product-home");//Lista Produtos
    $app->get("/create","ProductController:create")->setName("product_formCreate");//Formulário para criar produto
    $app->get("/{idproduct}","ProductController:update")->setName("product_formUpdate");
    $app->get("/{idproduct}/delete","ProductController:delete");

    $app->post("/create","ProductController:postCreate")->setName("product_postCreate");
    $app->post("/{idproduct}","ProductController:postUpdate")->setName("product_postUpdate");
})->add("Permission:Verify");

$app->run();
?>
