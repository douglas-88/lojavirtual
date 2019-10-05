<?php

namespace App\Controllers\Admin\Products;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Product;
use Model\PageAdmin;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Model\Model\Category;

class ProductController extends Controller {


    public function index() {
        $products = Product::listAll();
        $url_logout    = $this->getRouteByName("login_out");
        $url_cadastrar = $this->getRouteByName("product_formCreate");

        $home_admin    = $this->getRouteByName("home_admin");
        $routeHome     = $this->getRouteByName("Home");


        $options =
        [
            "data" => [
                        "path_admin"  => $_ENV["PATH_TEMPLATE_ADMIN"],
                        "username"    => $_SESSION[User::SESSION]["deslogin"],
                        "products"    => $products,
                        "appname"     => getenv("APP_NAME"),
                        "url_logout"  => $url_logout,
                        "url_form"    => $url_cadastrar,
                        "home_admin"  => $home_admin,
                        "urlHome"     => $routeHome

                      ]
        ];
        $template = new PageAdmin($options);

        return $template->setTpl("product/products");
    }

    public function create(Request $request,Response $response){

        if(!is_null($this->values["container"]->flash->getMessages())):
            $erros = $this->values["container"]->flash->getMessage("mensagem")[0];
            $dados = $this->values["container"]->flash->getMessage("dados")[0];
        endif;

        $url_logout    = $this->getRouteByName("login_out");
        $url_cadastrar = $this->getRouteByName("product_postCreate");
        $options =
            [
                "data" => [
                    "path_admin"  => $_ENV["PATH_TEMPLATE_ADMIN"],
                    "mensagem"    => ($erros) ?? false,
                    "dados"       => $dados,
                    "username"    => $_SESSION[User::SESSION]["deslogin"],
                    "appname"     => getenv("APP_NAME"),
                    "url_logout"  => $url_logout,
                    "url_form"    => $url_cadastrar
                ]
            ];

        $template = new PageAdmin($options);
        return $template->setTpl("product/products-create");
    }

    public function postCreate(Request $request,Response $response){
         $post = $request->getParsedBody();
         $file = $request->getUploadedFiles();

         $product = new Product();

         if(!$product->validateProduct($post,$file["pathphoto"])){

             $this->values["container"]->flash->addMessage("mensagem",$product->getMensagens());
             $this->values["container"]->flash->addMessage("dados",$product->getValues());
             $url = $this->getRouteByName("product_formCreate");

         }else{
             $product->save();
             $url = $this->getRouteByName("product-home");
         }


        return $response->withRedirect($url);
    }

    public function update(Request $request,Response $response){

        $url_logout    = $this->getRouteByName("login_out");
        $home_admin    = $this->getRouteByName("home_admin");
        $idproduct     = end(explode("/",$request->getUri()->getPath()));

        if(!is_null($this->values["container"]->flash->getMessages())):
            $erros = $this->values["container"]->flash->getMessage("mensagem")[0];
            $dados = $this->values["container"]->flash->getMessage("dados")[0];
        endif;

        $url_logout    = $this->getRouteByName("login_out");
        $url_cadastrar = $this->getRouteByName("product_postUpdate",["idproduct"=>$idproduct]);

        $product = new Product();
        if(!$product->get($idproduct)){
            $url = $this->getRouteByName("product-home");
            return $response->withRedirect($url);
        }
        $product->get($idproduct);

        $options =
            [
                "data" => [
                    "path_admin"  => $_ENV["PATH_TEMPLATE_ADMIN"],
                    "mensagem"    => ($erros) ?? false,
                    "username"    => $_SESSION[User::SESSION]["deslogin"],
                    "appname"     => getenv("APP_NAME"),
                    "url_logout"  => $url_logout,
                    "url_form"    => $url_cadastrar,
                    "product"     => $product->getValues()
                ]
            ];

        $template = new PageAdmin($options);

        return $template->setTpl("product/products-update");
    }

    public function postUpdate(Request $request,Response $response){
        $categoryName = $request->getParsedBodyParam("descategory");
        $idcategory  = end(explode("/",$request->getUri()->getPath()));
        $category = new Category();
        $category->setData(["idcategory" => $idcategory,"descategory" => $categoryName]);

        if(!$category->validateCategory(["descategory" => $category->getdescategory()])){
            $this->values["container"]->flash->addMessage("mensagem",$category->getMensagens());
            $this->values["container"]->flash->addMessage("dados",$category->getValues());
            $url = $this->getRouteByName("category-formUpdate",["idcategory" => $category->getidcategory()]);

        }else{
            $category->update();
            $url = $this->getRouteByName("category-home");
        }


        return $response->withRedirect($url);
    }

    public function delete(Request $request,Response $response) {

        $idcategory  = explode("/",$request->getUri()->getPath())[3];

        $category = new Category();
        $category->get($idcategory);
        $category->delete();
        $url = $this->getRouteByName("category-home");
        return $response->withRedirect($url);
    }

}
