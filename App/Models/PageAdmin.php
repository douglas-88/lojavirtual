<?php
namespace Hcode;

use Rain\Tpl;
use Hcode\Page;

/**
 * Esta Classe é capaz de criar páginas HTML usando o RAINTPL para gerar templates
 *
 * @author Douglas
 */
class PageAdmin extends Page{
    
    function __construct($opts = array(),$tpl_dir = "views".DIRECTORY_SEPARATOR."admin") {
        parent::__construct($opts,$tpl_dir);
    }

    
}
