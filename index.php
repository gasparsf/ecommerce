<?php 
session_start(); 		// INICIA O USO DE SESSÕES
require_once("vendor/autoload.php");

use \Slim\Slim;			// UTILIZAÇÃO DA CLASSE "Slim"
use \Hcode\Page; 		// UTILIZAÇÃO DA CLASSE "Page"
use \Hcode\PageAdmin; 	// UTILIZAÇÃO DA CLASSE "PageAdmin"
use \Hcode\Model\User; 	// UTILIZAÇÃO DA CLASSE "User"

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {

	$page = new Page();

	$page->setTpl("index");
	
});

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

$app->run();

 ?>