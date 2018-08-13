<?php 


 /**
  * 
  */
 
 namespace Hcode\Model;
 Use \Hcode\DB\Sql;
 Use \Hcode\Model;
 Use \Hcode\Mailer;

 class User extends Model{
 	
 	const SESSION = "user";
 	const SECRET = "HcodePhp7_secret";

 	function __construct(){
 		# code...
 	}

 	public static function getFromSession(){

 		$user = new User();

 		if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

 			
 			$user->setData($_SESSION[User::SESSION]);
 			
 		}

 		return $user;
 	}

 	public static function checkLogin($inadmin = true){

 		if (
 			!isset($_SESSION[User::SESSION]) 
 	 		|| 
 	 		!$_SESSION[User::SESSION] 
 	 		|| 
 	 		!(int)$_SESSION[User::SESSION]["iduser"] > 0 
 	 	) {
 			
 			// não esta logado 
 			return false;

 		}else{

 			if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true) {
 				
 				return true;

 			}else if($inadmin === false){

 				return true;

 			}else{

 				return false;
 			}

 		}

 	}

 	public static function Login ($login, $password){

 		$sql = new Sql();

 		$results = $sql->select("select * from tb_users where deslogin = :LOGIN",array(
 		 ":LOGIN" => $login
 		));

 		if (count($results) === 0) {

 		 throw new \Exception("Usuario inexistente ou senha inválida", 1);
 		 
 		}


 		$data = $results[0];

 		/*$password = password_hash($password,PASSWORD_DEFAULT,["cost"=>12]);
 		$data["despassword"] = password_hash($data["despassword"],PASSWORD_DEFAULT,["cost"=>12]);*/

 		/*echo $password."<br><br>";
 		echo $data["despassword"];
 		exit;*/


 		if(password_verify($password,$data["despassword"]) === true){

 		 $user = new User();

 		 $user->setData($data); 
 		 /*echo "<pre>";
 		 print_r($user);*/

 		 $_SESSION[User::SESSION] = $user->getValues();

 		 return $user;

 		}else{

 		 throw new \Exception("Usuario inexistente ou senha inválida", 1);
 		}
 	}




 	public static function verifyLogin($inadmin = true){
 	 // se a sessão não existir ou não for definida ou estiver vazia redireciona para o local do header	
 	 if(User::checkLogin($inadmin)) {
 	 	
 	 	header("Location: /admin/login");
 	 	exit;

 	 }
  

 	}

 	public static function logout(){
 		$_SESSION[User::SESSION] = NULL;
 	}


 	public static function listAll(){

 		$sql = new Sql(); 
 		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
 	}


 	public function save(){

 		$sql = new Sql();

 		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
 			 ":desperson"	=>$this->getdesperson(),
 			 ":deslogin"	=>$this->getdeslogin(),
 			 ":despassword"	=>$this->getdespassword(),
 			 ":desemail"	=>$this->getdesemail(),
 			 ":nrphone"		=>$this->getnrphone(),
 			 ":inadmin"		=>$this->getinadmin()

 		));

 		$this->setData($results[0]);

 	}

 	public function get($iduser){

 	 $sql = new Sql();

 	 $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser",array(":iduser"=>$iduser));

 	 $this->setData($results[0]);
 	}




 	public function update(){

 	 	$sql = new Sql();

 		$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
 			":iduser"		=>$this->getiduser(),
 			":desperson"	=>$this->getdesperson(),
 		    ":deslogin"		=>$this->getdeslogin(),
 			":despassword"	=>$this->getdespassword(),
 			":desemail"		=>$this->getdesemail(),
 			":nrphone"		=>$this->getnrphone(),
 			":inadmin"		=>$this->getinadmin()			

 		)); 		

 		$this->setData($results[0]);
 	}


 	public function delete(){

 		$sql = new Sql();

 		$sql->query("CALL sp_users_delete(:iduser)",array(":iduser"=>$this->getiduser()));
 	}


 	public static function getForgot($email){

 	 $sql = new Sql();

 	 $results = $sql->select("select * from tb_persons a inner join tb_users b using(idperson) where a.desemail = :email",array(
 	  ":email" =>$email
 	 ));


 	 if (count($results) === 0) {
 	 	
 	 	throw new \Exception("Não foi possivel recuperar a senha", 1);
 	 	
 	 }else{

 	 	$data = $results[0];
 	 	$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
 	 	 ":iduser"=>$data["iduser"],
 	 	 ":desip"=>$_SERVER["REMOTE_ADDR"]// pega o ip do usuário 
 	 	));

 	 	if (count($results2) === 0 ) {
 	 		
 	 		throw new \Exception("Não foi possivel recurerar a senha ", 1);
 	 		
 	 	}else{

 	 		$dataRecovery = $results2[0];


 	 		$code = $dataRecovery["idrecovery"];/*base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"], MCRYPT_MODE_ECB));*/

 	 		$link = "http://www.hcodecommerce.com.br:8080/admin/forgot/reset?code=$code";

 	 		$mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinir senha hcode", "forgot",array(
 	 			"name"=>$data["desperson"],
 	 			"link"=>$link
 	 		));

 	 		$mailer->send();

 	 		return $data;

 	 	}
 	 }

 	}



 	public static function validForgotDecrypt($code){
 	

 	 //$idRecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET,base64_decode($code), MCRYPT_MODE_ECB);
 	 //
 	 
 	 $idRecovery = $code;

 	 $sql = new Sql();

 	 $results = $sql->select("select * from tb_userspasswordsrecoveries a 
					inner join tb_users b using(iduser) 
					inner join tb_persons c using (idperson) 
					where 
 					a.idrecovery = :idrecovery
 					and a.dtrecovery IS NULL
 					and DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();",array(":idrecovery"=>$idRecovery));

 	 if (count($results) === 0) {
 		
 		throw new \Exception("Não foi possivel recuperar a senha ", 1);
 		
 	 }else{

 		return $results[0];
 	 }

 	}


 	public static function setForgotUsed($idrecovery){
      
     $sql = new Sql();

     $sql->query("UPDATE tb_userspasswordsrecoveries set dtrecovery = NOW() WHERE idrecovery = :idrecovery",array(":idrecovery"=>$idrecovery));




 	}


 	public function setPassword($password){

 		$sql = new Sql();

 		$sql->query("UPDATE tb_users SET despassword = :password where iduser = :iduser",array(
         ":password"=>$password,
         ":iduser"=>$this->getiduser()
 		));



 	}



 	


 }


 ?>