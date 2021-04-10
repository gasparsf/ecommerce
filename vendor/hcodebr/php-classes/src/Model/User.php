<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {
 
	const SESSION = "User";
	const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";
	const ERROR = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS = "UserSucesss";

	public static function getFromSession()
	{

		$user = new User();

		if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {

			$user->setData($_SESSION[User::SESSION]);

		}

		return $user;
	}

	public static function checkLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
		) {
			//Não está logado
			return false;
		} else {

			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {

				return true;

			} else if ($inadmin === false) {

				return true;
			} else {

				return false;
			}
		}
	}

	public static function login($login, $password)
	{

		$sql = new Sql();

		// BUSCAR O LOGIN NO BANCO
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b ON a.idperson = b.idperson WHERE a.deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		if (count($results) === 0) //SE NÃO ENCONTOU O VALOR NO BANCO
		{
			// A Exception esta no namespace do PHP e não no Hcode\Model e por isso deve-se utilizar (\)
			throw new \Exception("Usuário inexistente ou senha inválida."); 
		}

		$data = $results[0];
		// VERIFICA SE A HASH ("despassword") CORRESPONDE AO PASSWORD ($password) INSERIDO
		if (password_verify($password, $data["despassword"]) === true)
		{

			$user = new User(); //POR SER UM MÉTODO ESTÁTICO, DEVE GERAR UM INSTÂNCIA DA CLASSE ONDE ESTÁ O MÉTODO

			// CHAMA UM MÉTODO QUE NÃO EXISTE (setiduser), DISPARANDO O MÉTODO MÁGICO __CALL QUE ESTÁ NA CLASSE Model

			//$user->setiduser($data["iduser"]); //O VALOR DO CAMPO $data["iduser"] PARA O USUARIO ADMIN É 1 (O ID É UM)
			$data['desperson'] = utf8_encode($data['desperson']);

			$user->setData($data); // MÉTODO QUE CRIA UM ATRIBUTO PARA CADA CAMPO RETORNADO NO SELECT DO DB

			//PRA FUNCIONAR UM LOGIN É NECESSARIO CRIAR UMA SESSÃO CONTENDO OS DADOS E PARA SER VERIFICADA EM OUTRAS PAGINAS AFIM DE GARANTIR QUE O USUÁRIO ESTÁ LOGADO

			//INVOCA UM MÉTODO (getValues) QUE RETORNA OS DADOS ($values) PARA COLOCA-LOS NA SESSÃO (SESSION)
			$_SESSION[User::SESSION] = $user->getValues(); 

			return $user;
			
		} else {
			throw new \Exception("Usuário inexistente ou senha inválida");
		}
	}

	public static function verifyLogin($inadmin = true)
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

	public static function logout()
	{
	
		$_SESSION[User::SESSION] = NULL;
	}

	// LISTA TODOS OS DADOS DE TODOS OS USUÁRIOS DO BD, FAZENDO UMA JUNÇÃO DE DUAS TABELAS (tb_users / tb_persons)
	public static function listAll()
	{

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
	}

	public function save()
	{

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);
	}

	public function get($iduser)
	{
	 
	 $sql = new Sql();
	 
	 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array(
	 ":iduser"=>$iduser
	 ));
	 
	 $data = $results[0];

	 $data['desperson'] = utf8_encode($data['desperson']);
	 
	 $this->setData($data);
 	}

	public function update()
	{
		$sql = new Sql();

		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":iduser"=>$this->getiduser(),
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>$this->User::getPasswordHash($this->getdespassword()),
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

	// MÉTODO PARA VERIFICAR SE O E-MAIL ESTA CADASTRADO NO BANCO DE DADOS
	public static function getForgot($email, $inadmin = true)
	{
		$sql = new Sql();

		//ALÉM DE VERIFICAR A EXISTENCIA DO EMAIL, O SELECT TB DEVE RETORNAR O USERID
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
				"iduser"=>$data["iduser"],
				"desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if(count($results2)===0)
			{
				throw new \Exception("Não foi possível recuperar a senha.");
			}

			else
			{
				$dataRecovery = $results2[0];
				//CIRAÇÃO DO CÓDIGO PARA REDEFINIÇÃO DE SENHA (A CODIFICAÇÃO BASE 64 SERVE PARA PASSAR OS DADOS VIA GET SEM PERDER INFORMAÇÃO)
				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
				$code = base64_encode($code);

				if ($inadmin === true) {

					// CRIAÇÃO DO LINK PARA SER RECUPERADO A SENHA, PASSANDO O CODIGO VIA GET
					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";					
				} else {
					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
				}

				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha da Hcode Store", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));

				$mailer->send();

				return $link;
			}
		}
	}

	public static function validForgotDecrypt($code)
	{

		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

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
			":password"=>$password,
			":iduser"=>$this->getiduser()
		));
	}


	public static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_DEFAULT, [
			'cost'=>12
		]);
	}
}

?>