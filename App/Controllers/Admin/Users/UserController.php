<?php

namespace App\Controllers\Admin\Users;

use Hcode\DB\Sql;
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use App\Controllers\Controller;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserController extends Controller {

    public function index() {

        //User::verifyLogin();
        $user = User::listAll();
        $url_form = $this->getRouteByName("login_out");
        $options = [
            "data" => [
                "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                "username" => $_SESSION[User::SESSION]["deslogin"],
                "users" => $user,
                "appname" => getenv("APP_NAME"),
                "url_logout" => $url_form
            ]
        ];
        $template = new PageAdmin($options);

        return $template->setTpl("user/users");
    }

    public function create(Request $request,Response $response) {
        //User::verifyLogin();
        if(!is_null($this->values["container"]->flash->getMessages())):
            $erros = $this->values["container"]->flash->getMessage("mensagem")[0];
            $dados = $this->values["container"]->flash->getMessage("dados")[0];
        endif;
       
        $url_logout = $this->getRouteByName("login_out");
        $url_user_post_create = $this->getRouteByName("user-postCreate");
        $options = [
                     "data" =>["path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                     "mensagem" => ($erros) ?? false ,
                     "dados" => $dados,
                     "url_user_post_create"=> $url_user_post_create,
                     "url_logout" => $url_logout,
                     "username" => $_SESSION[User::SESSION]["deslogin"]]
                   ];
        $template = new PageAdmin($options);
       
        return $template->setTpl("user/users-create");
    }
    
    public function postCreate(Request $request,Response $response){
        //User::verifyLogin();

        $user = new User();
        $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
       
        if(!$user->validateUser($_POST)){
            $this->values["container"]->flash->addMessage("mensagem",$user->getMensagens());
            $this->values["container"]->flash->addMessage("dados",$user->getValues());
            $url = $this->getRouteByName("user-formCreate");
        }else{
            $user->save();
            $url = $this->getRouteByName("users-home");
        }
        return $response->withRedirect($url);
    }

    public function delete(Request $request,Response $response) {
        //User::verifyLogin();

        $route = $request->getAttribute('route');
        $iduser = $route->getArgument("iduser");

        $user = new User();
        $user->get($iduser);
        $user->delete();
        $url = $this->getRouteByName("users-home");
        return $response->withRedirect($url);
    }

    public function update(Request $request,Response $response) {
        //User::verifyLogin();
         if(!is_null($this->values["container"]->flash->getMessages())):
            $erros = $this->values["container"]->flash->getMessage("mensagem")[0];
            $dados = $this->values["container"]->flash->getMessage("dados")[0];
        endif;
        
        $url_logout = $this->getRouteByName("login_out");
        $route = $request->getAttribute('route');
        $iduser = $route->getArgument("iduser");
        $user = new User();
      
        $options = [
                      "data" => [
                                   "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                                   "username" => $_SESSION[User::SESSION]["deslogin"],
                                   "url_logout" =>$url_logout,
                                   "user" => (isset($dados)) ? $dados : $user->get((int) $iduser),
                                   "mensagem" => ($erros) ?? false ,
                                   "dados" => $dados
                                ]
                   ];
        
        $template = new PageAdmin($options);
        return $template->setTpl("user/users-update");
    }
    
    public function postUpdate(Request $request,Response $response){
        //User::verifyLogin();

        $route = $request->getAttribute('route');
        $iduser = $route->getArgument("iduser");

        $user = new User();
        $user->setiduser($iduser);
        $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
        if(!$user->validateUser($_POST)){
            $this->values["container"]->flash->addMessage("mensagem",$user->getMensagens());
            $this->values["container"]->flash->addMessage("dados",$user->getValues());
            $url = $this->getRouteByName("user-formUpdate",["iduser" => $iduser]);
        }else{
            $user->setiduser($iduser);
            $user->update();
            $url = $this->getRouteByName("users-home");
        }
        return $response->withRedirect($url);
       
    }

}
