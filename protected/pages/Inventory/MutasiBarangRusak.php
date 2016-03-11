<?PHP
class MutasiBarangRusak extends MainConf
{

	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$sql = "SELECT id,CONCAT(nama,' (',ukuran,')') AS nama FROM tbm_barang WHERE deleted ='0' ";
			$arrBarang = $this->queryAction($sql,'S');
			$this->DDBarang->DataSource = $arrBarang;
			$this->DDBarang->DataBind();
			
			$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#table-1 tbody").append("'.$tblBody.'");');
						
		}
		
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
				
		}
	}
	
	public function BindGrid()
	{
		$MutasiBarangRecord = MutasiBarangRecord::finder()->findAll('deleted = ? ORDER BY id ASC','0');
		
		$count = count($MutasiBarangRecord);
		$tblBody = '';
		if($count > 0)
		{
			foreach($MutasiBarangRecord as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row->tgl_transaksi.'</td>';
				$tblBody .= '<td>'.$row->wkt_transaksi.'</td>';
				$tblBody .= '<td>'.$row->jumlah_barang.'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"detailClicked('.$row->id.')\"><i class=\"entypo-doc-text-inv\"></i>Detail</a>&nbsp;&nbsp;';	
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
	
	public function tambahBtnClicked($sender,$param)
	{
		$barang = $this->DDBarang->SelectedValue;
		$BarangRecord = BarangRecord::finder()->findByPk($barang);
		$nmBarang = $BarangRecord->nama." (".$BarangRecord->ukuran.")";
		$jml = $this->jml->Text;
		$arrBarang = json_decode($this->arrBarang->Value,true);
		$stFind = '0';
		if(count($arrBarang) > 0)
		{
			foreach($arrBarang as $row)
			{
				if($row['id_barang'] == $barang)
					$stFind = '1';	
			}
		}
		
		if($stFind == '0')
		{
			$arrBarang[] = array('id_barang'=>$barang,'nama_barang'=>$nmBarang,'jml'=>$jml);
			$this->arrBarang->Value = json_encode($arrBarang,true);
			$this->bindBarangRusak();
			$this->DDBarang->SelectedValue = '';
			$this->jml->Text = '';
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.error("Barang Tersebut Sudah Dimasukkan !");');
		}
		
	}
	
	public function deleteBrngClicked($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arr = json_decode($this->arrBarang->Value,true);
		
		foreach($arr as $subKey => $subArray)
		{
			if($subArray['id_barang'] == $id)
				unset($arr[$subKey]);	
		}
		
		$this->arrBarang->Value = json_encode($arr,true);
		$this->bindBarangRusak();
	}
	
	
	public function bindBarangRusak()
	{
		$arr = json_decode($this->arrBarang->Value,true);
		if(count($arr) > 0)
		{
			$tblBody="";
			foreach($arr as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nama_barang'].'</td>';
				$tblBody .= '<td>'.$row['jml'].'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked('.$row['id_barang'].')\"><i class=\"entypo-cancel\"></i>Delete</a>';				
				$tblBody .=	'</td>';			
				$tblBody .= '</tr>';
			}
		}
		else
		{
			$tblBody = "";
		}
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#tableBrngRusak").dataTable().fnDestroy();
					jQuery("#tableBrngRusak tbody").empty();
					jQuery("#tableBrngRusak tbody").append("'.$tblBody.'");
					BindGridBrgRusak();');	
	}
	
	function detailClicked($sender,$param)
	{
		$idtrans = $param->CallbackParameter->id;
		$MutasiBarangDetailRecord = MutasiBarangDetailRecord::finder()->findAll('id_transaksi = ? AND deleted = ? ORDER BY id ASC',$idtrans,'0');
		
		$count = count($MutasiBarangDetailRecord);
		$tblBody = '';
		if($count > 0)
		{
			foreach($MutasiBarangDetailRecord as $row)
			{
				$BarangRecord = BarangRecord::finder()->findByPk($row->id_barang);
				$nmBarang = $BarangRecord->nama." (".$BarangRecord->ukuran.")";
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$nmBarang.'</td>';
				$tblBody .= '<td>'.$row->jml.'</td>';
				$tblBody .= '</tr>';
			}
		}
		else
		{
			$tblBody = '';
		}
		
		$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#modal-2").modal("show");
						jQuery("#tableBrng").dataTable().fnDestroy();
						jQuery("#tableBrng tbody").empty();
						jQuery("#tableBrng tbody").append("'.$tblBody.'");
						BindGridBrg();
						unloadContent();');	
		
	}
	
	public function submitBtnClicked($sender,$param)
	{
		$arr = json_decode($this->arrBarang->Value,true);
		
		if(count($arr) > 0)
		{
			$tglTrans = date("Y-m-d");
			$wktTrans = date("G:i:s"); 
			$MutasiBarangRecord = new MutasiBarangRecord();
			$MutasiBarangRecord->tgl_transaksi = $tglTrans;
			$MutasiBarangRecord->wkt_transaksi = $wktTrans;
			$MutasiBarangRecord->jumlah_barang = count($arr);
			$MutasiBarangRecord->save(); 
			
			foreach($arr as $row)
			{
				$MutasiBarangDetailRecord = new MutasiBarangDetailRecord();
				$MutasiBarangDetailRecord->id_transaksi = $MutasiBarangRecord->id;
				$MutasiBarangDetailRecord->id_barang = $row['id_barang'];
				$MutasiBarangDetailRecord->jml = $row['jml'];
				$MutasiBarangDetailRecord->save();
				
				$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$row['id_barang'],'0');
				if($StockBarang)
				{
					$stokAwal = $StockBarang->stok;
					$stokAkhir = $stokAwal - $row['jml'];
					$StockBarang->stok = $stokAkhir;
					
					if($StockBarang->save())
					{
						$StockInOutRecord = new StockInOutRecord();
						$StockInOutRecord->id_barang = $row['id_barang'];
						$StockInOutRecord->stok_awal = $stokAwal;
						$StockInOutRecord->stok_in = 0;
						$StockInOutRecord->stok_out = $row['jml'];
						$StockInOutRecord->stok_akhir = $stokAkhir;
						$StockInOutRecord->keterangan = "Mutasi Barang Rusak";
						$StockInOutRecord->id_transaksi = $MutasiBarangDetailRecord->id;
						$StockInOutRecord->jns_transaksi = "2";
						$StockInOutRecord->tgl = $tglTrans;
						$StockInOutRecord->wkt = $wktTrans;
						$StockInOutRecord->save();
					}
				}
				
			}
			$tblBody = $this->BindGrid();
			
			$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.info("Mutasi Barang Telah Diproses !");
						jQuery("#modal-1").modal("hide");
						jQuery("#table-1").dataTable().fnDestroy();
						jQuery("#table-1 tbody").empty();
						jQuery("#table-1 tbody").append("'.$tblBody.'");
						BindGrid();');	
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.error("Barang Belum Dimasukkan !");');	
		}
	}
	
}
?>
