<?php

namespace Hcode;

use Rain\Tpl;

class Page {

	private $tpl;
	private $options = []; //ATRIBUTO ONDE FICA GRAVADO O $defaults E O $opts
	private $defaults = [
		"header"=>true,
		"footer"=>true,
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

		if ($this->options["header"] === true) $this->tpl->draw("header");//SE OPÇÃO "header" FOR TRUE DESENHA O HEADER
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

		if ($this->options["footer"] === true) $this->tpl->draw("footer");//SE OPÇÃO "footer" FOR TRUE DESENHA O HEADER


	}
}

?>