<?php
Prado::using('System.Security.TAuthManager');
class MyAuthManager extends TAuthManager {

	public function loginCheck($username,$password)
	{
		$UserManager = $this->getUserManager();
		if ($UserManager->validateUser($username,$password))
		{
			$user=$UserManager->getUser($username);
			if (!$user->IsGuest)
			{
				$user->Time = time();
			}
			
			$this->updateSessionUser($user);
			$this->getApplication()->setUser($user);		
			return true;
		}
		else
			return false;
  	}

	public function OnAuthenticate($param)
	{
		parent::OnAuthenticate($param);
		if (!$this->Application->User->IsGuest)
		{
			if (($this->Application->User->Time + $this->AuthExpire) < time())
			{
				
				$UserManager = $this->getUserManager();
				$user=$UserManager->getUser($this->Application->User->Name);
				$userRecord = UserRecord::finder()->findByPk($user->Name);
				$userRecord->st_log='0';
				$userRecord->ssid='';
				$userRecord->Save();
				//$this->Application->User->StLog = '0';
				$this->logout();
				/*
				$this->Application->User->Time = time();
				$this->updateSessionUser($this->Application->User);*/
			}
			else
			{
				$this->Application->User->Time = time();
				$this->updateSessionUser($this->Application->User);
			}
		}
  }
	
}
?>
