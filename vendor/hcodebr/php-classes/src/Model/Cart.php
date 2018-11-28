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

 }//Fim da Classe Cart

?>