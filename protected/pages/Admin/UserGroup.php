<?PHP
class UserGroup extends MainConf
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
			$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#table-1 tbody").append("'.$tblBody.'");');	
		}
	}
	
	public function BindGrid()
	{
		$sql = "SELECT 
					tbm_user_group.id,
					tbm_user_group.nama
				FROM 
					tbm_user_group
				WHERE 
					tbm_user_group.deleted = '0' 
				ORDER BY 
					tbm_user_group.id ASC ";
		$Record = $this->queryAction($sql,'S');
		
		$count = count($Record);
		$tblBody = '';
		if($count > 0)
		{
			foreach($Record as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nama'].'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-pencil\" ></i>Edit</a>&nbsp;&nbsp;';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Delete</a>&nbsp;&nbsp;';				
				$tblBody .= '<a href=\"#\" class=\"btn btn-green btn-sm btn-icon icon-left\" OnClick=\"detailClicked('.$row['id'].')\"><i class=\"entypo-check\"></i>Set Hak Akses</a>';				
				$tblBody .=	'</td>';			
				$tblBody .= '</tr>';
			}
		}
		else
		{
			$tblBody = '';
		}
		
		return 	$tblBody;
	}
	
	public function BindGridHakAkses($sender,$param)
	{
		$idGroup = $param->CallbackParameter->id;
		$this->idUserGroup->Value = $idGroup;
		$sql = "SELECT 
					tbm_user_menu_group.id,
					tbm_user_menu_group.menu_id,
					tbm_menu.id AS menu_id,
					tbm_menu.nama,
					tbm_menu.link,
					tbm_menu.parent_id,
					tbm_user_menu_group.st
				FROM 
					tbm_menu
				LEFT JOIN tbm_user_menu_group ON (tbm_user_menu_group.menu_id = tbm_menu.id AND tbm_user_menu_group.user_group_id = '$idGroup')
				WHERE 
					tbm_menu.deleted = '0'
					AND tbm_menu.parent_id != '0'
				ORDER BY 
					tbm_user_menu_group.id ASC ";
		$Record = $this->queryAction($sql,'S');
		$tblBody = '';
		if($Record)
		{
			foreach($Record as $row)
			{
				$modul = MenuRecord::finder()->findByPk($row['parent_id'])->nama;;
				if($row['st'] == '1')
					$checked = 'checked';
				else
					$checked = '';
				
				if($row['id'] == '')
					$idMenuGroup = '0';
				else
					$idMenuGroup = $row['id'];
					
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$modul.'</td>';
				$tblBody .= '<td>'.$row['nama'].'</td>';
				$tblBody .= '<td>'.$row['link'].'</td>';
				$tblBody .= '<td>';
				//$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-pencil\" ></i>Edit</a>&nbsp;&nbsp;';
				$tblBody .= '<div class=\"col-sm-5\"><div class=\"make-switch\" data-on-label=\"<i class=\'entypo-check\'></i>\" data-off-label=\"<i class=\'entypo-cancel\'></i>\"><input type=\"checkbox\" onchange = \"onChecked('.$idMenuGroup.','.$row['menu_id'].',this);\" '.$checked.'/></div></div>';
				$tblBody .=	'</td>';			
				$tblBody .= '</tr>';
			}
		}
		else
		{
			$tblBody = '';
		}
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#tableHak").dataTable().fnDestroy();
					jQuery("#tableHak tbody").empty();
					jQuery("#tableHak tbody").append("'.$tblBody.'");
					BindGridHakAkses();
					jQuery("#modal-2").modal("show");
					unloadContent();
					');
	}
	
	public function checkedChanged($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$menuId = $param->CallbackParameter->menu_id;
		$st = $param->CallbackParameter->st;
		
		if($id != '0')
			$UserMenuGroupRecord = UserMenuGroupRecord::finder()->findByPk($id);
		else
		{
			$idUserGroup = $this->idUserGroup->Value;
			$UserMenuGroupRecord = new UserMenuGroupRecord();
			$UserMenuGroupRecord->user_group_id = $idUserGroup;
			$UserMenuGroupRecord->menu_id = $menuId;
		}
		
		$UserMenuGroupRecord->st = $st;
		$UserMenuGroupRecord->save();
		
	}
	
	public function editForm($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$Record = UserGroupRecord::finder()->findByPk($id);
		if($Record)
		{
			$this->modalJudul->Text = 'Edit User Group';
			$this->idGroup->Value = $id;
			$this->nama->Text = $Record->nama;
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					jQuery("#modal-1").modal("show");
					');	
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					toastr.error("Data Tidak Ditemukan");
					');	
		}
	}
	
	public function deleteData($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$Record = UserGroupRecord::finder()->findByPk($id);
		if($Record)
		{
			$Record->deleted = '1';
			$Record->save();
			$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Data Telah Dihapus");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');
		}
		else
		{
			
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					toastr.error("Data gagal Dihapus");
					');
		}
	}
	
	public function submitBtnClicked($sender,$param)
	{
		$nama = trim($this->nama->Text);
		if($this->idGroup->Value != '')
		{
			$Record= UserGroupRecord::finder()->findByPk($this->idGroup->Value);
			$msg = "Data Berhasil Diedit";
		}
		else
		{
			$Record = new UserGroupRecord();
			$msg = "Data Berhasil Disimpan";
		}
		
		$Record->nama = $nama;
		$Record->save(); 
		
		$this->InsertMenuRole($Record->id);
		$this->nama->Text = '';
		
		$tblBody = $this->BindGrid();
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("'.$msg.'");
					jQuery("#modal-1").modal("hide");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();');	
	}
	
	
	public function InsertMenuRole($idGroup)
	{
		$sql = "SELECT
					tbm_menu.id,
					tbm_menu.nama
				FROM
					tbm_menu
				WHERE
					tbm_menu.deleted = '0' 
					AND tbm_menu.parent_id != '0' 
				ORDER BY tbm_menu.id ASC ";
		$arr = $this->queryAction($sql,'S');
		
		if($arr)
		{
			foreach($arr as $row)
			{
				$idMenu = $row['id'];
				$UserMenuGroupRecord = UserMenuGroupRecord::finder()->find('user_group_id = ? AND menu_id = ? AND deleted = ?',$idGroup,$idMenu,'0');
				if(!$UserMenuGroupRecord)
				{
					$UserMenuGroupRecord = new UserMenuGroupRecord();
					$UserMenuGroupRecord->user_group_id = $idGroup;
					$UserMenuGroupRecord->menu_id = $idMenu;
					$UserMenuGroupRecord->st = '0';
					$UserMenuGroupRecord->save() ;
				}
			}
		}
		
	}
}
?>
