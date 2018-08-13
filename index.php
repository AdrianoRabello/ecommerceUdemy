<?php

 session_start();
 require_once("vendor/autoload.php");

 use \Slim\Slim;

 $app = new \Slim\Slim();
 require "site.php";
 require "admin-users.php";
 require "admin-categories.php";
 require "admin.php";
 require "admin-products.php";
 require "functions.php";








 $app->config('debug', true);

 $app->run();
?>
