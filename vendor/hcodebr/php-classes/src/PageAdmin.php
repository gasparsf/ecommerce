<?php

namespace Hcode;

class PageAdmin extends Page {

	public function __construct($opts = array(), $tpl_dir = "/views/admin/"){

		parent::__construct($opts, $tpl_dir); //PASSA OS PARAMETROS DA CLASSE PARA O CONSTRUCTOR DA CLASSE PAGE
	}
}
?>