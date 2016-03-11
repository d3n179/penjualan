<?PHP
class Penjualan extends MainConf
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
			$sqlPelanggan = "SELECT id,nama FROM tbm_pelanggan WHERE deleted ='0' ";
			$arrPelanggan = $this->queryAction($sqlPelanggan,'S');
			$this->DDPelanggan->DataSource = $arrPelanggan;
			$this->DDPelanggan->DataBind();
			
			$sqlBarang = "SELECT id,CONCAT(nama,' (',ukuran,')') AS text FROM tbm_barang WHERE deleted ='0' ";
			$arrBarang = $this->queryAction($sqlBarang,'S');
			$this->DDBarang->DataSource = $arrBarang;
			$this->DDBarang->DataBind();
			$this->arrBarang->Value = json_encode($arrBarang,true);
		}
	}
	
	public function barangChanged($sender,$param)
	{
		$idBarang = $param->CallbackParameter->id;
		$index = $param->CallbackParameter->index;
		$BarangRecord = BarangRecord::finder()->findByPk($idBarang);
		$jnsBarang = $BarangRecord->st_barang;
		$this->jumlah->Text = '';
		$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$idBarang,'0');
		
		if($jnsBarang == '0')
		{
			if(!$StockBarang)
				$stok = 0;
			else
				$stok = $StockBarang->stok;
				
				//$this->harga->ReadOnly = true;
				//$this->harga->Enabled = false;
				$hargaSt = true;
		}
		else
		{
			$stok = 0;
			$hargaSt = false;
			//$this->harga->ReadOnly = false;
			//$this->harga->Enabled = true;
		}
			
			
		$sqlHarga = "SELECT
							tbm_barang_harga.id,
							tbm_barang_harga.jml,
						IF (
							tbm_barang_harga.id_satuan = '0',
							'Eceran',
							tbm_satuan.nama
						) AS text,
						 harga
						FROM
							tbm_barang_harga
						LEFT JOIN tbm_satuan ON tbm_satuan.id = tbm_barang_harga.id_satuan
						WHERE
							tbm_barang_harga.deleted = '0'
						AND tbm_barang_harga.id_barang = '$idBarang' ";
		$arrHarga = $this->queryAction($sqlHarga,'S');
		
		if($arrHarga)
		{	
			$arrPenjualan = json_decode($this->arrPenjualan->Value,true);
			if(count($arrPenjualan > 0))
			{
				foreach($arrPenjualan as $rowJual)
				{
					if($rowJual['idBarang'] == $idBarang && $jnsBarang == '0' )
					{
						$idHargaTemp = $rowJual['idHarga'];
						$BarangHargaRecord = BarangHargaRecord::finder()->findByPk($idHargaTemp);
						$jmlTemp = $BarangHargaRecord->jml *  $rowJual['jumlah'];
						$stok = $stok - $jmlTemp;
					}
				}
			}
			
			//$this->stok->Text = $stok;
			/*if($BarangRecord->st_harga == '0')
			{
				$this->DDUkuran->Enabled = false;
				$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#panelUkuran").hide();
						');
			}
			else
			{*/
				$this->DDUkuran->DataSource=$arrHarga;
				$this->DDUkuran->DataBind();
				$this->DDUkuran->Enabled = true;
				$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#ukuranBrg'.$index.'").select2({allowClear: true,data: '.json_encode($arrHarga,true).',width: "100px"}).on("change", function(e) 
						{
							if(e.val == null || e.val == "")
								jQuery("#ukuranBrg'.$index.'").val("");
							else
							{
								ukuranChangedCallback(e.val,'.$index.');
							}
						});
						jQuery("#stokBrg'.$index.'").text('.$stok.');
						jQuery("#hargaBrg'.$index.'").prop("disabled","'.$hargaSt.'");
						');
			//}
		}
		else
		{
			$this->stok->Text = '';
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Harga Barang Belum Diset !");
					');	
		}
	}
	
	public function ukuranChanged($sender,$param)
	{
		$ukuran = $param->CallbackParameter->id;
		$index = $param->CallbackParameter->index;
		$Harga = BarangHargaRecord::finder()->findByPk($ukuran);
		$hargaCurrency = $this->formatCurrency($Harga->harga,2);
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#hargaBrg'.$index.'").val("'.$hargaCurrency.'");
					');	
	} 
	
	public function cekStok($sender,$param)
	{
		$idBarang = $param->CallbackParameter->idBarang;
		$idHarga = $param->CallbackParameter->idHarga;
		$stok = $param->CallbackParameter->stok;
		$jumlah = $param->CallbackParameter->jumlah;
		$harga = $param->CallbackParameter->harga;
		$diskon = $param->CallbackParameter->diskon;
		$index = $param->CallbackParameter->index;
		$BarangRecord = BarangRecord::finder()->findByPk($idBarang);
		$jnsBarang = $BarangRecord->st_barang;
		
		$stokBarang = '1';
		$stokBahan = '1';
		if($jnsBarang == '1')
		{
			$sqlBanner = "SELECT
									tbm_barang_banner.id_barang AS idBahan,
									tbm_barang.nama AS nmBahan,
									tbm_barang_banner.jml AS jmlBahan
								FROM
									tbm_barang_banner
								INNER JOIN tbm_barang ON tbm_barang.id = tbm_barang_banner.id_barang
								WHERE
									tbm_barang_banner.deleted = '0'
									AND tbm_barang_banner.id_parent_barang = '$idBarang' ";
			$arrBahan = $this->queryAction($sqlBanner,'S');
			foreach($arrBahan as $rowBahan)
			{
				$StockBahan = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$rowBahan['idBahan'],'0');
				if($StockBahan)
					$StockBahan = $StockBahan->stok;
				else
					$StockBahan = 0;
					
				$jmlBahan = $jumlah * $rowBahan['jmlBahan'];
				
				if($jmlBahan > $StockBahan)
					$stokBahan = '0';
			}
			
		}
		else
		{
			$BarangHargaRecord = BarangHargaRecord::finder()->findByPk($idHarga);
			$idSatuan = $BarangHargaRecord->id_satuan;
			$jmlReal = $BarangHargaRecord->jml *  $jumlah;
			var_dump($stok);
			if($jmlReal <= $stok)
				$stokBarang = '1';
			else
				$stokBarang = '0';
		}
		
		
		if($stokBarang == '1' && $stokBahan == '1')
		{
			$subtotal = ($jumlah * $harga) - $diskon;
			$this->getPage()->getClientScript()->registerEndScript
						('','
						unloadContent();
						jQuery("#subtotalBrg'.$index.'").text("'.$this->formatCurrency($subtotal,2).'");
						');	
		}
		else
		{
			if($stokBahan == '0')
				$msg = "Stok Bahan Untuk banner Tersebut Tidak Cukup !";
			else
				$msg = "Stok Barang Tidak Cukup!";
				
			$this->getPage()->getClientScript()->registerEndScript
						('','
						unloadContent();
						jQuery("#jumlahBrg'.$index.'").val("");
						toastr.error("'.$msg.'");
						');	
		}
	}
	
	public function tambahBtnClicked($sender,$param)
	{
		$idBarang = $this->DDBarang->SelectedValue;
		$BarangRecord = BarangRecord::finder()->findByPk($idBarang);
		$jnsBarang = $BarangRecord->st_barang;
		$nmBarang = $BarangRecord->nama;
		//if($this->DDUkuran->Enabled == true)
		$ukuran = $this->DDUkuran->SelectedValue;
		//else
			//$ukuran = '';
		$stok = $this->stok->Text;	
		$jumlah = $this->jumlah->Text;
		$harga = $this->toInt($this->harga->Text);
		$diskon = $this->toInt($this->diskon->Text);
		$idHarga = $this->idHarga->Value;
		$subtotal = ($jumlah * $harga) - $diskon;
		$stokBahan = '1';
		if($jnsBarang == '1')
		{
			$stokBarang = '1';
			$sqlBanner = "SELECT
									tbm_barang_banner.id_barang AS idBahan,
									tbm_barang.nama AS nmBahan,
									tbm_barang_banner.jml AS jmlBahan
								FROM
									tbm_barang_banner
								INNER JOIN tbm_barang ON tbm_barang.id = tbm_barang_banner.id_barang
								WHERE
									tbm_barang_banner.deleted = '0'
									AND tbm_barang_banner.id_parent_barang = '$idBarang' ";
			$arrBahan = $this->queryAction($sqlBanner,'S');
			foreach($arrBahan as $rowBahan)
			{
				$StockBahan = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$rowBahan['idBahan'],'0');
				if($StockBahan)
					$StockBahan = $StockBahan->stok;
				else
					$StockBahan = 0;
					
				$jmlBahan = $jumlah * $rowBahan['jmlBahan'];
				
				if($jmlBahan > $StockBahan)
					$stokBahan = '0';
			}
			
		}
		else
		{
			if($jmlReal <= $stok)
				$stokBarang = '1';
			else
				$stokBarang = '0';
		}
		
		$exist = '0';
		$BarangHargaRecord = BarangHargaRecord::finder()->findByPk($idHarga);
		$idSatuan = $BarangHargaRecord->id_satuan;
		$jmlReal = $BarangHargaRecord->jml *  $jumlah;
		if($stokBarang == '1' && $stokBahan == '1')
		{
			if($idSatuan == '0')
			{
				$satuanBarang = BarangRecord::finder()->findByPk($idBarang)->satuan;
			}
			else
			{
				$satuanBarang = $idSatuan;
			}
			
			$nmSatuan = SatuanRecord::finder()->findByPk($satuanBarang)->nama;
			
			$arrPenjualan = json_decode($this->arrPenjualan->Value,true);
			if(count($arrPenjualan) == 0)
				$id = 1;
			else
			{
				foreach($arrPenjualan as $rowPenjualan)
				{
					if($rowPenjualan['idBarang'] == $idBarang && $rowPenjualan['ukuran'] == $ukuran)
						$exist = '1';
						
					$id = $rowPenjualan['id'] + 1;
				}
			}
			
			if($exist == '0')
			{
				$arrPenjualan[] = array("id"=>$id,
										"idBarang"=>$idBarang,
										"nmBarang"=>$nmBarang,
										"ukuran"=>$ukuran,
										"jumlah"=>$jumlah." ".$nmSatuan,
										"idHarga"=>$idHarga,
										"harga"=>$harga,
										"diskon"=>$diskon,
										"subtotal"=>$subtotal);
				$this->arrPenjualan->Value = json_encode($arrPenjualan,true);
				$this->DDBarang->SelectedValue = 'empty';
				$this->DDUkuran->SelectedValue = 'empty';
				$this->jumlah->Text = '';
				$this->harga->Text = '';
				$this->idHarga->Value = '';
				$this->stok->Text ='';
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
		else
		{
			if($stokBahan == '0')
				$msg = "Stok Bahan Untuk banner Tersebut Tidak Cukup !";
			else
				$msg = "Stok Barang Tidak Cukup!";
				
			$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.error("'.$msg.'");
						');	
		}
			
	}
	
	public function BindGrid()
	{
		$arrPenjualan = json_decode($this->arrPenjualan->Value ,true);
		
		$count = count($arrPenjualan);
		$tblBody = '';
		if($count > 0)
		{
			foreach($arrPenjualan as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nmBarang'].'</td>';
				$tblBody .= '<td>'.$row['jumlah'].'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['harga'],2).'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['diskon'],2).'</td>';
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
					jQuery("#'.$this->DDUkuran->getClientID().'").select2("val", "empty");
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
		$arrPenjualan = json_decode($this->arrPenjualan->Value,true);
		
		foreach($arrPenjualan as $subKey => $subArray)
		{
			if($subArray['id'] == $id)
				unset($arrPenjualan[$subKey]);	
		}
		
		$this->arrPenjualan->Value = json_encode($arrPenjualan,true);
		$this->BindGrid();
	}
	
	public function submitBtnClicked($sender,$param)
	{
		//$arrPenjualan = json_decode($this->arrPenjualan->Value,true);
		$arrPenjualan = $param->CallbackParameter->arr;
		if(count($arrPenjualan) > 0)
		{
			$JnsTrans = $this->jns_transaksi->SelectedValue;
			if($JnsTrans == '0')
			{
				$idPelanggan = $this->DDPelanggan->SelectedValue;
				$nmPelanggan = PelangganRecord::finder()->findByPk($idPelanggan)->nama;
			}
			else
			{
				$idPelanggan = '0';
				$nmPelanggan = $this->nmPelanggan->Text;
			} 
			
			$tglTrans = date('Y-m-d');
			$wktTrans = date('G:i:s');
			
			$PenjualanRecord = new PenjualanRecord();
			$PenjualanRecord->jns_transaksi = $JnsTrans;
			$PenjualanRecord->id_pelanggan = $idPelanggan;
			$PenjualanRecord->nama_pelanggan = $nmPelanggan;
			$PenjualanRecord->tgl_transaksi = $tglTrans;
			$PenjualanRecord->wkt_transaksi = $wktTrans;
			$PenjualanRecord->st_posting = '1';
			$PenjualanRecord->save();
			$jmlTrans = 0;
			foreach($arrPenjualan as $rowPenjualan)
			{
				$BarangRecord = BarangRecord::finder()->findByPk($rowPenjualan->idBarang);
				$jnsBarang = $BarangRecord->st_barang;
				$DetailPenjualan = new PenjualanDetailRecord();
				$DetailPenjualan->id_transaksi = $PenjualanRecord->id;
				$DetailPenjualan->id_barang = $rowPenjualan->idBarang;
				$DetailPenjualan->jml = $rowPenjualan->jumlah;
				$DetailPenjualan->id_harga = $rowPenjualan->idHarga;
				$DetailPenjualan->harga = $rowPenjualan->harga;
				$DetailPenjualan->diskon = $rowPenjualan->diskon;
				$DetailPenjualan->total = $rowPenjualan->subtotal;
				
				$DetailPenjualan->save();
				$jmlTrans += $rowPenjualan->subtotal;
				if($jnsBarang == '0')
				{
					$BarangHargaRecord = BarangHargaRecord::finder()->findByPk($rowPenjualan->idHarga);
					$jmlReal = $BarangHargaRecord->jml * $rowPenjualan->jumlah;
					
					$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$rowPenjualan->idBarang,'0');
					if($StockBarang)
					{
						$stokAwal = $StockBarang->stok;
						$stokAkhir = $stokAwal - $jmlReal;
						$StockBarang->stok = $stokAkhir;
						
						if($StockBarang->save())
						{
							$StockInOutRecord = new StockInOutRecord();
							$StockInOutRecord->id_barang = $rowPenjualan->idBarang;
							$StockInOutRecord->stok_awal = $stokAwal;
							$StockInOutRecord->stok_in = 0;
							$StockInOutRecord->stok_out =$jmlReal;
							$StockInOutRecord->stok_akhir =$stokAkhir;
							$StockInOutRecord->keterangan = "Transaksi Penjualan";
							$StockInOutRecord->id_transaksi = $DetailPenjualan->id;
							$StockInOutRecord->jns_transaksi = "0";
							$StockInOutRecord->tgl = $tglTrans;
							$StockInOutRecord->wkt = $wktTrans;
							$StockInOutRecord->save();
						}
					}
				}
				elseif($jnsBarang == '1')
				{
					$idParent = $rowPenjualan->idBarang;
					$sqlBanner = "SELECT
									tbm_barang_banner.id_barang,
									tbm_barang_banner.jml
								FROM
									tbm_barang_banner
								WHERE
									tbm_barang_banner.deleted = '0'
									AND tbm_barang_banner.id_parent_barang = '$idParent' ";
									
					$arrBahan = $this->queryAction($sqlBanner,'S');
					foreach($arrBahan as $rowBahan)
					{
						$jmlReal = $rowBahan['jml'] * $rowPenjualan->jumlah;
					
						$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$rowBahan['id_barang'],'0');
						if($StockBarang)
						{
							$stokAwal = $StockBarang->stok;
							$stokAkhir = $stokAwal - $jmlReal;
							$StockBarang->stok = $stokAkhir;
							
							if($StockBarang->save())
							{
								$StockInOutRecord = new StockInOutRecord();
								$StockInOutRecord->id_barang = $rowBahan['id_barang'];
								$StockInOutRecord->stok_awal = $stokAwal;
								$StockInOutRecord->stok_in = 0;
								$StockInOutRecord->stok_out =$jmlReal;
								$StockInOutRecord->stok_akhir =$stokAkhir;
								$StockInOutRecord->keterangan = "Transaksi Penjualan";
								$StockInOutRecord->id_transaksi = $DetailPenjualan->id;
								$StockInOutRecord->jns_transaksi = "0";
								$StockInOutRecord->tgl = $tglTrans;
								$StockInOutRecord->wkt = $wktTrans;
								$StockInOutRecord->save();
							}
						}
					}
				}
		
			}
			
			$this->InsertJurnalBukuBesar(
											$PenjualanRecord->id,
											'0',
											'0',
											$tglTrans,
											$wktTrans,
											'Penjualan Barang',
											$jmlTrans
											);
											
			$this->jns_transaksi->SelectedValue = '';
			$this->DDPelanggan->SelectedValue = 'empty';
			$this->nmPelanggan->text = '';
			$src = "index.php?page=Transaksi.CetakBillPenjualan&id=".$PenjualanRecord->id;
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#'.$this->jns_transaksi->getClientID().'").select2("val", "0");
					jQuery("#'.$this->DDPelanggan->getClientID().'").select2("val", "empty");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("");
					jQuery("#modal-3  .modal-body").empty();
					jQuery("<iframe id=\"BillPdf\" style=\"width:100%;height:100%;\" frameborder=\"0\" src=\"'.$src.'\">").appendTo("#modal-3 .modal-body");
					jQuery("#modal-3").modal("show");
					unloadContent();
					BindGrid();
					toastr.info("Transaksi Penjualan Telah Dimasukkan ");
					');	
			
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Barang Yang Akan Dijual Belum Dimasukkan!");
					');	
		}
		$pelanggan = $this->DDPelanggan->SelectedValue;
		
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
