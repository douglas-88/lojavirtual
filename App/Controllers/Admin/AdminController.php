<?php

namespace App\Controllers\Admin;
use Hcode\DB\Sql;
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use App\Controllers\Controller;

class AdminController extends Controller {
   
    
    public function index(){
       //User::verifyLogin(); 
       $url_form = $this->getRouteByName("login_out");
       $options = [
                    "data" => [
                               "path_admin" => $_ENV["PATH_TEMPLATE_ADMIN"],
                               "username"   => $_SESSION[User::SESSION]["deslogin"],
                               "appname"    => getenv("APP_NAME"),
                               "url_logout" => $url_form
                              ]
                 ];
      $template = new PageAdmin($options);
     
      return $template->setTpl("index");
    }
    
    
}
