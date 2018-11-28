<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model{

	const SESSION = "Cart";

	public static function getFromSession()
	{
		$cart = new Cart();

		if (isset($_SESSION[Cart::SESSION]) //Se já está na seção
			&& (int)$_SESSION[Cart::SESSION]['idcart'] > 0)//Se a seção já existe um ID
		{

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']);//Carrega o carrinho
		} else {//Se não existir
			$cart->getFromSessionID();//Busca carrinho no banco

			if(!(int)$cart->getidcart() >0){//Se não encontrou carrinho, Cria um carrinho

				$data = [
					'dessessionid'=>session_id()
				];

				$user = User::getFromSession();

				if (User::checkLogin(false)){
					$user = User::getFromSession();

					$data['iduser'] = $user->getiduser();
				}
				$cart->setData($data);

				$cart->save();

				$cart->setToSession();
			}
		}

		return $cart;

	}

	public function seToSession()
	{
		$_SESSION[Cart::SESSION] = $this->getValues();

	}

	public function getFromSessionID()
	{//Verifica se o carrinho existe no banco
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid",array(
			':dessessionid' => session_id() 
		));

		if (count($results) > 0){

			$this->setData($results[0]);//Seta os valores em suas chaves
		}
	}

	public function save()##### Salva os registros no BD
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", array(
			":idcart"=>$this->getidcart(),
			":dessessionid"=>$this->getdessessionid(),
			":iduser"=>$this->getiduser(),
			":deszipcode"=>$this->getdeszipcode(),
			":vlfreight"=>$this->getvlfreight(),
			":nrdays"=>$this->getnrdays(),
			
		));

		$this->setData($results[0]);
	}

	public function get(int $idcart)##### Retorna o carrinho pelo id
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart",array(
			':idcart' => $idcart 
		));

		if (count($results) > 0){

			$this->setData($results[0]);//Seta os valores em suas chaves
		}
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("DELETE FROM tb_carts WHERE idcart = :idcart",array(
			':idcart' => $this->getidcart()
		));
	}

	public function addProduct(Products $product)
	{
		$sql = new Sql();

		$sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
			':idcart' => $this->getidcart(),
			':idproduct' => $product->getidproduct()
		]);

	}

	public function removeProduct(Products $product, $all = false){

		$sql = new Sql();

		if($all){

			$sql->query("UPDATE  tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
			':idcart' => $this->getidcart(),
			':idproduct' => $product->getidproduct()
			]);

		} else {
			$sql->query("UPDATE  tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
			':idcart' => $this->getidcart(),
			':idproduct' => $product->getidproduct()
			]);
		}	

	}

	public function getProducts()
	{
		$sql = new Sql();

		$results = $sql->select("
			SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
			FROM tb_cartsproducts a 
			INNER JOIN tb_products b ON a.idproduct = b.idproduct
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL
			GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
			ORDER BY b.desproduct
			 ", [
			':idcart' => $this->getidcart() 
		]);

		return Products::checkList($results);
	}

 }//Fim da Classe Cart

?>