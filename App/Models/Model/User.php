<?php

namespace Hcode\Model;

use App\Models\Model\Mailer;
use Hcode\DB\Sql;
use Hcode\Model;
use mysql_xdevapi\Exception;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Filter;
use Zend\Validator;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\I18n\Filter\Alpha;

class User extends Model {

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
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

    public static function getForgot($email,string $urlReset){
        $sql = new Sql();
        $result = $sql->select("
           SELECT * FROM
           tb_persons a
           INNER JOIN 
           tb_users b ON(a.idperson = b.idperson)
           WHERE a.desemail = :email
        ",[":email" => $email]);

       if(count($result) === 0){
           throw new \Exception("Não foi possível recuperar a senha");
           
       }
       else {

           $data = $result[0];

           $recoveryResult = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser,:desip)",[
               ":iduser" => $data["iduser"],
               ":desip"  => $_SERVER["REMOTE_ADDR"]
           ]);

           if(count($recoveryResult) === 0){
               throw new \Exception("Não foi possível recuperar a senha");
           }else{
               $dataRecovery = $recoveryResult[0];

               $code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"],"AES-128-ECB",User::SECRET));

               $link = $urlReset."/code=$code";

               $mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinindo Senha do Site","forgot",[
                   "name" => $data["desperson"],
                   "link" => $link
               ]);
               $mailer->send();

               return $data;
           }
       }

   }

   public static function validForgotDecrypt($code){

       $idRecovery = openssl_decrypt(base64_decode($code),"AES-128-ECB",User::SECRET);
       $sql = new Sql();
       $result = $sql->select("
       SELECT * FROM tb_userspasswordsrecoveries a
        INNER JOIN tb_users b ON(a.iduser = b.iduser)
        INNER JOIN tb_persons c ON(b.idperson = c.idperson)
        WHERE 
           a.idrecovery = :idrecovery
           AND
           a.dtrecovery IS NULL
           AND
           DATE_ADD(a.dtregister,INTERVAL 1 HOUR) >= NOW()
       ",[":idrecovery" => $idRecovery]);

       if(count($result) === 0){
           throw new \Exception("Não foi possível recuperar a senha.");
       }else{
           return $result[0];
       }

   }

   public static function setForgotUsed($idrecovery){
        $sql = new Sql();
        $sql->query("
        UPDATE tb_userspasswordsrecoveries SET dtrecovery = now() WHERE idrecovery = :idrecovery
        ",[":idrecovery" => $idrecovery]);
   }

   public function setPassword($password){
       $password_hash = password_hash($password, PASSWORD_DEFAULT, array("cost" => 12));

       $sql = new Sql();
       $result = $sql->query("
          UPDATE tb_users SET despassword = :password WHERE iduser = :iduser
       ",[":password" => $password_hash,":iduser" => $this->getiduser()]);
   }
}

?>