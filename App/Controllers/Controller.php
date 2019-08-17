<?php

namespace App\Controllers;

abstract class Controller {
    
    protected $values = [];
    
    function __construct(\Slim\Container $c) {
      
        $this->values["request"]   = $c->request;
        $this->values["response"]  = $c->response;
        $this->values["router"]    = $c->router;
        $this->values["container"] = $c;
        
    }
    
    public function getRouteByName(string $name,$args = null){
        if(!is_null($args)):
            return $this->values["router"]->pathFor($name,$args);
        else:
            return $this->values["router"]->pathFor($name);
        endif;
        
    }
    public function __get($key) {
       
        return $this->values[$key];
            
    }
    
    public function __set($key, $value) {
      
        $this->values[$key] = $values;
        
    }
     
    
}
