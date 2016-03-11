<?PHP
class Login extends MainConf
{
	public function onInit($param)
	{		
	
	}	
			
	public function onPreRender($param)
	{				
		parent::onPreRender($param);				
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)
		{   
		}		
	}
	
	
	public function loginProses($sender,$param)
	{
		$username = $param->CallbackParameter->username;
		$password = $param->CallbackParameter->password;	
		$passEncrypt=base64_encode(sha1($password,true));
		$response = $this->prosesLogin($username,$password);
		$param->ResponseData = array(
			"username"=>$username,
			"password"=>$password,
			"success"=>$response,
			"url"=>'index.php?page=Home');				
			
	 }
	  
	public function prosesLogin($username,$password)
   {   
		$authManager=$this->Application->getModule('auth');
		if(!$authManager->login($username,$password))
		{
			return "invalid";
		}
		else
		{
			return "success";
		}
	}
  
}
?>
