<?php

namespace Hcode\Model;

use \Hcode\DB\Sql; 	// UTILIZAÇÃO DA CLASSE "Sql"
use \Hcode\Model;	// UTILIZAÇÃO DA CLASSE "Model"

class User extends Model {
 
	const SESSION = "User"; //DEFINE O NOME DA SESSÃO

	public static function login($login, $password)
	{

		$sql = new Sql();

		// BUSCAR O LOGIN NO BANCO
		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
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

			$user->setData($data); // MÉTODO QUE CRIA UM ATRIBUTO PARA CADA CAMPO RETORNADO NO SELECT DO DB

			//PRA FUNCIONAR UM LOGIN É NECESSARIO CRIAR UMA SESSÃO CONTENDO OS DADOS E PARA SER VERIFICADA EM OUTRAS PAGINAS AFIM DE GARANTIR QUE O USUÁRIO ESTÁ LOGADO

			//INVOCA UM MÉTODO (getValues) QUE RETORNA OS DADOS ($values) PARA COLOCA-LOS NA SESSÃO (SESSION)
			$_SESSION[User::SESSION] = $user->getValues(); 

			return $user;
			
		} else {
			throw new \Exception("Usuário inexistente ou senha inválida");
		}
	}

	public static function verifyLogin($inadmin = true) 	// VERIFICA SE O USUÁRIO ESTÁ LOGADO [NA ADMINISTRAÇÃO]
	{

		if (!isset($_SESSION[User::SESSION])				// VERIFICA SE NÃO HÁ SESSÃO DEFINIDA
			||												// OU
			!$_SESSION[User::SESSION]						// VERIFICA SE A SESSÃO NÃO TEM VALO
			||												// OU
			!(int)$_SESSION[User::SESSION]["iduser"] > 0 	// VERIFICA SE O CAMPO DE ID USUÁRIO NÃO(!) FOR MAIO QUE 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		) {

			header("Location: /admin/login"); 			//SE ALGUMA DAS CONDIÇÕES ANTERIORES FOR VERDADEIRA, REDIRECIONA
			exit; 
		}
	}

	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}
}

?>