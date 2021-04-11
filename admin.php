<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app->get('/admin', function() {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");
});

$app->get('/admin/login', function() {
	// ESTAS OPÇÕES FORAM CONFIGURADAS NA CLASSE Page QUE EXTENDE DE PageAdmin
	$page = new PageAdmin([
		"header"=>false,	// OPÇÃO PARA DESABILITAR O TEMPLATE DO HEADER
		"footer"=>false 	// OPÇÃO PARA DESABILITAR O TEMPLATE DO FOOTER
	]);

	$page->setTpl("login");
	
});

$app->post('/admin/login', function() {

	// ACESSO O MÉTODO ESTÁTICO "login" DENTRO DA CLASSE "User" (::)

	User::login($_POST["login"], $_POST["password"]); // RECEBE O POST DO "login" E DA "password"

	header("Location: /admin"); // REDIRECIONA
	exit;	
});

$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;
});

// CRIAÇÃO DA ROTA DO LINK ESQUECI A SENHA
$app->get("/admin/forgot", function() {

	// ESTAS OPÇÕES FORAM CONFIGURADAS NA CLASSE Page QUE EXTENDE DE PageAdmin
	$page = new PageAdmin([
		"header"=>false,	// OPÇÃO PARA DESABILITAR O TEMPLATE DO HEADER
		"footer"=>false 	// OPÇÃO PARA DESABILITAR O TEMPLATE DO FOOTER
	]);

	$page->setTpl("forgot");
});

// ROTA TIPO POST PARA ENVIO DO FORMULARIO
$app->post("/admin/forgot", function() {

	// ESTAS OPÇÕES FORAM CONFIGURADAS NA CLASSE Page QUE EXTENDE DE PageAdmin
		$user = User::getForgot($_POST["email"]);

		header("Location: /admin/forgot/sent");
		exit;
});

$app->get("/admin/forgot/sent", function() {

	$page = new PageAdmin([
		"header"=>false,	// OPÇÃO PARA DESABILITAR O TEMPLATE DO HEADER
		"footer"=>false 	// OPÇÃO PARA DESABILITAR O TEMPLATE DO FOOTER
	]);

	$page->setTpl("forgot-sent");
});

$app->get("/admin/forgot/reset", function(){
	// VALIDAR O CÓDIGO RECEBIDO
	$user = User::validForgotDecrypt($_GET["code"]);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	// VALIDA O CÓDIGO ENCRIPTADO NA PÁGINA DE RESET
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

$app->post("/admin/forgot/reset", function(){

	$forgot = User::validForgotDecrypt($_POST["code"]);	

	User::setForgotUsed($forgot["idrecovery"]);

	$user = new User();

	$user->get((int)$forgot["iduser"]);

	$password = User::getPasswordHash($_POST["password"]);

	$user->setPassword($password);

	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");

});

?>