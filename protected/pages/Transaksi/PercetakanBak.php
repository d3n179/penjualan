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
			
			$sqlBarang = "SELECT id,CONCAT(nama,' (',ukuran,')') AS text FROM tbm_barang WHERE deleted ='0' AND st_barang = '0' ";
			$arrBarang = $this->queryAction($sqlBarang,'S');
			//$arrTemp = [];
			foreach($arrBarang as $rowBarang)
			{
				$idBrg = $rowBarang['id'];
				$BarangHargaRecord = BarangHargaRecord::finder()->find('id_barang = ? AND id_satuan = ? AND deleted = ?',$idBrg,'0','0');
				if($BarangHargaRecord)
					$hargaEceran = number_format($BarangHargaRecord->harga,0,",",".");
				else
					$hargaEceran = 0;
				
				$arrTemp[] = array('id'=>$idBrg,'text'=>$rowBarang['text'].' @ '.$hargaEceran);
			}
			
			//$this->DDBarang->DataSource = $arrBarang;
			//$this->DDBarang->DataBind();
			$this->arrBarang->Value = json_encode($arrTemp,true);
		}
	}
	
	public function barangChanged($sender,$param)
	{
		$idBarang = $param->CallbackParameter->id;
		$index = $param->CallbackParameter->index;
		$BarangRecord = BarangRecord::finder()->findByPk($idBarang);
		$jnsBarang = $BarangRecord->st_barang;
		$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$idBarang,'0');
		
		if(!$StockBarang)
			$stok = 0;
		else
			$stok = $StockBarang->stok;
			
			
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
				$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#stokBrg'.$index.'").text('.$stok.');
						');
			//}
		}
		else
		{
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
			$this->getPage()->getClientScript()->registerEndScript
						('','
						tambahClicked();
						');	
	}
	
	public function prosesTambah($sender,$param)
	{
		$nmCetakan = $param->CallbackParameter->nmCetakan;
		$arrBahan = $param->CallbackParameter->arrBahan;
		$arrParam = $param->CallbackParameter->arrParam;
		$hargaTinta = $param->CallbackParameter->hargaTinta;
		$hargaLaminasi = $param->CallbackParameter->hargaLaminasi;
		$hargaSpiral = $param->CallbackParameter->hargaSpiral;
		$hargaHotprint = $param->CallbackParameter->hargaHotprint;
		$totalModal = $param->CallbackParameter->totalModal;
		$totalPersen = $param->CallbackParameter->totalPersen;
		$jmlPesanan = $param->CallbackParameter->jmlPesanan;
		$estHari = $param->CallbackParameter->estHari;
		$ttHari = $param->CallbackParameter->ttHari;
		$lembur = $param->CallbackParameter->lembur;
		$TotalHargaJual = $param->CallbackParameter->TotalHargaJual;
		
		$jnsTinta = $this->JnsTinta->SelectedValue;
		$JnsLaminasi = $this->JnsLaminasi->SelectedValue;
		$spiralUkuran = $this->spiralUkuran->Text;
		$jnsHotprint = $this->jnsHotprint->Text;
		
		
		$arrCetakan = json_decode($this->arrCetakan->Value,true);
		
		if($this->index->Value == '')
		{
			if(count($arrCetakan) > 0)
			{
				foreach($arrCetakan as $row)
				{
					$index = $row['id'] + 1;
				}
			}
			else
			{
				$index = 1;
			}
			
			$arrCetakan[] = array('id'=>$index,
									'nmCetakan'=>$nmCetakan,
									'arrBahan'=>$arrBahan,
									'arrParam'=>$arrParam,
									'JnsTinta'=>$jnsTinta,
									'JnsLaminasi'=>$JnsLaminasi,
									'spiralUkuran'=>$spiralUkuran,
									'jnsHotprint'=>$jnsHotprint,
									'hargaTinta'=>$hargaTinta,
									'hargaLaminasi'=>$hargaLaminasi,
									'hargaSpiral'=>$hargaSpiral,
									'hargaHotprint'=>$hargaHotprint,
									'totalModal'=>$totalModal,
									'totalPersen'=>$totalPersen,
									'jmlPesanan'=>$jmlPesanan,
									'estHari'=>$estHari,
									'ttHari'=>$ttHari,
									'lembur'=>$lembur,
									'TotalHargaJual'=>$TotalHargaJual
									);
		}
		else
		{
			$index = $this->index->Value;
			foreach($arrCetakan as $subKey => $subArray)
			{
				if($subArray['id'] == $index)
				{
					$arrCetakan[$subKey]['nmCetakan'] = $nmCetakan;
					$arrCetakan[$subKey]['arrBahan'] = $arrBahan;
					$arrCetakan[$subKey]['arrParam'] = $arrParam;
					$arrCetakan[$subKey]['JnsTinta'] = $jnsTinta;
					$arrCetakan[$subKey]['JnsLaminasi']	= $JnsLaminasi;
					$arrCetakan[$subKey]['spiralUkuran'] = $spiralUkuran;
					$arrCetakan[$subKey]['jnsHotprint']	= $jnsHotprint;
					$arrCetakan[$subKey]['hargaTinta'] = $hargaTinta;
					$arrCetakan[$subKey]['hargaLaminasi'] = $hargaLaminasi;
					$arrCetakan[$subKey]['hargaSpiral']	= $hargaSpiral;
					$arrCetakan[$subKey]['hargaHotprint'] = $hargaHotprint;
					$arrCetakan[$subKey]['totalModal'] = $totalModal;
					$arrCetakan[$subKey]['totalPersen']	= $totalPersen;
					$arrCetakan[$subKey]['jmlPesanan'] = $jmlPesanan;
					$arrCetakan[$subKey]['estHari']	= $estHari;
					$arrCetakan[$subKey]['ttHari'] = $ttHari;
					$arrCetakan[$subKey]['lembur'] = $lembur;
					$arrCetakan[$subKey]['TotalHargaJual'] = $TotalHargaJual;
					
					break;
				}
			}
		}
								
		$this->arrCetakan->Value = json_encode($arrCetakan,true);
		$this->index->Value = '';
		$this->nmCetakan->Text = '';
		$this->JnsTinta->SelectedValue = '0';
		$this->hrgTinta->Text = '';
		$this->JnsLaminasi->SelectedValue = '0';
		$this->hrgLaminasi->Text = '';
		$this->spiralUkuran->Text = '';
		$this->hrgSpiral->Text = '';
		$this->jnsHotprint->Text = '';
		$this->hrgHotprint->Text = '';
		$this->totalModal->Text = '0.00';
		$this->persen->Text = '0.00';
		$this->jmlPesanan->Text = '';
		$this->estHari->Text = '';
		$this->tuntutanHari->Text = '';
		$this->lembur->Text = '';
		$this->totalHrgJual->Text = '0.00';
		
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					jQuery("#table-bahan").dataTable().fnDestroy();
					jQuery("#table-bahan tbody").empty();
					jQuery("#table-bahan tbody").append("");
					jQuery("#table-parameter").dataTable().fnDestroy();
					jQuery("#table-parameter tbody").empty();
					jQuery("#table-parameter tbody").append("");
					BindGrid();
					BindGridBahan();
					BindGridParameter();
					jQuery("#modal-1").modal("hide");
					unloadContent();
					');
	}
	
	public function BindGrid()
	{
		$arrCetakan = json_decode($this->arrCetakan->Value,true);
		$count = count($arrCetakan);
		$tblBody = '';
		if($count > 0)
		{
			foreach($arrCetakan as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nmCetakan'].'</td>';
				$tblBody .= '<td>'.$row['jmlPesanan'].'</td>';
				$tblBody .= '<td>'.$row['estHari'].'</td>';
				$tblBody .= '<td>'.$row['ttHari'].'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['TotalHargaJual'],2).'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-pencil\" ></i>Edit</a>&nbsp;&nbsp;';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
				$tblBody .=	'</td>';			
				$tblBody .= '</tr>';
			}
		}
		else
		{
			$tblBody = '';
		}
		
		return $tblBody;
	}
	
	public function editData($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arrCetakan = json_decode($this->arrCetakan->Value,true);
		
		foreach($arrCetakan as $row)
		{
			if($row['id'] == $id)
			{
				$index = $row['id'];
				$nmCetakan = $row['nmCetakan'];
				$arrBahan = $row['arrBahan'];
				$arrParam = $row['arrParam'];
				$jnsTinta = $row['JnsTinta'];
				$JnsLaminasi = $row['JnsLaminasi'];
				$spiralUkuran = $row['spiralUkuran'];
				$jnsHotprint = $row['jnsHotprint'];
				$hargaTinta = number_format($row['hargaTinta'],2,".",",");
				$hargaLaminasi = number_format($row['hargaLaminasi'],2,".",",");
				$hargaSpiral = number_format($row['hargaSpiral'],2,".",",");
				$hargaHotprint = number_format($row['hargaHotprint'],2,".",",");
				$totalModal = number_format($row['totalModal'],2,".",",");
				$totalPersen = number_format($row['totalPersen'],2,".",",");
				$jmlPesanan = $row['jmlPesanan'];
				$estHari = $row['estHari'];
				$ttHari = $row['ttHari'];
				$lembur = $row['lembur'];
				$TotalHargaJual = number_format($row['TotalHargaJual'],2,".",",");
								
				break;
			}	
		}
		
		$this->index->Value = $index;
		$this->nmCetakan->Text = $nmCetakan;
		$this->JnsTinta->SelectedValue = $jnsTinta;
		$this->hrgTinta->Text = $hargaTinta;
		$this->JnsLaminasi->SelectedValue = $JnsLaminasi;
		$this->hrgLaminasi->Text = $hargaLaminasi;
		$this->spiralUkuran->Text = $spiralUkuran;
		$this->hrgSpiral->Text = $hargaSpiral;
		$this->jnsHotprint->Text = $jnsHotprint;
		$this->hrgHotprint->Text = $hargaHotprint ;
		$this->totalModal->Text = $totalModal;
		$this->persen->Text = $totalPersen;
		$this->jmlPesanan->Text = $jmlPesanan;
		$this->estHari->Text = $estHari;
		$this->tuntutanHari->Text = $ttHari;
		$this->lembur->Text = $lembur;
		$this->totalHrgJual->Text = $TotalHargaJual;
		
		$tblBodyBahan = "";
		if(count($arrBahan) > 0)
		{
			$i = 1;
			foreach($arrBahan as $rowBahan)
			{
				$tblBodyBahan .= '<tr>';
				$tblBodyBahan .= '<td><input id=\"nmBrg'.$i.'\" class=\"nmBrg\" type=\"text\" value=\"'.$rowBahan['idBahan'].'\" ></td>';
				$tblBodyBahan .= '<td><label id=\"stokBrg'.$i.'\" text=\"'.$rowBahan['stokBahan'].'\"></label></td>';
				$tblBodyBahan .= '<td><input id=\"jumlahBrg'.$i.'\" value=\"'.number_format($rowBahan['jmlBahan'],2,",",".").'\" width=\"50px\" type=\"text\" class=\"form-control autoJml\" data-a-sep=\".\" data-a-dec=\",\"  onChange=\"cekStok('.$i.')\" /></td>';
				$tblBodyBahan .= '<td><input id=\"ukuranBrg'.$i.'\" value=\"'.$rowBahan['ukuranBahan'].'\" class=\"form-control\" type=\"text\" ></td>';
				$tblBodyBahan .= '<td><input id=\"hargaBrg'.$i.'\" value=\"'.$rowBahan['hargaBahan'].'\" width=\"50px\" class=\"form-control autoJml\" type=\"text\" onChange=\"calculateAll();\"></td>';
				$tblBodyBahan .= '<td>';
				$tblBodyBahan .= '<button type=\"button\" id=\"row'.$i.'\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteRowbahan('.$i.')\"><i class=\"entypo-cancel\"></i>Hapus</button>';
				$tblBodyBahan .= '</td>';
				$tblBodyBahan .= '</tr>';
				$i++;
			}
		}
		
		$tblBodyParam = "";
		if(count($arrParam) > 0)
		{
			$i = 1;
			foreach($arrParam as $rowParam)
			{
				$tblBodyParam .= '<tr>';
				$tblBodyParam .= '<td><input id=\"nmParam'.$i.'\" value=\"'.$rowParam['nmParam'].'\" class=\"form-control\" type=\"text\" ></td>';
				$tblBodyParam .= '<td><input id=\"hargaParam'.$i.'\" value=\"'.$rowParam['hargaParam'].'\" width=\"50px\" class=\"form-control autoJmlParam\" type=\"text\" onChange=\"calculateAll();\"></td>';
				$tblBodyParam .= '<td>';
				$tblBodyParam .= '<button type=\"button\" id=\"rowParam'.$i.'\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteRowparam('.$i.');\"><i class=\"entypo-cancel\"></i>Hapus</button>';
				$tblBodyParam .= '</td>';
				$tblBodyParam .= '</tr>';
				$i++;
			}
		}
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#table-bahan").dataTable().fnDestroy();
					jQuery("#table-bahan tbody").empty();
					jQuery("#table-bahan tbody").append("'.$tblBodyBahan.'");
					jQuery("#table-parameter").dataTable().fnDestroy();
					jQuery("#table-parameter tbody").empty();
					jQuery("#table-parameter tbody").append("'.$tblBodyParam.'");
					BindGridBahan();
					BindGridParameter();
					jQuery("#modal-1").modal("show");
					unloadContent();
					');
	}
	
	public function deleteData($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arrCetakan = json_decode($this->arrCetakan->Value,true);
		
		foreach($arrCetakan as $subKey => $subArray)
		{
			if($subArray['id'] == $id)
				unset($arrCetakan[$subKey]);	
		}
		
		$this->arrCetakan->Value = json_encode($arrCetakan,true);
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');
	}
	
	public function submitBtnClicked($sender,$param)
	{
		$arrCetakan = json_decode($this->arrCetakan->Value,true);
		
		if(count($arrCetakan) > 0)
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
			
			$CetakanRecord = new CetakanRecord();
			$CetakanRecord->jns_transaksi = $JnsTrans;
			$CetakanRecord->id_pelanggan = $idPelanggan;
			$CetakanRecord->nama_pelanggan = $nmPelanggan;
			$CetakanRecord->tgl_transaksi = $tglTrans;
			$CetakanRecord->wkt_transaksi = $wktTrans;
			$CetakanRecord->save();
			
			foreach($arrCetakan as $rowCetakan)
			{
				$arrBahan = $rowCetakan['arrBahan'];
				$arrParam = $rowCetakan['arrParam'];
				
				$CetakanDetailRecord = new CetakanDetailRecord();
				$CetakanDetailRecord->id_transaksi = $CetakanRecord->id;
				$CetakanDetailRecord->nama_cetakan = $rowCetakan['nmCetakan'];
				$CetakanDetailRecord->jns_tinta = $rowCetakan['JnsTinta'];
				$CetakanDetailRecord->hrg_tinta = $rowCetakan['hargaTinta'];
				$CetakanDetailRecord->jns_laminasi = $rowCetakan['JnsLaminasi'];
				$CetakanDetailRecord->hrg_laminasi = $rowCetakan['hargaLaminasi'];
				$CetakanDetailRecord->jns_spiral = $rowCetakan['spiralUkuran'];
				$CetakanDetailRecord->hrg_spiral = $rowCetakan['hargaSpiral'];
				$CetakanDetailRecord->jns_hotprint = $rowCetakan['jnsHotprint'];
				$CetakanDetailRecord->hrg_hotprint = $rowCetakan['hargaHotprint'];
				$CetakanDetailRecord->total_modal = $rowCetakan['totalModal'];
				$CetakanDetailRecord->persen = $rowCetakan['totalPersen'];
				$CetakanDetailRecord->jumlah_pesanan = $rowCetakan['jmlPesanan'];
				$CetakanDetailRecord->est_hari = $rowCetakan['estHari'];
				$CetakanDetailRecord->tt_hari = $rowCetakan['ttHari'];
				$CetakanDetailRecord->lembur = $rowCetakan['lembur'];
				$CetakanDetailRecord->total_harga_jual = $rowCetakan['TotalHargaJual'];
				$CetakanDetailRecord->save();
				 
				foreach($arrBahan as $rowBahan)
				{
					$CetakanDetailBahanRecord = new CetakanDetailBahanRecord();
					$CetakanDetailBahanRecord->id_cetakan_detail = $CetakanDetailRecord->id;
					$CetakanDetailBahanRecord->id_barang = $rowBahan['idBahan'];
					$CetakanDetailBahanRecord->ukuran = $rowBahan['ukuranBahan'];
					$CetakanDetailBahanRecord->jml = $rowBahan['jmlBahan'];
					$CetakanDetailBahanRecord->harga = $rowBahan['hargaBahan'];
					$CetakanDetailBahanRecord->save();
					
					$BarangRecord = BarangRecord::finder()->findByPk($rowBahan['idBahan']);
					$jmlReal = $rowBahan['jmlBahan'];
					
					$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$rowBahan['idBahan'],'0');
					if($StockBarang)
					{
						$stokAwal = $StockBarang->stok;
						$stokAkhir = $stokAwal - $jmlReal;
						$StockBarang->stok = $stokAkhir;
						
						if($StockBarang->save())
						{
							$StockInOutRecord = new StockInOutRecord();
							$StockInOutRecord->id_barang = $rowBahan['idBahan'];
							$StockInOutRecord->stok_awal = $stokAwal;
							$StockInOutRecord->stok_in = 0;
							$StockInOutRecord->stok_out =$jmlReal;
							$StockInOutRecord->stok_akhir =$stokAkhir;
							$StockInOutRecord->keterangan = "Transaksi Percetakan";
							$StockInOutRecord->id_transaksi = $CetakanDetailBahanRecord->id;
							$StockInOutRecord->jns_transaksi = "3";
							$StockInOutRecord->tgl = $tglTrans;
							$StockInOutRecord->wkt = $wktTrans;
							$StockInOutRecord->save();
						}
					}
				}
				
				foreach($arrParam as $rowParam)
				{
					$CetakanDetailParamRecord = new CetakanDetailParamRecord();
					$CetakanDetailParamRecord->id_cetakan_detail = $CetakanDetailRecord->id;
					$CetakanDetailParamRecord->parameter = $rowParam['nmParam'];
					$CetakanDetailParamRecord->harga = $rowParam['hargaParam'];
					$CetakanDetailParamRecord->save();
				}
				
		
			}
			$this->jns_transaksi->SelectedValue = '';
			$this->DDPelanggan->SelectedValue = 'empty';
			$this->nmPelanggan->text = '';
			$src = "index.php?page=Transaksi.CetakBillCetakan&id=".$CetakanRecord->id;
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
					toastr.info("Transaksi Percetakan Telah Dimasukkan ");
					');	
			
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Cetakan Yang Akan Dijual Belum Dimasukkan!");
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
