<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Filter;
use Zend\Validator;
use Zend\InputFilter\Factory as InputFilterFactory;
;

use Zend\I18n\Filter\Alpha;

class User extends Model {

    const SESSION = "User";
    protected $mensagens;

    public static function login($login, $senha) {

        $sql = new Sql();
        $query = "SELECT * FROM tb_users WHERE deslogin = :LOGIN";
        $result = $sql->select($query, array("LOGIN" => $login));
        if (empty($result)):
            throw new \Exception("Usuário Inexistente");
        endif;

        $data = $result[0];

        if (password_verify($senha, $data["despassword"])):
            $user = new User();
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();

            return $user;
        else:
            throw new \Exception("Senha inválida");
        endif;
    }

    public static function verifyLogin($inadmin = TRUE) {
        //Se o Usuário NÂO estiver Logado, redirecione-o para a página de Login:
        if (
                !isset($_SESSION[User::SESSION])
                OR
                empty($_SESSION[User::SESSION])
                OR ! (int) $_SESSION[User::SESSION]["iduser"] > 0
                OR
                (bool) $_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ) {
            /*
              header("Location: /admin/login");
              exit;
             * 
             */
            return false;
        } else {
            return true;
        }
    }

    public static function logout() {
        $_SESSION[User::SESSION] = NULL;
    }

    public static function listAll() {

        $sql = new Sql();
        $query = "SELECT * FROM tb_users a INNER JOIN tb_persons b ON (a.idperson = b.idperson) ORDER BY a.deslogin";
        $result = $sql->select($query);

        return $result;
    }

    public function save() {

        $sql = new Sql();


        $this->setdespassword(password_hash($this->getdespassword(), PASSWORD_DEFAULT, array("cost" => 12)));

        $results = $sql->select("CALL sp_users_save(:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", array(
            ":desperson" => $this->getdesperson(),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => $this->getdespassword(),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));
        return $this->setData($results[0]);
    }

    public function get(int $iduser) {
        $sql = new Sql();
        $result = $sql->select("SELECT iduser,desperson,deslogin,nrphone,desemail,despassword,inadmin FROM tb_users a INNER JOIN tb_persons b ON(a.idperson = b.idperson) WHERE iduser =:ID", array(
            ":ID" => $iduser
        ));


        $this->setData($result[0]);

        return $this->getValues();
    }

    public function Update() {

        $sql = new Sql();  
        $results = $sql->select(
                "CALL sp_usersupdate_save(:iduser,:desperson,:deslogin,:despassword,:desemail,:nrphone,:inadmin)", [
            ":iduser" => $this->getiduser(),
            ":desperson" => $this->getdesperson(),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => $this->getdespassword(),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
                ]
        );
        
        $this->setData($results[0]);
    }

    public function Delete() {

        $sql = new Sql();
        $results = $sql->select("CALL sp_users_delete(:iduser)", [
            ":iduser" => $this->getiduser()
        ]);
    }

    protected function createInputFilter() {

        $nome = [
                    "name" => "desperson",
                    "required" => true,
                    "validators" =>
                    [
                        [
                            "name" => "StringLength",
                            "options" => [
                                            "min" => 3,
                                            "max" => 60,
                                            "message" => "O nome deve ter no mínimo 3, e no máximo 60 letras"
                                          ]
                        ]
                    ],
                    "filters" =>
                    [
                        [
                            "name" => "striptags"
                        ]
                    ]
                ]
        ;

        $login = [
                    "name" => "deslogin",
                    "required" => true,
                    "validators" =>
                    [
                        [
                            "name" => "StringLength",
                            "options" => [
                                            "min" => 3,
                                            "max" => 10,
                                            "message" => "O login deve ter no mínimo 3, e no máximo 10 letras"
                                         ]
                        ]
                    ],
                    "filters" =>
                    [
                        [
                            "name" => "striptags"
                        ]
                    ]
        ];
        $telefone = [
                    "name" => "nrphone",
                    "required" => true,
                    "validators" =>
                    [
                        [
                            "name" => "StringLength",
                            "options" => [
                                            "min" => 9,
                                            "max" => 11,
                                            "message" => "O telefone deve ter no mínimo 3, e no máximo 11 letras"
                                        ]
                        ]
                    ],
                    "filters" =>
                    [
                        ["name" => "Digits"],
                        ["name" => "striptags"]
                    ]
        ];

        $email = [
                    "name" => "desemail",
                    "required" => true,
                    "validators" =>
                    [
                        [
                            "name" => "EmailAddress",
                            "options" => ["message" => "Favor, informe um <b>E-MAIL</b> válido"],
                        ],
                        [
                            "name" => "StringLength",
                            "options" => [
                                            "min" => 3,
                                            "max" => 30,
                                            "message" => "O e-mail deve ter no mínimo 3, e no máximo 30 caracteres."
                                        ]
                        ]
                    ],
                    "filters" =>
                    [
                        ["name" => "striptags"]
                    ]
        ];
        $senha = [
                    "name" => "despassword",
                    "required" => true,
                    "validators" =>
                    [
                        [
                            "name" => "StringLength",
                            "options" => [
                                            "min" => 8,
                                            "max" => 8,
                                            "message" => "A senha deve ter no mínimo 3, e no máximo 8 caracteres."
                                        ]
                        ]
                    ],
                    "filters" =>
                    [
                        ["name" => "striptags"]
                    ]
        ];
       
        $factory = new InputFilterFactory();
        $inputFilterNewUser = $factory->createInputFilter([$nome,$login,$telefone,$email]);

        return $inputFilterNewUser;
    }

    public function validateUser($data) {
        $inputFilter = $this->createInputFilter();
        $inputFilter->setData($data);
       
        if ($inputFilter->isValid()) {
            
            $this->setdesperson($inputFilter->getValue("desperson"));
            $this->setdeslogin($inputFilter->getValue("deslogin"));
            $this->setnrphone($inputFilter->getValue("nrphone"));
            $this->setdesemail($inputFilter->getValue("desemail"));
            $this->setdespassword($data["despassword"]);
            $this->setinadmin($data["inadmin"]);
            return true;
        } else {
            $this->setdesperson($inputFilter->getValue("desperson"));
            $this->setdeslogin($inputFilter->getValue("deslogin"));
            $this->setnrphone($inputFilter->getValue("nrphone"));
            $this->setdesemail($inputFilter->getValue("desemail"));
            $this->setdespassword($data["despassword"]);
            $this->setinadmin($data["inadmin"]);
            
            $messages["erros"] = $inputFilter->getMessages();
            $this->mensagens = $messages;
            return false;
        }
    }
    
    public function getMensagens(){
        return $this->mensagens;
    }

}

?>