<?php


 use \Hcode\PageAdmin;
 use \Hcode\Model\User;
 use \Hcode\Model\Category;
 use \Hcode\Model\Product;


 $app->get("/admin/categories",function (){

   $page = new PageAdmin();

   $categories = Category::listAll();

   $page->setTpl("categories",[
     "categories"=>$categories
   ]);


 });


  $app->get("/admin/categories/create",function(){

   User::verifyLogin();

   $page = new PageAdmin();

   $page->setTpl("categories-create");

  });


  $app->post("/admin/categories/create",function(){

   User::verifyLogin();

   $category = new Category();

   $category->setData($_POST);

   $category->save();

   header("Location: /admin/categories");
   exit;


  });


  $app->get("/admin/categories/:idcategory/delete",function ($idcategory){

   User::verifyLogin();

   $category = new Category();

   $category->get((int)$idcategory);

   $category->delete();

   header("Location: /admin/categories");
   exit;

  });

  $app->get("/admin/categories/:idcategory/products",function($idcategory){

   User::verifyLogin();

   $category = new Category();

   $category->get((int)$idcategory);

   $page = new PageAdmin();

   $page->setTpl("categories-products",[
                  "category"           =>$category->getValues(),
                  "productsRelated"    =>$category->getProducts(),
                  "productsNotRelated" =>$category->getProducts(false)

    ]);

  });


  $app->get("/admin/categories/:idcategory",function ($idcategory){

   User::verifyLogin();

   $category = new Category();

   $category->get((int)$idcategory);

   $page = new PageAdmin();

   $page->setTpl("categories-update",array("category"=>$category->getValues()));



  });





  $app->post("/admin/categories/:idcategory",function ($idcategory){

   User::verifyLogin();

   $category = new Category();

   $category->get((int)$idcategory);

   $category->setData($_POST);

   $category->save();

   header("Location: /admin/categories");

   exit;

  });






  // adicionando o produto a uma categoria
   $app->get("/admin/categories/:idcategory/products/:idproduct/add",function($idcategory,$idproduct){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $product = new Product();

    $product->get((int)$idproduct);

    $category->addProduct($product);

    header("Location: /admin/categories/".$idcategory."/products");

    exit;

   });

  // removendo o produto de  uma categoria
   $app->get("/admin/categories/:idcategory/products/:idproduct/remove",function($idcategory,$idproduct){

    User::verifyLogin();

    $category = new Category();

    $category->get((int)$idcategory);

    $product = new Product();

    $product->get((int)$idproduct);

    $category->removeProduct($product);

    header("Location: /admin/categories/".$idcategory."/products");

    exit;

   });


 ?>
