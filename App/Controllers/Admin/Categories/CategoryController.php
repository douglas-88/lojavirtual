<?php

namespace App\Controllers\Admin\Categories;

use Model\DB\Sql;
use Model\Model\User;
use Model\Model\Category;
use Model\PageAdmin;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CategoryController extends Controller {


    public function index() {
        $category = Category::listAll();
        $url_logout    = $this->getRouteByName("login_out");
        $url_cadastrar = $this->getRouteByName("category_formCreate");
        $home_admin    = $this->getRouteByName("home_admin");

        $options =
        [
            "data" => [
                        "path_admin"  => $_ENV["PATH_TEMPLATE_ADMIN"],
                        "username"    => $_SESSION[User::SESSION]["deslogin"],
                        "categories"  => $category,
                        "appname"     => getenv("APP_NAME"),
                        "url_logout"  => $url_logout,
                        "url_form"    => $url_cadastrar,
                        "home_admin"  => $home_admin
                      ]
        ];
        $template = new PageAdmin($options);

        return $template->setTpl("category/categories");
    }

    public function create(Request $request,Response $response){

        if(!is_null($this->values["container"]->flash->getMessages())):
            $erros = $this->values["container"]->flash->getMessage("mensagem")[0];
            $dados = $this->values["container"]->flash->getMessage("dados")[0];
        endif;

        $url_logout    = $this->getRouteByName("login_out");
        $url_cadastrar = $this->getRouteByName("category_postCreate");
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
        return $template->setTpl("category/categories-create");
    }

    public function postCreate(Request $request,Response $response){
         $categoryName = $request->getParsedBody();
         $category = new Category();

         if(!$category->validateCategory($categoryName)){
             $this->values["container"]->flash->addMessage("mensagem",$category->getMensagens());
             $this->values["container"]->flash->addMessage("dados",$category->getValues());
             $url = $this->getRouteByName("category_formCreate");
         }else{
             $category->save();
             $url = $this->getRouteByName("category-home");
         }


        return $response->withRedirect($url);
    }

    public function update(Request $request,Response $response){
        $url_logout    = $this->getRouteByName("login_out");
        $home_admin    = $this->getRouteByName("home_admin");
        $idcategory      = end(explode("/",$request->getUri()->getPath()));

        if(!is_null($this->values["container"]->flash->getMessages())):
            $erros = $this->values["container"]->flash->getMessage("mensagem")[0];
            $dados = $this->values["container"]->flash->getMessage("dados")[0];
        endif;

        $category = new Category();
        if(!$category->get($idcategory)){
            $url = $this->getRouteByName("category-home");
            return $response->withRedirect($url);
        }
        $category->get($idcategory);
        $options =
            [
                "data" => [
                    "path_admin"  => $_ENV["PATH_TEMPLATE_ADMIN"],
                    "username"    => $_SESSION[User::SESSION]["deslogin"],
                    "appname"     => getenv("APP_NAME"),
                    "url_logout"  => $url_logout,
                    "category"    => $category->getValues(),
                    "home_admin"  => $home_admin,
                    "mensagem"    => ($erros) ?? false,
                    "dados"       => (isset($dados)) ? $dados : $category->getdescategory()
                ]
            ];
        $template = new PageAdmin($options);

        return $template->setTpl("category/categories-update");
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
