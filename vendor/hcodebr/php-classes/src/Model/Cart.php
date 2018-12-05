<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;

class Cart extends Model{

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

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

				//$user = User::getFromSession();

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

	public function setToSession()
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

		$this->getCalculateTotal();

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

		$this->getCalculateTotal();

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

	public function getProductsTotals()
	{

		$sql = new Sql();

		$results = $sql->select("
			SELECT SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd, SUM(vlprice) AS vlprice 
			FROM tb_products a 
			INNER JOIN  tb_cartsproducts b ON a.idproduct = b.idproduct
			WHERE b.idcart = :idcart AND b.dtremoved IS NULL
			 ", [
			':idcart' => $this->getidcart() 
		]);

		if (count($results) > 0){
			return $results[0];
		} else {
			return [];
		}
	}

	public function setFreight($nrzipcode)
	{
		$nrzipcode = str_replace('-', '', $nrzipcode);//removendo o '-'

		$totals = $this->getProductsTotals();//Retorna as informações totais do carrinho

		//verificar se existe algum carrinho
		if($totals['nrqtd'] > 0){
			//função que constroi query string para URL (recebe um array com os parametros)
			if ($totals['vllength'] < 16) $totals['vllength'] = 16;
			if ($totals['vlheight'] < 2) $totals['vlheight'] = 2;
			if ($totals['vlweight'] < 1) $totals['vlweight'] = 1;
			if ($totals['vlwidth'] < 11) $totals['vlwidth'] = 11;


			$qs = http_build_query([
				'nCdEmpresa'=>'',//Nome da empresa
				'sDsSenha'=>'',//senha do serviço
				'nCdServico'=>'40010',//codigo do serviço que deseja utilizar
				'sCepOrigem'=>'27970000',//De onde sai a mercadoria
				'sCepDestino'=>$nrzipcode,//Para onde vai a mercadoria
				'nVlPeso'=>$totals['vlweight'] ,//valor total do peso
				'nCdFormato'=>'1',//1 – Formato caixa/pacote
								//2 – Formato rolo/prisma
								//3 - Envelope	
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'N',//Indica se a encomenda será entregue com o serviço adicional mão própria.
									//Valores possíveis: S ou N (S – Sim, N – Não)
				'nVlValorDeclarado'=>$totals['vlprice'],//Indica se a encomenda será entregue com o serviço adicional valor declarado. Neste campo deve ser apresentado o valor declarado desejado, em Reais.
				//CASO NÃO DESEJE INFORMAR '0'
				'sCdAvisoRecebimento'=>'N'//Indica se a encomenda será entregue com o serviço adicional aviso de recebimento.
										//Valores possíveis: S ou N (S – Sim, N – Não)

			]);	

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);//Função que lê arquivos XML
			//Passando o WEBSERVICE dos correios

			$result = $xml->Servicos->cServico;

			if ($result->MsgErro != ''){
				Cart::setMsgError($result->MsgErro);
			} else {
				Cart::clearMsgError();
			}


			$this->setdeszipcode($nrzipcode);
			$this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			$this->setnrdays($result->PrazoEntrega);

			$this->save();

			return $result;

		} else {

		}
	}

	public static function formatValueToDecimal($value):float
	{//COnverte valores em decimais para gravar no banco

		$value = str_replace('.', '', $value);
		return str_replace(',', '.', $value);
	}

	public static function setMsgError($msg)
	{//Seta o Erro para a seção
		$_SESSION[Cart::SESSION_ERROR] = $msg;
	}

	public static function getMsgError()
	{//Pega o error na Seção
		$msg =  (isset($_SESSION[Cart::SESSION_ERROR])) ? $$_SESSION[Cart::SESSION_ERROR] : "";

		Cart::clearMsgError();

		return $msg;

	}

	public static function clearMsgError()
	{//Limpa a Seção de erro
		$_SESSION[Cart::SESSION_ERROR] = NULL;
	}

	public function updateFreight()
	{
		if ($this->getdeszipcode() != ''){

			$this->setFreight($this->getdeszipcode());
		}

	}

	public function getValues(){

		$this->getCalculateTotal();

		return parent::getValues();
	}

	public function getCalculateTotal()
	{
		$this->updateFreight();

		$totals = $this->getProductsTotals();

		$this->setvlsubtotal($totals['vlprice']);
		$this->setvltotal($totals['vlprice'] + $this->getvlfreight());

	}

 }//Fim da Classe Cart

?>