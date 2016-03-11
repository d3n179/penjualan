<?PHP
class MasterPemasok extends MainConf
{

	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$sql = "SELECT id,nama FROM tbm_barang WHERE deleted ='0' AND st_barang ='0' ";
			$arrBarang = $this->queryAction($sql,'S');
			$this->DDBarang->DataSource = $arrBarang;
			$this->DDBarang->DataBind();
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
					tbm_pemasok.id,
					tbm_pemasok.nama,
					tbm_pemasok.alamat,
					tbm_pemasok.telepon,
					tbm_pemasok.fax,
					tbm_pemasok.contact_person
				FROM 
					tbm_pemasok
				WHERE 
					tbm_pemasok.deleted = '0' 
				ORDER BY 
					tbm_pemasok.id ASC ";
		$Record = $this->queryAction($sql,'S');
		
		$count = count($Record);
		$tblBody = '';
		if($count > 0)
		{
			foreach($Record as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nama'].'</td>';
				$tblBody .= '<td>'.mysql_escape_string($row['alamat']).'</td>';
				$tblBody .= '<td>'.$row['telepon'].'</td>';
				$tblBody .= '<td>'.$row['fax'].'</td>';
				$tblBody .= '<td>'.$row['contact_person'].'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-pencil\" ></i>Edit</a>&nbsp;&nbsp;';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Delete</a>';				
				$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"detailClicked('.$row['id'].',\''.$row['nama'].'\')\"><i class=\"entypo-tag\"></i>Detail</a>&nbsp;&nbsp;';				
				
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
		$Record = PemasokRecord::finder()->findByPk($id);
		if($Record)
		{
			$this->modalJudul->Text = 'Edit Pemasok';
			$this->idPemasok->Value = $id;
			$this->nama->Text = $Record->nama;
			$this->alamat->Text = $Record->alamat;
			$this->telepon->Text = $Record->telepon;
			$this->fax->Text = $Record->fax;
			$this->contact_person->Text = $Record->contact_person;
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
		$Record = PemasokRecord::finder()->findByPk($id);
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
		$alamat = trim($this->alamat->Text);
		$telepon = $this->telepon->Text;
		$fax = $this->fax->Text;
		$contact_person = $this->contact_person->Text;
		if($this->idPemasok->Value != '')
		{
			$Record= PemasokRecord::finder()->findByPk($this->idPemasok->Value);
			$msg = "Data Berhasil Diedit";
		}
		else
		{
			$Record = new PemasokRecord();
			$msg = "Data Berhasil Disimpan";
		}
		
		$Record->nama = $nama;
		$Record->alamat = $alamat;
		$Record->telepon = $telepon;
		$Record->fax = $fax;
		$Record->contact_person = $contact_person;
		$Record->save(); 
		
		$this->nama->Text = '';
		$this->alamat->Text = '';
		$this->telepon->Text = '';
		$this->fax->Text = '';
		$this->contact_person->Text = '';
		
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
	
	public function BindGridBarangPemasok()
	{
		$idPemasok = $this->idBrngPemasok->Value;
		$sql = "SELECT 
					tbd_pemasok_barang.id,
					tbd_pemasok_barang.id_barang,
					tbm_barang.nama
				FROM 
					tbd_pemasok_barang
				INNER JOIN tbm_barang ON tbm_barang.id = tbd_pemasok_barang.id_barang 
				WHERE 
					tbd_pemasok_barang.deleted = '0' 
					AND tbd_pemasok_barang.id_pemasok = '$idPemasok'
				ORDER BY 
					tbd_pemasok_barang.id ASC ";
		$BarangRecord = $this->queryAction($sql,'S');
		
		$count = count($BarangRecord);
		$tblBody = '';
		if($count > 0)
		{
			foreach($BarangRecord as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nama'].'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteBarangClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
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
					jQuery("#tableBrngPemasok").dataTable().fnDestroy();
					jQuery("#tableBrngPemasok tbody").empty();
					jQuery("#tableBrngPemasok tbody").append("'.$tblBody.'");
					BindGridBrgPemasok();
					unloadContent();');	
	}
	
	public function tambahBtnClicked($sender,$param)
	{
		$idPemasok = $this->idBrngPemasok->Value;
		$idBarang = $this->DDBarang->SelectedValue;
		$PemasokBarangRecord = PemasokBarangRecord::finder()->find('id_barang = ? AND id_pemasok = ? AND deleted = ?',$idBarang,$idPemasok,'0');
		if(!$PemasokBarangRecord)
		{
			$PemasokBarangRecord = new PemasokBarangRecord();
			$PemasokBarangRecord->id_barang = $idBarang;
			$PemasokBarangRecord->id_pemasok = $idPemasok;
			$PemasokBarangRecord->save();
			
			$this->DDBarang->SelectedValue = 'empty';
			$this->BindGridBarangPemasok();
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					toastr.error("Barang Tersebut Sudah Dimasukkan !");
					');	
		}
		
	}
		public function deleteBarang($sender,$param)
		{
			$id = $param->CallbackParameter->id;
			$PemasokBarangRecord = PemasokBarangRecord::finder()->findByPk($id);
			$PemasokBarangRecord->deleted = '1';
			$PemasokBarangRecord->save();
			$this->BindGridBarangPemasok();
		}
	
	
}
?>
