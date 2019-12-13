<?php

namespace Model\Model;

use Model\DB\Sql;
use Model\Model;

/**
 * Class Name: Cart
 * Descrition: Responsável por manipular o carrinho de compras.
 * @package Model\Model
 * @author Douglas <dcdouglas64@gmail.com>
 *
 */
class Cart extends Model
{
    /** @var array SESSION armazena dados do carrinho */
   const SESSION = "Cart";
   const SESSION_ERROR = "ErrorCart";

   /**
    * Responsável por obter, ou criar um carrinho de compras.
    * @access public
    * @return object
   */
   public static function getFromSession():Cart {

       $cart = new Cart();

       if(isset($_SESSION[Cart::SESSION]["idcart"]) AND $_SESSION[Cart::SESSION]["idcart"] > 0){
            $cart->get((int) $_SESSION[Cart::SESSION]["idcart"]);
       }else{
           $cart->getFromSessionID();
           if(!(int) $cart->getidcart() > 0){
               $data = ["dessessionid" => session_id()];

               if(User::checkLogin(false)){
                  $user = User::getFromSession();
                  $data["iduser"] = $user->getiduser();
               }

               $cart->setData($data);
               $cart->save();
               $cart->setToSession();
           }
       }

       return $cart;

   }

    /**
     * Preenche a sessão do carrinho com os dados do usuário, ou do visitante.
     * @access public
     * @return void
     */
   public function setToSession():void{
       $_SESSION[Cart::SESSION] = $this->getValues();
   }

    /**
     * Obtém o carrinho de compras pelo ID da Seção e preenche o objeto:Cart
     * @access public
     * @return void
     */
    public function getFromSessionID():void{

        /** @var object $sql contém métodos de consulta ao Banco de Dados*/
        $sql = new Sql();
        /** @var array $result recebe resultado da consulta ao Banco de Dados*/
        $result = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",[":dessessionid" => session_id()]);

        if(count($result) > 0){
            $this->setData($result[0]);
        }

   }

    /**
     * Obtém o carrinho de compras pelo idcart
     * @access public
     * @param int $idcart
     * @return void
     */
    public function get(int $idcart):void{
       /** @var object $sql contém métodos de consulta ao Banco de Dados*/
       $sql = new Sql();
       /** @var array $result recebe resultado da consulta ao Banco de Dados*/
       $result = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",[":idcart" => $idcart]);

       if(count($result) > 0){
           $this->setData($result[0]);
       }

   }

   public function addProduct(Product $product):void{

       $sql = new Sql();

       $qtd = (isset($_GET["quantidade"]) && (int)$_GET["quantidade"] > 1) ? intval($_GET["quantidade"]) : 1;

       for($i = 0;$i < $qtd;$i++){
           $sql->query("INSERT INTO tb_cartsproducts (idcart,idproduct) VALUES (:idcart,:idproduct)",[
               ":idcart" => $this->getidcart(),
               ":idproduct" => $product->getidproduct(),
           ]);
       }

       $this->getCalculateTotal();

   }

   public function removeProduct(Product $product, bool $all = false):void{
       $sql = new Sql();

       if($all){
           $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart =:idcart AND idproduct =:idproduct AND dtremoved IS NULL",[
               ":idcart" => $this->getidcart(),
               ":idproduct" => $product->getidproduct()
           ]);
       }else{
           $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart =:idcart AND idproduct =:idproduct AND dtremoved IS NULL LIMIT 1",[
               ":idcart" => $this->getidcart(),
               ":idproduct" => $product->getidproduct()
           ]);
       }
       $this->getCalculateTotal();
   }

   public function getProducts():array{

       $sql = new Sql();
       $rows = $sql->select("
			SELECT 
			       b.idproduct,
			       b.desproduct ,
			       b.vlprice,
			       b.vlwidth,
			       b.vlheight,
			       b.vllength,
			       b.vlweight,
			       b.desurl,
			       b.pathphoto,
			       COUNT(*) AS nrqtd,
			       SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct 
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
			GROUP BY b.idproduct,
			         b.desproduct,
			         b.vlprice,
			         b.vlwidth,
			         b.vlheight,
			         b.vllength,
			         b.vlweight,
			         b.desurl,
			         b.pathphoto
			ORDER BY b.desproduct
		", [
           ':idcart'=>$this->getidcart()
       ]);

       return $rows;
   }

   /**
    * Salva na tabela tb_carts o carrinho de compras
    * @access public
    * @return void
    */
   public function save():void {
       /** @var object $sql contém métodos de consulta ao Banco de Dados*/
       $sql = new Sql();

       /** @var array $result recebe resultado da consulta ao Banco de Dados*/
       $result = $sql->select("CALL sp_carts_save(:idcart, :dessessionid,:iduser,:deszipcode,:vlfreight,:nrdays)",[
           ':idcart'=>$this->getidcart(),
           ":dessessionid" => $this->getdessessionid(),
           ":iduser" => $this->getiduser(),
           ":deszipcode" => $this->getdeszipcode(),
           ":vlfreight" => $this->getvlfreight(),
           ":nrdays" => $this->getnrdays()
       ]);

       if(!empty($result) && count($result) > 0){
           $this->setData($result[0]);
       }else{
           echo($sql->getMessage());

       }
   }

    /**
     * Obtém os totais de: Preço,Largura,Altura,Comprimento,Peso, e Quantidade Total
     * dos produtos contidos no carrinho de compras.
     * @access public
     * @return array
     */
   public function getProductsTotal():array{

       /** @var string $query contém a consulta feita ao Banco*/
       $query = "SELECT
                    SUM(vlprice) AS preco,
                    SUM(vlwidth) AS largura,
                    SUM(vlheight) AS altura,
                    SUM(vllength) AS comprimento,
                    SUM(vlweight) AS peso,
                    COUNT(*) as qtdtotal
                 FROM tb_products a
                    INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
                 WHERE b.idcart = :idcart AND dtremoved IS NULL";

       $sql = new Sql();

       /** @var array $result contém a lista com os totais do carrinho de compras.*/
       $result = $sql->select($query,[":idcart" => $this->getidcart()]);

       if(count($result) > 0){
           return $result[0];
       }else{
           return [];
       }
   }

   public function setFrete(string $zipcode){

       $totais = $this->getProductsTotal();

       /*https://www.correios.com.br/a-a-z/limites-de-dimensoes-e-peso*/

       ($totais["comprimento"] > 105 OR $totais["comprimento"] < 15) ? $totais["comprimento"] = 15 : $totais["comprimento"];
       ($totais["altura"] > 105 OR $totais["altura"] < 1) ? $totais["altura"] = 10 : $totais["altura"];
       ($totais["largura"] > 105 OR $totais["largura"] < 10) ? $totais["largura"] = 10 : $totais["largura"];
       ($totais["peso"] < 1) ? $totais["peso"] = 10 : $totais["peso"];


       $qs = http_build_query([
           "nCdEmpresa" => "",
           "sDsSenha" => "",
           "nCdServico" => "04014",
           "sCepOrigem" => "26600000",
           "sCepDestino" => $zipcode,
           "nVlPeso" => (string) $totais["peso"],
           "nCdFormato" => 1,
           "nVlComprimento" => (float) $totais["comprimento"],
           "nVlAltura" => (float) $totais["altura"],
           "nVlLargura" => (float) $totais["largura"],
           "nVlDiametro" => 10,
           "sCdMaoPropria" => "S",
           "nVlValorDeclarado" => (float) $totais["preco"],
           "sCdAvisoRecebimento" => "S"
       ]);

       $webservice = "http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs;

       $cr = curl_init();
       curl_setopt($cr, CURLOPT_URL, $webservice);
       curl_setopt($cr, CURLOPT_RETURNTRANSFER, true);
       $retorno = curl_exec($cr);
       curl_close($cr);

       $xml = simplexml_load_string($retorno);
       $frete = $xml->Servicos->cServico;

       $erroFrete = $frete->MsgErro;

       if(!empty($erroFrete)){
           Cart::setMsgErro($erroFrete);
       }else{
           Cart::clearMsgErro();
       }
       $valorFrete = $frete->Valor;
       $prazoFrete = $frete->PrazoEntrega;

       $this->setdeszipcode($zipcode);
       $this->setvlfreight(Cart::formatValueToDecimal($valorFrete));
       $this->setnrdays($prazoFrete);

       $this->save();

   }

   public static  function formatValueToDecimal(string $value):float{

       $number = str_replace(".","",$value);
       $number = str_replace(",",".",$number);

       return floatval($number);
   }

   public static function setMsgErro(string $msg):void{

        $_SESSION[Cart::SESSION_ERROR] = $msg;

   }

    public static function clearMsgErro():void{

        $_SESSION[Cart::SESSION_ERROR] = NULL;

    }

    public static function getMsgErro():string{

        $msg = (isset($_SESSION[Cart::SESSION_ERROR]) AND !empty($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
        Cart::clearMsgErro();
        return $msg;

    }

    public function updateFrete():void{

       if(!empty($this->deszipcode)){
            $this->setFrete($this->deszipcode);
       }

    }

    public function getValues():array{

       $this->getCalculateTotal();

       return parent::getValues();
    }

    public function getCalculateTotal():void{

       $this->updateFrete();
       $totals = $this->getProductsTotal();

       $this->setvlsubtotal($totals["preco"]);
       $this->setvltotal($totals["preco"] + $this->getvlfreight());

    }
}