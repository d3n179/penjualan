<?PHP
class MasterKategoriCetakan extends MainConf
{

	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		
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
		$KategoriCetakanRecord = KategoriCetakanRecord::finder()->findAll('deleted = ? ORDER BY id ASC','0');
		
		$count = count($KategoriCetakanRecord);
		$tblBody = '';
		if($count > 0)
		{
			foreach($KategoriCetakanRecord as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row->nama.'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row->id.')\"><i class=\"entypo-pencil\" ></i>Edit</a>&nbsp;&nbsp;';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked('.$row->id.')\"><i class=\"entypo-cancel\"></i>Delete</a>';				
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
	
	public function editForm($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$KategoriCetakanRecord = KategoriCetakanRecord::finder()->findByPk($id);
		if($KategoriCetakanRecord)
		{
			$this->modalJudul->Text = 'Edit Kategori Cetakan';
			$this->idJenis->Value = $id;
			$this->nama->Text = $KategoriCetakanRecord->nama;
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
		$KategoriCetakanRecord = KategoriCetakanRecord::finder()->findByPk($id);
		if($KategoriCetakanRecord)
		{
			$KategoriCetakanRecord->deleted = '1';
			$KategoriCetakanRecord->save();
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
		
		if($this->idJenis->Value != '')
		{
			$KategoriCetakanRecord = KategoriCetakanRecord::finder()->findByPk($this->idJenis->Value);
			$msg = "Data Berhasil Diedit";
		}
		else
		{
			$KategoriCetakanRecord = new KategoriCetakanRecord();
			$msg = "Data Berhasil Disimpan";
		}
		
		$KategoriCetakanRecord->nama = $nama;
		$KategoriCetakanRecord->save(); 
		
		$this->nama->Text = '';
		
		$KategoriCetakanRecord = KategoriCetakanRecord::finder()->findAll('deleted = ?','0');
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
	
}
?>
