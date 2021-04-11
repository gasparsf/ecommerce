<?php 
session_start(); 		// INICIA O USO DE SESSÕES
require_once("vendor/autoload.php");

use \Slim\Slim;			// UTILIZAÇÃO DA CLASSE "Slim"
use \Hcode\Page; 		// UTILIZAÇÃO DA CLASSE "Page"
use \Hcode\PageAdmin; 	// UTILIZAÇÃO DA CLASSE "PageAdmin"
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");

$app->run();

 ?>