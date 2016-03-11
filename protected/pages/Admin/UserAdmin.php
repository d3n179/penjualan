<?PHP
class UserAdmin extends MainConf
{

	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$sqlUserGroup = "SELECT
								tbm_user_group.id,
								tbm_user_group.nama
							FROM 
								tbm_user_group
							WHERE 
								tbm_user_group.deleted ='0'
							ORDER BY
								tbm_user_group.id ASC ";
			$arrGroup = $this->queryAction($sqlUserGroup,'S');
			$this->DDUserGroup->DataSource = $arrGroup;
			$this->DDUserGroup->dataBind(); 
		}
		
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			/*$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#table-1 tbody").append("'.$tblBody.'");');	*/
		}
	}
	
	public function onLoadComplete($param)
	{
		parent::onLoadComplete($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			
		}
	}
	
	public function BindGridDefault($sender,$param)
	{
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#table-1").dataTable().fnDestroy();
						jQuery("#table-1 tbody").empty();
						jQuery("#table-1 tbody").append("'.$tblBody.'");
						BindGrid();');	
	}
	
	public function BindGrid()
	{
		$sql = "SELECT 
					tbd_user.username,
					tbd_user.nama,
					tbd_user.group,
					tbm_user_group.nama AS group_nama,
					tbd_user.status,
					tbd_user.tgl_log,
					tbd_user.wkt_log
				FROM 
					tbd_user
				INNER JOIN tbm_user_group ON tbm_user_group.id = tbd_user.group
				WHERE 
					tbd_user.deleted = '0' 
				ORDER BY 
					tbd_user.username ASC ";
		$Record = $this->queryAction($sql,'S');
		
		$count = count($Record);
		$tblBody = '';
		if($count > 0)
		{
			foreach($Record as $row)
			{
				if($row['tgl_log'] != '' && $row['tgl_log'] != '0000-00-00')
					$tglLog = $this->ConvertDate($row['tgl_log'],'1')." ".$row['wkt_log'];
				else
					$tglLog = '';
				
				if($row['status'] == '1')
					$checked = 'checked';
				else
					$checked = '';
					
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['username'].'</td>';
				$tblBody .= '<td>'.$row['nama'].'</td>';
				$tblBody .= '<td>'.$row['group_nama'].'</td>';
				$tblBody .= '<td>'.$tglLog.'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<div class=\"col-sm-5\"><div class=\"make-switch\" data-on-label=\"<i class=\'entypo-check\'></i>\" data-off-label=\"<i class=\'entypo-cancel\'></i>\"><input type=\"checkbox\" onchange = \"onChecked(\''.$row['username'].'\',this);\" '.$checked.'/></div></div>';
				$tblBody .= '</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"editClicked(\''.$row['username'].'\')\"><i class=\"entypo-pencil\" ></i>Edit</a>&nbsp;&nbsp;';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked(\''.$row['username'].'\')\"><i class=\"entypo-cancel\"></i>Delete</a>';				
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
	
	public function checkedChanged($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$st = $param->CallbackParameter->st;
		
		$UserRecord = UserRecord::finder()->findByPk($id);
		if($UserRecord)
		{
			$UserRecord->status = $st;
			$UserRecord->save();
		}
	}
	
	public function editForm($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$Record = UserRecord::finder()->findByPk($id);
		if($Record)
		{
			$this->modalJudul->Text = 'Edit User';
			$this->idUser->Value = $id;
			$this->username->Text = $Record->username;
			$this->password->Text = '';
			$this->nama->Text = $Record->nama;
			$this->DDUserGroup->SelectedValue = $Record->group;
			$UserGroupNama = UserGroupRecord::finder()->findByPk($Record->group)->nama;
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					jQuery("#'.$this->DDUserGroup->getClientID().'").select2("data", {id: '.$Record->group.', text: "'.$UserGroupNama.'"});
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
		$Record = UserRecord::finder()->findByPk($id);
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
		$username = $this->username->Text;
		$password = md5($this->password->Text);
		$nama = trim($this->nama->Text);
		$UserGroup = $this->DDUserGroup->SelectedValue;
		if($this->idUser->Value != '')
		{
			$Record= UserRecord::finder()->findByPk($this->idUser->Value);
			$msg = "Data Berhasil Diedit";
		}
		else
		{
			$Record = new UserRecord();
			$msg = "Data Berhasil Disimpan";
			$Record->tgl_log = '0000-00-00';
			$Record->wkt_log = '00:00:00';
		}
		
		$Record->username = $username;
		$Record->password = $password;
		$Record->nama = $nama;
		$Record->group = $UserGroup;
		$Record->status = '1';
		$Record->tgl_create = date('Y-m-d');
		$Record->wkt_create = date('G:i:s');
		
		$Record->save(); 
		
		$this->nama->Text = '';
		$this->password->Text = '';
		$this->username->Text = '';
		$this->DDUserGroup->SelectedValue = '';
		
		$tblBody = $this->BindGrid();
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("'.$msg.'");
					jQuery("#modal-1").modal("hide");
					jQuery("#'.$this->DDUserGroup->getClientID().'").val("empty");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();');	
	}
	
}
?>
