<?php

namespace Hcode;

use Rain\Tpl;

class Page {

	private $tpl;
	private $options = []; //ATRIBUTO ONDE FICA GRAVADO O $defaults E O $opts
	private $defaults = [
		"data"=>[]
	];

	public function __construct($opts = array(), $tpl_dir = "/views/"){ // P

		$this->options	= array_merge($this->defaults, $opts); // O ULTIMOS SEMPRE SOBRESCREVE OS ANTERIORES

		$config = array(
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false
	   	);

		Tpl::configure( $config );

		$this->tpl = new Tpl;		// AS VARIÁVEIS ESTÃO EM FUNÇÃO DAS ROTAS E RECEBEM DADOS PELO CONSTRUCT ($opts)

		$this->setData($this->options["data"]);

		$this->tpl->draw("header");
	}

	private function setData($data = array()) {

		foreach ($data as $key => $value) {
		 	$this->tpl->assign($key, $value);
	 	}	
	}

	public function setTpl($name, $data = array(), $returnHTML = false) {

		$this->setData($data);

		return $this->tpl->draw($name,$returnHTML);
	}


	public function __destruct(){

		$this->tpl->draw("footer");


	}
}

?>