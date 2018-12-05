<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

	const SESSION = "User";
	const SECRET = "SaiaDoBuraco_Secret";//Esta constante deve ter pelo menos 16 caracteres
	const CIPHER = "aes-128-gcm";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";


	public static function login ($login, $password)#### Logar Usuário no sistema
	{

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
 				":LOGIN"=>$login
			));

		if (count($results) === 0) //Se não encontrou nenhum registro, Exibe erro!
		{
			throw new \Exception("Usuário ou senha inválida");
		}

		$data = $results[0];

		if (password_verify($password, $data["despassword"]) === true)//Se senha OK! Retorna usuário
		{
			$user = new User();

			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			throw new \Exception("Usuário ou senha inválida");// Se Senha não confere, Exibe erro!
			
		}

	}

	public static function verifyLogin($inadmin = true)#### Verfica se usuário está logado
	{
		if (!User::checkLogin($inadmin)) {
			if ($inadmin) {
				header("Location: /admin/login");
			} else {
				header("Location: /login");
			}
			exit;
		}
	}

	public static function logout()#### Desloga usuário
	{
		$_SESSION[User::SESSION] = NULL;
	}

	public static function listAll()#### Listar todos os usuários
	{
		$sql = new Sql();

		return $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	public function save()##### Salva os registros no BD
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}

	public function get($iduser)##### Retorna o Usuáio pelo id
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",array(
			':iduser' => $iduser 
		));

		$data['desperson'] = utf8_encode($data['desperson']);
		$this->setData($results[0]);//Seta os valores em suas chaves
	}

	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}

	public function delete()
	{
		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:iduser)", array(
			":iduser"=>$this->getiduser()
		));
	}

	public static function getForgot($email, $inadmin = true)
	{
	     $sql = new Sql();
	     $results = $sql->select("
	         SELECT *
	         FROM tb_persons a
	         INNER JOIN tb_users b USING(idperson)
	         WHERE a.desemail = :email;
	     ", array(
	         ":email"=>$email
	     ));
	     if (count($results) === 0)
	     {
	         throw new \Exception("Não foi possível recuperar a senha.");
	     }
	     else
	     {
	         $data = $results[0];
	         $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
	             ":iduser"=>$data['iduser'],
	             ":desip"=>$_SERVER['REMOTE_ADDR']
	         ));
	         if (count($results2) === 0)
	         {
	             throw new \Exception("Não foi possível recuperar a senha.");
	         }
	         else
	         {
	             $dataRecovery = $results2[0];
	             $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	             $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
	             $result = base64_encode($iv.$code);
	             if ($inadmin === true) {
	                 $link = "http://saiadoburaco.com.br/admin/forgot/reset?code=$result";
	             } else {
	                 $link = "http://saiadoburaco.com.br/forgot/reset?code=$result";
	             } 
	             $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
	                 "name"=>$data['desperson'],
	                 "link"=>$link
	             )); 
	             $mailer->send();
	             return $link;
	         }
	     }
	 }
	 public static function validForgotDecrypt($result)
	 {
	     $result = base64_decode($result);
	     $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
	     $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;
	     $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
	     $sql = new Sql();
	     $results = $sql->select("
	         SELECT *
	         FROM tb_userspasswordsrecoveries a
	         INNER JOIN tb_users b USING(iduser)
	         INNER JOIN tb_persons c USING(idperson)
	         WHERE
	         a.idrecovery = :idrecovery
	         AND
	         a.dtrecovery IS NULL
	         AND
	         DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
	     ", array(
	         ":idrecovery"=>$idrecovery
	     ));
	     if (count($results) === 0)
	     {
	         throw new \Exception("Não foi possível recuperar a senha.");
	     }
	     else
	     {
	         return $results[0];
	     }
	 }

	 public static function setForgotUsed($idrecovery)
	 {
	 	$sql = new Sql();

	 	$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
	 		":idrecovery"=>$idrecovery
	 	));
	 }

	 public function setPassword($password)
	 {
	 	$sql = new Sql();

	 	$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
	 		":password" => $password,
 			":iduser"=>$this->getiduser()
	 	));
	 }

	 public static function getFromSession()
	 {
	 	
	 	$user = new User();

	 	if (isset($_SESSION[User::SESSION]) && 
	 	(int)$_SESSION[User::SESSION]['iduser'] > 0){//Verifica se existe a seção
	 		$user->setData($_SESSION[User::SESSION]);

	 	}

	 	return $user;
	 }

	 public static function checkLogin($inadmin = true)
	 {
	 	if (
			!isset($_SESSION[User::SESSION])//se existe a sessão
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		){

	 		//Não esta logado
	 		return false;

		} else {

			if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){
				return true;
			} else if ($inadmin === false){
				return true;
			} else {
				return false;
			}
		}
	 }

	 	public static function setError($msg)
	{//Seta o Erro para a seção
		$_SESSION[User::ERROR] = $msg;
	}

	public static function getError()
	{//Pega o error na Seção
		$msg =  (isset($_SESSION[User::ERROR])) ? $_SESSION[User::ERROR] : "";

		User::clearError();

		return $msg;

	}

	public static function clearError()
	{//Limpa a Seção de erro
		$_SESSION[User::ERROR] = NULL;
	}

	public static function getPasswordHash($password)
	{
		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);
	}

	public static function setSuccess($msg)
	{
	
		$_SESSION[User::SUCCESS] = $msg;
	
	}
	
	public static function getSuccess()
	{
	
		$msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : '';
	
		User::clearSuccess();
	
		return $msg;
	
	}
	
	public static function clearSuccess()
	{
	
		$_SESSION[User::SUCCESS] = NULL;
	
	}
	
	public static function setErrorRegister($msg)
	{
	
		$_SESSION[User::ERROR_REGISTER] = $msg;
	
	}
	
	public static function getErrorRegister()
	{
	
		$msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : '';
	
		User::clearErrorRegister();
	
		return $msg;
	}
	
	public static function clearErrorRegister()
	{
	
		$_SESSION[User::ERROR_REGISTER] = NULL;
	
	}
	
	public static function checkLoginExist($login)
	{
	
		$sql = new Sql();
	
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :deslogin", [
			':deslogin'=>$login
		]);
	
		return (count($results) > 0);
	}


 }//Fim da Classe User

?>