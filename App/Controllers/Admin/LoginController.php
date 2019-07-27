<?php
namespace App\Controllers\Admin;

use Hcode\DB\Sql;
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use App\Controllers\Controller;

class LoginController extends Controller{
  
    
    public function index() {
        
        $url_form = $this->getRouteByName("login_form");
        
        $options = array("data" => array("path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],"url_form" => $url_form ),"header" => false, "footer" => false);
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

}
