<?php

	namespace Hcode;

	/**
	 *
	 */
	class Model{

		private $values = [];




		/* recebe o nome do campo e o valor*/
		public function __call($name, $args){

		 $method = substr($name, 0, 3);

		 $fieldName = substr($name, 3, strlen($name));

		 /*print_r($method, $fieldName);
		 exit;*/

		 switch ($method) {

		  case "get":
		   return (isset($this->values[$fieldName]))? $this->values[$fieldName] : NULL;
		  break;
		   case "set":
		    $this->values[$fieldName] = $args[0];
		   break;

		 }

		}


		public function setData($data = array()){

		 foreach ($data as $key => $value) {
		  $this->{"set".$key}($value);
		 }

		}

		public function getValues(){

		 return $this->values;
		}


	}

 ?>
