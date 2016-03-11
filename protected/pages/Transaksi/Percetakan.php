<?PHP
class Percetakan extends MainConf
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
			
			$sqlKateg = "SELECT id,nama AS text FROM tbm_kategori_cetakan WHERE deleted ='0' ";
			$arrKateg = $this->queryAction($sqlKateg,'S');
			$this->arrKategori->Value = json_encode($arrKateg,true);
			
			$sqlBarang = "SELECT id,nama_cetakan,kategori_cetakan FROM tbm_cetakan WHERE deleted ='0' ";
			$arrBarang = $this->queryAction($sqlBarang,'S');
			$this->arrBarang->Value = json_encode($arrBarang,true);
		}
	}
	
	public function barangChanged($sender,$param)
	{
		$idCetakan = $param->CallbackParameter->id;
		$index = $param->CallbackParameter->index;
		$MasterCetakanRecord = MasterCetakanRecord::finder()->findByPk($idCetakan);
		if($MasterCetakanRecord)
		{
			$hargaCetakan = $MasterCetakanRecord->total_harga_jual;
			$stText='jQuery("#hrgCetakan'.$index.'").prop("disabled",false);';
		}
		else
		{
			$hargaCetakan = 0;
			$stText='jQuery("#hrgCetakan'.$index.'").prop("disabled",false);';
		}
		var_dump($hargaCetakan);
		$this->getPage()->getClientScript()->registerEndScript
			('','
				jQuery("#hrgCetakan'.$index.'").val("'.$this->formatCurrency($hargaCetakan,2).'");
				'.$stText.'
			');
			
	}
	
	public function pelangganChanged($sender,$param)
	{
		$idPelanggan = $this->DDPelanggan->SelectedValue;
		if($idPelanggan != '')
		{
			$query = "SELECT
							tbt_cetakan_detail.id,
							CONCAT( tbt_cetakan_detail.nm_cetakan,  ' - ', tbt_cetakan_detail.hrg_cetakan,  ' (', tbt_cetakan.tgl_transaksi,  ')' ) AS nama 
						FROM
							tbt_cetakan_detail
						INNER JOIN tbt_cetakan ON tbt_cetakan.id = tbt_cetakan_detail.id_transaksi
						WHERE
							tbt_cetakan.id_pelanggan = '$idPelanggan'
						ORDER BY tbt_cetakan.tgl_transaksi DESC";
						var_dump($query);
			$arr = $this->queryAction($query,'S');
			if($arr)
			{
				$this->hirtoryTransaksi->DataSource = $arr;
				$this->hirtoryTransaksi->DataBind();
				
				$this->getPage()->getClientScript()->registerEndScript
				('','
					jQuery("#historyPanel").show();
				');
			}
			else
			{
				$this->getPage()->getClientScript()->registerEndScript
				('','
					jQuery("#historyPanel").hide();
				');
			}
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
	
	public function saveRows($sender,$param)
	{
		$this->getPage()->getClientScript()->registerEndScript
				('','
					saveRows();
				');	
	}
	
	public function submitBtnClicked($sender,$param)
	{
		$arrPercetakan = $param->CallbackParameter->arr;
		$diskonCetakan = $param->CallbackParameter->diskonCetakan;
		$subtotalCetakan = $param->CallbackParameter->subtotalCetakan;
		$totalCetakan = $param->CallbackParameter->totalCetakan;
		$dpayment = $param->CallbackParameter->dpayment;
		var_dump($arrPercetakan);
		if(count($arrPercetakan) > 0)
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
			
			$CetakanRecord = new CetakanRecord;
			$CetakanRecord->jns_transaksi = $JnsTrans;
			$CetakanRecord->id_pelanggan = $idPelanggan;
			$CetakanRecord->nama_pelanggan = $nmPelanggan;
			$CetakanRecord->tgl_transaksi = $tglTrans;
			$CetakanRecord->wkt_transaksi = $wktTrans;
			$CetakanRecord->subtotal = $subtotalCetakan;
			$CetakanRecord->diskon = $diskonCetakan;
			$CetakanRecord->total = $totalCetakan;
			if($dpayment > 0)
			{
				if($dpayment >= $totalCetakan)
					$stLunas = '1';
				else
					$stLunas = '0';
			}
			
			$CetakanRecord->st_lunas = $stLunas;
			$CetakanRecord->save();
			
			if($dpayment > 0)
			{
				if($dpayment >= $totalCetakan)
				{
					$kembalian = $dpayment - $totalCetakan;
					$jmlTrans = $totalCetakan;
					$sisaBayar = 0;
				}
				else
				{
					$kembalian = 0;
					$jmlTrans = $dpayment;
					$sisaBayar = $totalCetakan - $dpayment;
				}
					
				$CetakanBayarRecord = new CetakanBayarRecord();
				$CetakanBayarRecord->id_transaksi = $CetakanRecord->id;
				$CetakanBayarRecord->tgl_bayar = $tglTrans;
				$CetakanBayarRecord->wkt_bayar = $wktTrans;
				$CetakanBayarRecord->jml_bayar = $dpayment;
				$CetakanBayarRecord->kembalian = $kembalian;
				$CetakanBayarRecord->sisa_bayar = $sisaBayar;
				$CetakanBayarRecord->st_dp = '0';
				$CetakanBayarRecord->st_posting = '1';
				$CetakanBayarRecord->save();
				
				$this->InsertJurnalBukuBesar(
											$CetakanBayarRecord->id,
											'1',
											'0',
											$tglTrans,
											$wktTrans,
											'Pembayaran DP Cetakan',
											$jmlTrans
											);
			}
			
				foreach($arrPercetakan as $row)
				{
					$MasterCetakanRecord = MasterCetakanRecord::finder()->findByPk($row->idCetakan);
					if($MasterCetakanRecord)
					{
						$idCetakan = $row->idCetakan;
						$nmCetakan = $MasterCetakanRecord->nama_cetakan;
					}
					else
					{
						$idCetakan = '0';
						$nmCetakan = $row->idCetakan;
					}
					
					$CetakanDetailRecord = new CetakanDetailRecord;
					$CetakanDetailRecord->id_transaksi = $CetakanRecord->id;
					$CetakanDetailRecord->id_cetakan = $idCetakan;
					$CetakanDetailRecord->nm_cetakan = $nmCetakan;
					$CetakanDetailRecord->hrg_cetakan = $row->hrgCetakan;
					$CetakanDetailRecord->jumlah_pesanan = $row->jmlPesanan;
					$CetakanDetailRecord->est_hari = $row->estHari;
					$CetakanDetailRecord->tt_hari = $row->ttHari;
					$CetakanDetailRecord->lembur = $row->lembur;
					$CetakanDetailRecord->subtotal = $row->subtotal;
					$CetakanDetailRecord->save();
				}
			
			$this->jns_transaksi->SelectedValue = '0';
			$this->DDPelanggan->SelectedValue = 'empty';
			$this->nmPelanggan->text = '';
			$this->nmPelanggan->visible = false;
			$src = "index.php?page=Transaksi.CetakBillCetakan&id=".$CetakanRecord->id;
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#'.$this->jns_transaksi->getClientID().'").select2("val", "0");
					jQuery("#'.$this->DDPelanggan->getClientID().'").select2("val", "empty");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("");
					jQuery("#diskonCetakan").val("0.00");
					jQuery("#subtotalCetakan").text("0.00");
					jQuery("#totalCetakan").text("0.00");
					jQuery("#modal-3  .modal-body").empty();
					jQuery("<iframe id=\"BillPdf\" style=\"width:100%;height:100%;\" frameborder=\"0\" src=\"'.$src.'\">").appendTo("#modal-3 .modal-body");
					jQuery("#modal-3").modal("show");
					jQuery("#PanelDDPelanggan").show();
					jQuery("#historyPanel").hide();
					unloadContent();
					BindGrid();
					toastr.info("Transaksi Percetakan Telah Dimasukkan ");
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
