<?php

namespace Hcode;

class Model {

	private $values = []; //TERÁ TODOS OS CAMPOS/VALORES DENTRO DO OBJETO ($args / ex: $data["iduser"])

	// MÉTODO MÁGICO QUE É DISPARADO QUANDO UM MÉTODO INACESSÍVEL É EXECUTADO

	public function __call($name, $args)	//$name (ex: setiduser) E $args (ex: $data["iduser"])
	{			
		// SEPARA O $name (setiduser) INDICAR O MÉTODO($method) E OS PARAMETROS ($fieldname)

		$method = substr($name, 0, 3); 					//SEPARA AS 3 PRIMEIRAS LETRAS (setiduser -- [set])
		$fieldName = substr($name, 3, strlen($name));	//SEPARA O RESTO (setiduser -- [iduser])

		switch ($method) // O SWITCH É USADO PARA SE COMPARAR A MESMA VARIÁVEL ($method)
		{
			// SE FOR MÉTODO GET RETORNA OS VALOR DO CAMPO(iduser) QUE ESTA NO ARRAY $values 
			case "get":
				return $this->values[$fieldName]; 		
			break;

			// SE FOR MÉTODO SET ARMAZENA NO CAMPO INDICADO A PRIMEIRA POSIÇÃO DO ARRAY $args;
			case "set":
				$this->values[$fieldName] = $args[0];	
			break;
		}
	}

	public function setData($data = array())
	{

		foreach ($data as $key => $value) {

			$this->{"set".$key}($value); //TUDO QUE É DINAMICO SE COLOCA CHAVES {}
		}
	}

	public function getValues()
	{
		return $this->values;
	}
}