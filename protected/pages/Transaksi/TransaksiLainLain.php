<?PHP
class TransaksiLainLain extends MainConf
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
		}
	}
	
	public function submitBtnClicked($sender,$param)
	{
		$arrTransaksi = $param->CallbackParameter->arr;
		if(count($arrTransaksi) > 0)
		{
			foreach($arrTransaksi as $row)
			{
				$wktTrans = date('G:i:s');
				$tgl = $row->tglTransaksi;
				$tgl1 = trim(str_replace("/","-",$tgl));
				$tgl1 = explode("-",$tgl1);
				$tgl1 = $tgl1[1]."-".$tgl1[0]."-".$tgl1[2];
				$tgl1 = $this->ConvertDate($tgl1,'2');
				
				$TransaksiLuarRecord = new TransaksiLuarRecord();
				$TransaksiLuarRecord->sumber_transaksi = $row->sumberTransaksi;
				$TransaksiLuarRecord->jns_transaksi = $row->jnsTransaksi;
				$TransaksiLuarRecord->tgl_transaksi = $tgl1;
				$TransaksiLuarRecord->wkt_transaksi = $wktTrans;
				$TransaksiLuarRecord->keterangan = $row->keteranggan;
				$TransaksiLuarRecord->jml_transaksi = $row->jmlTransaksi;
				$TransaksiLuarRecord->st_posting = '1';
				$TransaksiLuarRecord->save();
				
				$this->InsertJurnalBukuBesar(
											$TransaksiLuarRecord->id,
											$row->sumberTransaksi,
											$row->jnsTransaksi,
											$tgl1,
											$wktTrans,
											$row->keteranggan,
											$row->jmlTransaksi
											);
				
			}
			
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("");
					BindGrid();
					unloadContent();
					toastr.info("Transaksi Telah Dimasukkan ");
					');	
			
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					toastr.error("Transaksi Belum Dimasukkan!");
					');	
		}
		
	}
	
	public function setHarga($sender,$param)
	{
		$idBarang = $param->CallbackParameter->id;
		$BarangRecord = BarangRecord::finder()->findByPk($idBarang);
		$this->nmBarangharga->Text = $BarangRecord->nama;
		$this->idSetBarang->Value = $idBarang;
		$this->CBhargaset->SelectedValue = $BarangRecord->st_harga ;
			
		$sqlHarga = "SELECt 
						tbm_barang_harga.id,
						tbm_barang_harga.jml,
						tbm_barang_harga.harga
					FROM
						tbm_barang_harga
					WHERE 
						tbm_barang_harga.id_barang = '$idBarang' 
						AND tbm_barang_harga.deleted ='0' 
						ORDER BY tbm_barang_harga.jml ASC ";
						
		$arrHarga = $this->queryAction($sqlHarga,'S');
		if($arrHarga)
		{
			$this->arrHarga->Value = json_encode($arrHarga,true);
			$this->bindHarga();
		}
			
	}
	
	public function deleteHarga($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arr = json_decode($this->arrHarga->Value,true);
		
		foreach($arr as $subKey => $subArray)
		{
			if($subArray['id'] == $id)
				unset($arr[$subKey]);	
		}
		
		$this->arrHarga->Value = json_encode($arr,true);
		$this->bindHarga();
	}
	
	public function bindHarga()
	{
		$arr = json_decode($this->arrHarga->Value,true);
		
		$tblBody = '';
		if(count($arr) > 0)
		{
			foreach($arr as $row)
			{
					$tblBody .= '<tr>';
					$tblBody .= '<td>'.$row['jml'].'</td>';
					$tblBody .= '<td>'.$row['harga'].'</td>';
					$tblBody .= '<td>';
					$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteHarga('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>';	
					$tblBody .=	'</td>';			
					$tblBody .= '</tr>';
			}
		}
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#tableHarga").dataTable().fnDestroy();
					jQuery("#tableHarga tbody").empty();
					jQuery("#tableHarga tbody").append("'.$tblBody.'");
					BindGridHarga();');	
	}
	
	public function submitHargaBtnClicked($sender,$param)
	{
		$idBarang = $this->idSetBarang->Value;
		$arr = json_decode($this->arrHarga->Value,true);
		$stHarga = $this->CBhargaset->SelectedValue; 
			
		if(count($arr) > 0)
		{
			$BarangRecord = BarangRecord::finder()->findByPk($idBarang);
			$BarangRecord->st_harga = $stHarga;
			$BarangRecord->save();
			
			$sqlDelete = "DELETE FROM tbm_barang_harga WHERE id_barang = '$idBarang' ";
			$this->queryAction($sqlDelete,'C');
			
			foreach($arr as $row)
			{
				$hargaRecord = new BarangHargaRecord();
				$hargaRecord->id_barang = $idBarang;
				$hargaRecord->jml = $row['jml'];
				$hargaRecord->harga = $row['harga'];
				$hargaRecord->save();
			}
			$this->arrHarga->Value = '';
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#tableHarga").dataTable().fnDestroy();
					jQuery("#tableHarga tbody").empty();
					jQuery("#tableHarga tbody").append("");
					BindGridHarga();
					jQuery("#modal-2").modal("hide");
					toastr.info("Harga Barang Telah Dimasukkan");
					');
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Harga Barang Belum Dimasukkan");
					');
		}
	}
	
}
?>
