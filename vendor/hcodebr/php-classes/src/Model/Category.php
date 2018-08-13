<?php

 namespace Hcode\Model;
 Use \Hcode\DB\Sql;
 Use \Hcode\Model;
 Use \Hcode\Mailer;

 /**
  *
  */
 class Category extends Model{

 	function __construct(){
 		# code...
 	}

 	public static function listAll(){

  	 $sql = new Sql();

  		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
 	}

 	public function save($data = array()){

 	 	$sql = new Sql();

 		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
 			 ":idcategory"	=>$this->getidcategory(),
 			 ":descategory"	=>$this->getdescategory()

 		));

 		$this->setData($results[0]);

 		Category::updateFile();

 	}

 	public function get($idcategory){

 		$sql = new Sql();

 		$results = $sql->select("SELECT * FROM tb_categories where idcategory = :idcategory",array(":idcategory"=>$idcategory));

 		$this->setdata($results[0]);
 	}

 	public function delete(){

 	 $sql = new Sql();

 	 $sql->query("DELETE FROM tb_categories where idcategory = :idcategory",array(":idcategory"=>$this->getidcategory()));

 	 Category::updateFile();

 	}

 	public static function updateFile(){

 	 $categories = Category::listAll();

 	 $html = [];

 	 foreach ($categories as $row) {
 	 	array_push($html,"<li> <a href='/categories/".$row['idcategory']."'> ".$row['descategory']."</a></li>");
 	 }

 	 file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html",implode('', $html));

 	}


 	public function getProducts($related = true){

 		$sql = new Sql();

 		if ($related === true) {

 			return $sql->select("SELECT * from tb_products where idproduct IN(
						   select a.idproduct from tb_products a
						   inner join tb_productscategories b
							on a.idproduct = b.idproduct where b.idcategory =:idcategory);",array(":idcategory"=>$this->getidcategory()));

 	 	}else{

 		 return $sql->select("SELECT * from tb_products where idproduct NOT IN(
					 select a.idproduct from tb_products a
					 inner join tb_productscategories b
					 on a.idproduct = b.idproduct where b.idcategory =:idcategory);",array(":idcategory"=>$this->getidcategory()));


 		}

 	}

 	public function getProductsPage($page = 1, $itensPerPage = 4){

 		$start = ($page - 1) * $itensPerPage;

 		$sql = new Sql();

 		$results = $sql->select("SELECT SQL_CALC_FOUND_ROWS * from tb_products a
					  inner join tb_productscategories b  on a.idproduct = b.idproduct
					  inner join tb_categories c on c.idcategory = b.idcategory
					  where c.idcategory = :idcategory
					  limit $start, $itensPerPage;",[":idcategory"=>$this->getidcategory()]);

 	 $resultTotal = $sql->select("SELECT FOUND_ROWS() as nrtotal;");

 		return [

 				"data"	=> Product::checkList($results),
 				"total"	=> (int)$resultTotal[0]["nrtotal"],
 				"pages"	=> ceil($resultTotal[0]["nrtotal"] / $itensPerPage)

 			   ];



 	}

  //adicionando porduto a uma categoria
 	public function addProduct(Product $product){

 	 $sql = new Sql();

 	 $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct)
                VALUES(:idcategory, :idproduct)",
                [":idcategory"=>$this->getidcategory(),":idproduct"=>$product->getidproduct()
              ]);

 	}

 	public function removeProduct(Product $product){

 	 $sql = new Sql();

 	 $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",
         array(":idcategory"=>$this->getidcategory(),":idproduct"=>$product->getidproduct()));

 	}

 }

 ?>
