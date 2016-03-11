<?php
Prado::using('Application.Portlets.Portlet');
class SidePortlet extends Portlet
{
	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			
		}
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$page = $this->Request['page'];	
			$MenuRecord = MenuRecord::finder()->find('link = ? AND deleted = ?',$page,'0');
			$this->userRealName->Text = $this->User->IsRealName;
			$parentId = $MenuRecord->parent_id;
			$menuId = $MenuRecord->id;
			$UserMenu = $this->User->UserMenu;
			/*$sqlParent = "SELECT 
								tbm_menu.id,
								tbm_menu.nama,
								tbm_menu.link,
								tbm_menu.icon
							FROM 
								tbm_menu 
							WHERE
								tbm_menu.parent_id = '0'
								AND tbm_menu.deleted = '0'
								AND tbm_menu.aktif ='1' ";*/
				
			$arrParent = $UserMenu['parent'];//MainConf::queryAction($sqlParent,'S');
			$SideMenuList = '';
			if($arrParent)
			{
				foreach($arrParent as $rowParent)
				{
					$idparent = $rowParent['parent_id'];
					
					if($idparent == $parentId)
						$classParent = 'class="opened active"';
					else
						$classParent = '';
					
					$modul = MenuRecord::finder()->findByPk($rowParent['parent_id']);	
					$SideMenuList .= '<li '.$classParent.'>
										<a href="#">
											<i class="'.$modul->icon.'"></i>
											<span class="title">'.$modul->nama.'</span>
										</a>';
										
					/*$sqlChild = "SELECT 
										tbm_menu.id,
										tbm_menu.nama,
										tbm_menu.link
									FROM 
										tbm_menu 
									WHERE
										tbm_menu.parent_id = '$idparent'
										AND tbm_menu.deleted = '0'
										AND tbm_menu.aktif ='1' ";*/
				
					$arrChild = $UserMenu['menu'];//MainConf::queryAction($sqlChild,'S');	
					if($arrChild)
					{	$SideMenuList .= '<ul>';
						foreach($arrChild as $rowChild)
						{				
							$idChild = $rowChild['id'];
							if($rowChild['parent_id'] == $idparent)
							{
								if($idChild == $menuId)
									$classChild = 'class="active"';
								else
									$classChild = '';
								$SideMenuList .= '	<li '.$classChild.'>
															<a href="index.php?page='.$rowChild['link'].'">
																<span class="title">'.$rowChild['nama'].'</span>
															</a>
														</li>';
							}
						}
						$SideMenuList .= '</ul>';
					}
					$SideMenuList .= '</li>';
				}
				
			} 
			$this->SideMenuLiteral->text = $SideMenuList;
		}
		
		if($this->Page->IsCallBack)    
		{	
			
		}
	}
	
	public function logoutProses($sender,$param)
	{
		MainConf::prosesLogout();
	}
	
}

?>
