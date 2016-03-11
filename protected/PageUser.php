<?php
Prado::using('System.Security.TDbUserManager');
//session_start();

class PageUser extends TDbUser
{
	public function createUser($username)
	{
		$username = strtoupper($username);
		$userRecord=UserRecord::finder()->find('username = ?',$username);
		
		if($userRecord instanceof UserRecord) // if found
		{
			$user=new PageUser($this->Manager);
			$user->Name=strtoupper($username);  // set username	
			
			//$ssid = session_id();
			
			$name = strtoupper($userRecord->nama);
			$realname = strtoupper($userRecord->nama);
			$group = $userRecord->group;
			//$idObrav = $userRecord->openbravo_id;
			//$roleObrav = $userRecord->openbravo_role;
			//$businessPartner = $userRecord->business_partner;
			
			$user->setState('statPosition',$statPosition);
			$user->setState('userName',$name);
			//$user->setState('userId',$userRecord->id);
			$user->setState('userGroup',$group);
			$user->setState('user',$username);
			$user->setState('time',time());
			
			$userRecord->wkt_log=date('G:i:s');
			$userRecord->tgl_log=date('Y-m-d');
			$userRecord->st_log='1';
			//$userRecord->ssid=$ssid;
			$userRecord->Save();
			
			$UserGroup = $userRecord->group;
			//collect menu
			$sql = "SELECT
							tbm_user_menu_group.menu_id,
							tbm_menu.id,
							tbm_menu.parent_id,
							tbm_menu.nama,
							tbm_menu.link
						FROM
							tbm_user_menu_group
						INNER JOIN tbm_menu ON tbm_menu.id = tbm_user_menu_group.menu_id
						WHERE
							tbm_user_menu_group.deleted = '0'
						AND tbm_user_menu_group.st = '1'
						AND tbm_menu.parent_id != '0'
						AND tbm_user_menu_group.user_group_id = '$UserGroup' ";
			$sqlParent = $sql."GROUP BY
							tbm_menu.parent_id 
							ORDER BY tbm_menu.id ";
			
			$arrParent = MainConf::queryAction($sqlParent,'S');
			$arrMenu = 	MainConf::queryAction($sql,'S');	
			//$arr = "SELECT *,(SELECT count(*) FROM view_role_menu_event_sales b WHERE b.id_group='$group' AND b.nm_event='VIEW_DATA' AND b.id_parent=view_role_menu_event_sales.id AND b.st_delete='0') AS count_child FROM view_role_menu_event_sales WHERE id_group='$group' AND nm_event='VIEW_DATA' AND st_menu='1' AND st_delete='0' AND application = '1'  ORDER BY id_sort";
			//$sql = "SELECT *,(SELECT count(*) FROM view_role_menu_event b WHERE b.group_id='$group' AND b.nm_event='VIEW_DATA' AND b.id_parent=view_role_menu_event.id AND b.deleted='0') AS count_child FROM view_role_menu_event WHERE group_id='$group' AND nm_event='VIEW_DATA' AND st_menu='1' AND deleted='0' AND application='1' ORDER BY nm_menu";
			$UserMenu = array('parent' =>$arrParent,'menu' => $arrMenu);
			
			
			$user->setState('UserMenu',$UserMenu);
			
			$user->IsGuest=false;  
			return $user;
		}
		else
			return null;
	}
	
	public function validateUser($username,$password)
	{
		//$passVal=base64_encode(sha1($password,true));
		$passVal = md5($password);
		//var_dump($passVal);
		return UserRecord::finder()->findBy_username_AND_password($username,$passVal)!==null;		
	}
	
	
	public function getIsUserName()
	{
		return $this->getState('userName');
	}
	
	public function getIsRealName()
	{
		return $this->getState('userName');
	}
	
	public function getIsIdUser()
	{
		return $this->getState('userId');
	}
	
	public function getIsUser()
	{
		return $this->getState('user');
	}
	
	public function getIsUserGroup()
	{
			return $this->getState('userGroup');
	}
	
	public function getIsAppAuth()
	{
		return $this->getState('AppAuth');
	}
	
	public function getIsAppRole()
	{
		return $this->getState('authorized');
	}
	
	public function getIsAdmin()
	{
		return $this->isInRole('admin');
	}
	
	public function getIsPegawai()
	{
		return $this->isInRole('pegawai');
	}
	
	public function getUserMenu()
	{
		return $this->getState('UserMenu');
	}
	
	public function getTime()
	{
		return $this->getState('time');
	}
	
	public function setTime()
	{
		return $this->getState('time');
	}
	
	public function setStLog()
	{
		return $this->getState('time');
	}
	
}
?>
