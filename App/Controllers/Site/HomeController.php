<?php

namespace App\Controllers\Site;

use App\Controllers\Controller;
use Hcode\DB\Sql;
use Hcode\Page;

class HomeController extends Controller {

    public function index() {

        $options = array("data" => array("path_loja" => $_ENV["PATH_TEMPLATE_LOJA"]));
        $template = new Page($options);
        $template->setTpl("index");
    }

}
