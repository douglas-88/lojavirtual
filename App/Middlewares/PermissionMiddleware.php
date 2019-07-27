<?php

namespace App\Middlewares;

use Slim\Container;
use Hcode\Model\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class PermissionMiddleware {

    protected $request;
    protected $response;
    protected $container;

    public function __construct(Container $container,Request $request, Response $response, $next) {

        $this->request = $request;
        $this->response = $response;
        $this->container = $container;
    }

    
    public function Verify(Request $request, Response $response, $next) {
        $route      = $request->getAttribute('route');
        $routeName  = $route->getName();
        $groups     = $route->getGroups();
        $methods    = $route->getMethods();
        $arguments  = $route->getArguments();
        
        if(User::verifyLogin()){
            $response = $next($request, $response);
        }else{
            $response = $response->withRedirect($this->getUrl("login_form"));
        }

         return $response;
    }
    
    public function getUrl(string $name){
        $url = $this->container->get('router')->pathFor($name);
        return $url;
    }

    public function __get($key) {

        return $this->values[$key];
    }

    public function __set($key, $value) {

        $this->values[$key] = $values;
    }

}
