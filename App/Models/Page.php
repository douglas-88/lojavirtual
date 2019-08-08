<?php

namespace Model;

use Rain\Tpl;

/**
 * Esta Classe é capaz de criar páginas HTML usando o RAINTPL para gerar templates
 *
 * @author Douglas
 */
class Page {

    private $tpl;
    private $options = array();
    private $defaults = array(
        "header" => true,
        "footer" => true,
        "data" => array()
    );

    function __construct($opts = array(),$tpl_dir = "views") {

        $this->options = array_merge($this->defaults, $opts);
        // Onde estarão localizadas as pastas das views:
        $config = array(
            "tpl_dir" => getenv("BASE_DIR") . DIRECTORY_SEPARATOR . $tpl_dir . DIRECTORY_SEPARATOR,
            "cache_dir" => getenv("BASE_DIR") . DIRECTORY_SEPARATOR . "views-cache" . DIRECTORY_SEPARATOR,
            "debug" => false
        );

        Tpl::configure($config);

        $this->tpl = new Tpl;
        //Se existir Parâmetros a serem passados a view, então configure-os:
        if ($this->options["data"])
            $this->setData($this->options["data"]);
        
        //Se a chave "header" estiver como TRUE então é para ser renderizada:
        if ($this->options["header"] === TRUE)
            $this->tpl->draw("header", FALSE);
    }

    private function setData($data = array()) {
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }
    /**
     * Método responsável por invocar uma VIEW.
     * @param type $tplname
     * @param type $data
     * @param type $returnHtml
     * @return type
     */
    public function setTpl($tplname,$data = array(),$returnHtml = FALSE) {
        if ($data)$this->setData($data);
        return $this->tpl->draw($tplname,$returnHtml);
    }
    
    public function __destruct(){
        if($this->options["footer"] === TRUE) $this->tpl->draw ("footer",FALSE);
    }

}
