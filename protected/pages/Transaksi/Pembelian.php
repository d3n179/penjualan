<?PHP
class Pembelian extends MainConf
{

	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$sqlPemasok = "SELECT id,nama FROM tbm_pemasok WHERE deleted ='0' ";
			$arrPemasok = $this->queryAction($sqlPemasok,'S');
			$this->DDPemasok->DataSource = $arrPemasok;
			$this->DDPemasok->DataBind();
			
			/*$sqlBarang = "SELECT id,nama FROM tbm_barang WHERE deleted ='0' ";
			$arrBarang = $this->queryAction($sqlBarang,'S');*/
			$this->DDBarang->DataSource = '';
			$this->DDBarang->DataBind();
		}
		
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
		}
	}
	
	public function pemasokChanged()
	{
		$idPemasok = $this->DDPemasok->SelectedValue;
		
		if($idPemasok != '' )
		{
			$sqlBarang = "SELECT 
								tbd_pemasok_barang.id_barang AS id,
								CONCAT(tbm_barang.nama,' (',tbm_barang.ukuran,')') AS text
							FROM 
								tbd_pemasok_barang
							INNER JOIN tbm_barang ON tbm_barang.id = tbd_pemasok_barang.id_barang
							WHERE 
								tbm_barang.deleted ='0' 
								AND tbm_barang.st_barang = '0'
								AND tbd_pemasok_barang.deleted = '0' 
								AND tbd_pemasok_barang.id_pemasok = '$idPemasok' ";
			$arrBarang = $this->queryAction($sqlBarang,'S');
			
			$this->arrBarang->Value = json_encode($arrBarang,true);
			$this->DDBarang->DataSource = $arrBarang;
			$this->DDBarang->DataBind();
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					bindBrgTable();	
					');	
		}
		else
		{
			$this->arrBarang->Value = '';
			$this->DDBarang->DataSource = '';
			$this->DDBarang->DataBind();
		}
	}
	
	public function tambahBtnClicked($sender,$param)
	{
		$idBarang = $this->DDBarang->SelectedValue;
		$nmBarang = BarangRecord::finder()->findByPk($idBarang)->nama;
		$jumlah = $this->jumlah->Text;
		$harga = $this->toInt($this->harga->Text);
		$subtotal = $jumlah * $harga;
		$exist = '0';
		$arrPembelian = json_decode($this->arrPembelian->Value,true);
		if(count($arrPembelian) == 0)
			$id = 1;
		else
		{
			foreach($arrPembelian as $rowPembelian)
			{
				if($rowPembelian['idBarang'] == $idBarang)
					$exist = '1';
					
				$id = $rowPembelian['id'] + 1;
			}
		}
		
		if($exist == '0')
		{
			$arrPembelian[] = array("id"=>$id,
									"idBarang"=>$idBarang,
									"nmBarang"=>$nmBarang,
									"jumlah"=>$jumlah,
									"harga"=>$harga,
									"subtotal"=>$subtotal);
			$this->arrPembelian->Value = json_encode($arrPembelian,true);
			$this->DDBarang->SelectedValue = 'empty';
			$this->jumlah->Text = '';
			$this->harga->Text = '';
			$this->BindGrid();
			
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Barang tersebut Sudah Masuk Transaksi!");
					');	
		}
			
	}
	
	public function BindGrid()
	{
		$arrPembelian = json_decode($this->arrPembelian->Value ,true);
		
		$count = count($arrPembelian);
		$tblBody = '';
		if($count > 0)
		{
			foreach($arrPembelian as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nmBarang'].'</td>';
				$tblBody .= '<td>'.$row['jumlah'].'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['harga'],2).'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['subtotal'],2).'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
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
					jQuery("#'.$this->DDBarang->getClientID().'").select2("val", "empty");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');
		
	}
	
	public function deleteData($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arrPembelian = json_decode($this->arrPembelian->Value,true);
		
		foreach($arrPembelian as $subKey => $subArray)
		{
			if($subArray['id'] == $id)
				unset($arrPembelian[$subKey]);	
		}
		
		$this->arrPembelian->Value = json_encode($arrPembelian,true);
		$this->BindGrid();
	}
	
	public function submitBtnClicked($sender,$param)
	{
		$arrPembelian = $param->CallbackParameter->arr;
		if(count($arrPembelian) > 0)
		{
			$idPemasok = $this->DDPemasok->SelectedValue;
			$tglTrans = date('Y-m-d');
			$wktTrans = date('G:i:s');
			
			$PembelianRecord = new PembelianRecord();
			$PembelianRecord->id_pemasok = $idPemasok;
			$PembelianRecord->tgl_transaksi = $tglTrans;
			$PembelianRecord->wkt_transaksi = $wktTrans;
			$PembelianRecord->st_posting = '1';
			$PembelianRecord->save();
			$jmlTrans = 0;
			foreach($arrPembelian as $rowPembelian)
			{
				$PembelianDetailRecord = new PembelianDetailRecord();
				$PembelianDetailRecord->id_transaksi = $PembelianRecord->id;
				$PembelianDetailRecord->id_barang = $rowPembelian->idBarang;
				$PembelianDetailRecord->jml = $rowPembelian->jumlah;
				$PembelianDetailRecord->harga = $rowPembelian->harga;
				$PembelianDetailRecord->total = $rowPembelian->subtotal;
				$jmlTrans += $rowPembelian->subtotal;
				if($PembelianDetailRecord->save())
				{
					$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$rowPembelian->idBarang,'0');
					if(!$StockBarang)
					{
						$StockBarang = new StockBarangRecord();
						$StockBarang->id_barang = $rowPembelian->idBarang;
						$stokAwal = 0;
					}
					else
					{
						$stokAwal = $StockBarang->stok;
					}
					
					$stokAkhir = $stokAwal + $rowPembelian->jumlah;
					$StockBarang->stok = $stokAkhir;
					
					if($StockBarang->save())
					{
						$StockInOutRecord = new StockInOutRecord();
						$StockInOutRecord->id_barang = $rowPembelian->idBarang;
						$StockInOutRecord->stok_awal = $stokAwal;
						$StockInOutRecord->stok_in = $rowPembelian->jumlah;
						$StockInOutRecord->stok_out = 0 ;
						$StockInOutRecord->stok_akhir =$stokAkhir;
						$StockInOutRecord->keterangan = "Transaksi Pembelian";
						$StockInOutRecord->id_transaksi = $PembelianDetailRecord->id;
						$StockInOutRecord->jns_transaksi = "1";
						$StockInOutRecord->tgl = $tglTrans;
						$StockInOutRecord->wkt = $wktTrans;
						$StockInOutRecord->save();
					}
					
				}
					
			}
			
			$this->InsertJurnalBukuBesar(
											$PembelianRecord->id,
											'0',
											'1',
											$tglTrans,
											$wktTrans,
											'Pembelian Barang',
											$jmlTrans
											);
											
			$this->DDPemasok->SelectedValue = 'empty';
			$src = "index.php?page=Transaksi.CetakBillPembelian&id=".$PembelianRecord->id;
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#'.$this->DDPemasok->getClientID().'").select2("val", "empty");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("");
					BindGrid();
					jQuery("#modal-3  .modal-body").empty();
					jQuery("<iframe id=\"BillPdf\" style=\"width:100%;height:100%;\" frameborder=\"0\" src=\"'.$src.'\">").appendTo("#modal-3 .modal-body");
					jQuery("#modal-3").modal("show");
					unloadContent();
					toastr.info("Transaksi Pembelian Telah Dimasukkan ");
					');	
			
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					toastr.error("Barang Yang Akan Dibeli Belum Dimasukkan!");
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
