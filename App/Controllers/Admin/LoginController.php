<?php
namespace App\Controllers\Admin;

use Model\DB\Sql;
use Model\Page;
use Model\PageAdmin;
use Model\Model\User;
use App\Controllers\Controller;
use Rain\Tpl\Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class LoginController extends Controller{
  
    
    public function index() {
        
        $url_form = $this->getRouteByName("login_form");
        $forgot_form_url = $this->getRouteByName("forgot_form");
        $options = [
                      "data" => [
                                   "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                                   "url_form" => $url_form,
                                   "forgot_form_url" => $forgot_form_url
                                ],
                      "header" => false,
                      "footer" => false
                   ];
        $template = new PageAdmin($options);
        $template->setTpl("login");
    }

    public function login() {
        
        $post = $this->values["request"]->getParsedBody();
        
        User::login($post["login"], $post["password"]);
        $url = $this->getRouteByName("home_admin");
        return $this->values["response"]->withHeader('Location', $url);
    }

    public function logout() {

        User::logout();
        $url = $this->getRouteByName('login_form');
        return $this->values["response"]->withHeader('Location', $url);
    }

    public function forgot(){

        $url_form = $this->getRouteByName("forgot_post");
        
        $options = [
                      "data" => [
                                    "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                                     "url_form" => $url_form
                                ],
                      "header" => false,
                      "footer" => false
                   ];
        $template = new PageAdmin($options);
        $template->setTpl("forgot");

    }

    public function forgotPost(Request $request,Response $response){

      $arguments = $request->getParsedBody();
      $urlBase = $request->getUri()->getBaseUrl();
      $urlReset = $this->values["router"]->pathFor("forgot-reset",["code" => "123"]);
      $urlConcat = $urlBase.$urlReset;
      $urlFormat = substr($urlConcat,0,strrpos($urlConcat,"/"));

      $user = User::getForgot($arguments["email"],$urlFormat);
      $url = $this->getRouteByName('forgot-sent');
      return $this->values["response"]->withHeader('Location', $url);
     
    }

    public function forgotSent(){

        $options = [
            "data" => [
                "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"]
            ],
            "header" => false,
            "footer" => false
        ];
        $template = new PageAdmin($options);
        $template->setTpl("forgot-sent");
    }

    /**
     * @param Request $request
     * @param Response $response
     * Ao se clicar no Link de Reset Password no e-mail, chamará esse método:
     */
    public function forgotReset(Request $request,Response $response){


        $getCode = substr($request->getUri()->getPath(),strrpos($request->getUri()->getPath(),"code")+strlen("code="),strlen($request->getUri()->getPath()));
        $user = User::validForgotDecrypt($getCode);
        $url_form = $this->getRouteByName("forgot-resetPost");
        $options = [
            "data" => [
                "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                "name"       => $user["desperson"],
                "code"       => $getCode,
                "urlForm"    => $url_form
            ],
            "header" => false,
            "footer" => false
        ];
        $template = new PageAdmin($options);
        $template->setTpl("forgot-reset");
    }

    public function forgotResetPost(Request $request,Response $response){
        $code     = $request->getParsedBody()["code"];
        $password = $request->getParsedBody()["password"];

        $forgot = User::validForgotDecrypt($code);
        User::setForgotUsed($forgot["idrecovery"]);

        $user = new User();
        $user->get((int) $forgot["iduser"]);
        $user->setPassword($password);
        $url_form = $this->getRouteByName("login_form");
        $options = [
            "data" => [
                "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                "urlForm"    => $url_form
            ],
            "header" => false,
            "footer" => false
        ];
        $template = new PageAdmin($options);
        $template->setTpl("forgot-reset-success");
    }
}
