<?PHP
class LaporanPenjualan extends MainConf
{
	public function onLoadComplete($param)
	{
		parent::onLoadComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			//$this->cariBtnClicked($sender,$param);
			$tahun = date("Y");
			$arrThn[] = array("id"=>$tahun,'nama'=>$tahun);
			 
			$a = 20;
			$i = 1;
			while($i < $a)
			{
				$arrThn[] = array("id"=>$tahun-$i,'nama'=>$tahun-$i); 
				$i++;
			}
			$this->DDTahun->DataSource = $arrThn;
			$this->DDTahun->DataBind();
		}
		
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
		}
	}
	
	public function periodeChanged()
	{
		$periode = $this->Periode->SelectedValue;
		if($periode == '0')
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#panelMingguan").hide();
					jQuery("#panelTahunan").show();
					jQuery("#panelBulanan").show();
					');
		}
		elseif($periode == '1')
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#panelMingguan").hide();
					jQuery("#panelTahunan").show();
					jQuery("#panelBulanan").hide();
					');
		}
		elseif($periode == '2')
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#panelMingguan").show();
					jQuery("#panelTahunan").hide();
					jQuery("#panelBulanan").hide();
					');
		}
	}
	
	public function BindGrid()
	{
		$periode = $this->Periode->SelectedValue;
		
		$sqlTrans = "SELECT 
						'0' AS st_trans,
						tbt_penjualan.id,
						tbt_penjualan.jns_transaksi,
						tbt_penjualan.nama_pelanggan,
						tbt_penjualan.tgl_transaksi,
						tbt_penjualan.wkt_transaksi,
						tbt_penjualan.st_posting
					FROM 
						tbt_penjualan
					WHERE
						tbt_penjualan.deleted ='0' ";
						 			
		if($periode == '0')
		{
			$bulan = $this->DDBulan->SelectedValue;
			$tahun = $this->DDTahun->SelectedValue;
			if($bulan != '' && $tahun != '')
			{
				$sqlTrans .="AND MONTH(tbt_penjualan.tgl_transaksi) = '$bulan' AND YEAR(tbt_penjualan.tgl_transaksi) = '$tahun' ";
			}
		}
		elseif($periode == '1')
		{
			$tahun = $this->DDTahun->SelectedValue;
			if($tahun != '')
			{
				$sqlTrans .="AND YEAR(tbt_penjualan.tgl_transaksi) = '$tahun' ";
			}
		}				
		elseif($periode == '2')
		{
			$mingguan = $this->mingguan->Text;
			if($mingguan != '')
			{
				$mingguan = explode("-",$mingguan);
				$tgl1 = trim(str_replace("/","-",$mingguan[0]));
				$tgl2 = trim(str_replace("/","-",$mingguan[1]));
				$tgl1 = explode("-",$tgl1);
				$tgl2 = explode("-",$tgl2);
				
				$tgl1 = $tgl1[1]."-".$tgl1[0]."-".$tgl1[2];
				$tgl2 = $tgl2[1]."-".$tgl2[0]."-".$tgl2[2];
				
				$tgl1 = $this->ConvertDate($tgl1,'2');
				$tgl2 = $this->ConvertDate($tgl2,'2');
				$sqlTrans .="AND tbt_penjualan.tgl_transaksi BETWEEN '$tgl1' AND '$tgl2' ";
			}
		}		
		
		$sqlFull = $sqlTrans;
		$arrTrans = $this->queryAction($sqlFull,'S');
		var_dump($sqlFull);
		$tblBody = '';
		if($arrTrans)
		{
			foreach($arrTrans as $row)
			{
				$idTrans =  $row['id'];
				$jnsTrans = "Transaksi Penjualan";
					$sqlTot = "SELECT 
									COUNT(tbt_penjualan_detail.id) AS total_item,
									SUM(tbt_penjualan_detail.total) AS total
								FROM 
									tbt_penjualan_detail
								WHERE 
									tbt_penjualan_detail.id_transaksi = '$idTrans' 
									AND tbt_penjualan_detail.deleted = '0' 
								GROUP BY 
									tbt_penjualan_detail.id_transaksi ";
					$arrTot = $this->queryAction($sqlTot,'S');
				
				if($row['jns_transaksi'] == '0')
					$jnsPelanggan = "Member / Regular";
				else
					$jnsPelanggan = "OTC";
					
				//var_dump($arrTot[0]['total_item']);
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['tgl_transaksi'].'</td>';
				$tblBody .= '<td>'.$row['wkt_transaksi'].'</td>';
				$tblBody .= '<td>'.$jnsPelanggan.'</td>';
				$tblBody .= '<td>'.$row['nama_pelanggan'].'</td>';
				$tblBody .= '<td>'.$arrTot[0]['total_item'].'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($arrTot[0]['total'],2).'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"detailClicked('.$row['id'].')\"><i class=\"entypo-doc-text-inv\"></i>Detail</a>&nbsp;&nbsp;';	
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"cetakClicked('.$row['id'].')\"><i class=\"entypo-print\"></i>Cetak</a>&nbsp;&nbsp;';	
				
				if($row['st_posting'] =='0')	
					$tblBody .= '<a href=\"#\" class=\"btn btn-primary btn-sm btn-icon icon-left\" OnClick=\"postingClicked('.$row['id'].')\"><i class=\"entypo-doc-text-inv\"></i>Posting</a>&nbsp;&nbsp;';
					
				if($this->User->IsUserGroup == '1')
				{		
					$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"hapusClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
				}
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
	public function cariBtnClicked($sender,$param)
	{
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
	
	public function postingClicked($sender,$param)
	{
		$idTrans = $param->CallbackParameter->id;
		$sqlTot = "SELECT 
									SUM(tbt_penjualan_detail.total) AS total
								FROM 
									tbt_penjualan_detail
								WHERE 
									tbt_penjualan_detail.id_transaksi = '$idTrans' 
									AND tbt_penjualan_detail.deleted = '0' 
								GROUP BY 
									tbt_penjualan_detail.id_transaksi ";
			$arrTot = $this->queryAction($sqlTot,'S');
			$PenjualanRecord = PenjualanRecord::finder()->findByPk($idTrans);
			$PenjualanRecord->st_posting = '1';
			$PenjualanRecord->save();
			
			$this->InsertJurnalBukuBesar(
											$PenjualanRecord->id,
											'0',
											'0',
											$PenjualanRecord->tgl_transaksi,
											$PenjualanRecord->wkt_transaksi,
											'Penjualan Barang',
											$arrTot[0]['total']
											);
											
		
		
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Transaksi Telah Diposting");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');
					
	}
	
	public function BindGridDetail($idDetail)
	{
		$idTrans = $idDetail;
			$sqlDetail = "SELECT
							tbt_penjualan_detail.id,
							tbt_penjualan_detail.id_barang,
							tbt_penjualan_detail.id_harga,
							tbm_barang.nama AS barang,
							tbt_penjualan_detail.jml,
							tbt_penjualan_detail.harga,
							tbt_penjualan_detail.diskon,
							tbt_penjualan_detail.total
						FROM
							tbt_penjualan_detail
						INNER JOIN tbm_barang ON tbm_barang.id = tbt_penjualan_detail.id_barang
						WHERE
							tbt_penjualan_detail.deleted ='0'
							AND tbt_penjualan_detail.id_transaksi = '$idTrans' ";
			$arrDetail = $this->queryAction($sqlDetail,'S');
			
			$index = 1;
			$tblBody = '';
			if($arrDetail > 0)
			{
				
				foreach($arrDetail as $row)
				{
					$tblBody .= '<tr>';
					$tblBody .= '<td>'.$row['barang'].'</td>';
					$tblBody .= '<td><input id=\"jmlBrgDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control autoJml\" data-a-sep=\".\" data-a-dec=\",\" type=\"text\" value=\"'.$row['jml'].'\" ></td>';
					$tblBody .= '<td><label id=\"labelHarga'.$row['id'].'\" class=\"autoJml\">'.$row['harga'].'</label></td>';
					$tblBody .= '<td><input id=\"diskonBrgDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control autoJml\" type=\"text\" value=\"'.$row['diskon'].'\" ></td>';
					$tblBody .= '<td><label id=\"labelTotal'.$row['id'].'\" class=\"autoJml\">'.$row['total'].'</label></td>';
					$tblBody .= '<td>';	
					if($this->User->IsUserGroup == '1')
					{
						$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-pencil\"></i>Simpan Perubahan</a>&nbsp;&nbsp;';	
						$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"hapusDetailClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
					}
					$tblBody .= '</td>';			
					$tblBody .= '</tr>';
					$index++;
				}
			}
			else
			{
				$tblBody = '';
			}
			
			return $tblBody;
			
	}
	
	public function detailClicked($sender,$param)
	{
		$idDetail = $param->CallbackParameter->id;
			$tblBody = $this->BindGridDetail($idDetail);
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#tableDetail").dataTable().fnDestroy();
						jQuery("#tableDetail tbody").empty();
						jQuery("#tableDetail tbody").append("'.$tblBody.'");
						BindGridDetail();
						jQuery("#modal-2").modal("show");
						unloadContent();
						');
		
		
	}
	
	public function simpanPerubahanClicked($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$jml = $param->CallbackParameter->jml;
		$harga = $param->CallbackParameter->harga;
		$diskon = $param->CallbackParameter->diskon;
		$subtotal = $param->CallbackParameter->subtotal;
		
		$DetailRecord = PenjualanDetailRecord::finder()->findByPk($id);
		$idBarang = $DetailRecord->id_barang;
		$prevJml = $DetailRecord->jml;
		$prevDiskon = $DetailRecord->diskon;
		$prevTotal = $DetailRecord->total;
		$StockBarangRecord = StockBarangRecord::finder()->find('id_barang = ?',$idBarang);
		$StockBarang = $StockBarangRecord->stok;
		$sqlStockOut = "SELECT
							MAX(tbt_stok_in_out.id) AS id
						FROM
							tbt_stok_in_out 
						WHERE
							id_barang = '$idBarang'
							AND id_transaksi = '$id' 
							AND jns_transaksi = '0' 
							AND stok_out > 0 ";
		$arrQuery = $this->queryAction($sqlStockOut,'S');
		$idMaxStock = $arrQuery[0]['id'];
		
		$StockOutRecord = StockInOutRecord::finder()->findByPk($idMaxStock);
		$prevStockOut = $StockOutRecord->stok_out;
		$newStockBarang = $StockBarang + $prevStockOut;
		$multiply = $prevStockOut / $prevJml;
		$newStockOut = $jml * $multiply;
		
		if($newStockBarang >= $newStockOut)
		{
			$DetailRecord->jml = $jml;
			$DetailRecord->diskon = $diskon;
			$DetailRecord->total = $subtotal;
			$DetailRecord->save();
			
			$StockInOutRecord = new StockInOutRecord();
			$StockInOutRecord->id_barang = $idBarang;
			$StockInOutRecord->stok_awal = $StockBarang;
			$StockInOutRecord->stok_in = $prevStockOut;
			$StockInOutRecord->stok_out = 0;
			$StockInOutRecord->stok_akhir = $newStockBarang;
			$StockInOutRecord->keterangan = "Edit Data Detail Penjualan";
			$StockInOutRecord->id_transaksi = $id;
			$StockInOutRecord->jns_transaksi = '0';
			$StockInOutRecord->tgl = date('Y-m-d');
			$StockInOutRecord->wkt = date('G:i:s');
			$StockInOutRecord->save();
			
			$StockInOutRecord = new StockInOutRecord();
			$StockInOutRecord->id_barang = $idBarang;
			$StockInOutRecord->stok_awal = $newStockBarang;
			$StockInOutRecord->stok_in = 0;
			$StockInOutRecord->stok_out = $newStockOut;
			$StockInOutRecord->stok_akhir = $newStockBarang - $newStockOut;
			$StockInOutRecord->keterangan = "Edit Data Detail Penjualan";
			$StockInOutRecord->id_transaksi = $id;
			$StockInOutRecord->jns_transaksi = '0';
			$StockInOutRecord->tgl = date('Y-m-d');
			$StockInOutRecord->wkt = date('G:i:s');
			$StockInOutRecord->save();
			
			$StockBarangRecord->stok = $newStockBarang - $newStockOut;
			$StockBarangRecord->save();
			
			$idTrans = $DetailRecord->id_transaksi;
			$sqlTot = "SELECT 
										SUM(tbt_penjualan_detail.total) AS total
									FROM 
										tbt_penjualan_detail
									WHERE 
										tbt_penjualan_detail.id_transaksi = '$idTrans' 
										AND tbt_penjualan_detail.deleted = '0' 
									GROUP BY 
										tbt_penjualan_detail.id_transaksi ";
										
			$arrTot = $this->queryAction($sqlTot,'S');
			$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$idTrans,'0','0','Penjualan Barang');
			if($JurnalBukuBesarRecord)
			{
				$JurnalBukuBesarRecord->jml_transaksi = $arrTot[0]['total'];
				$JurnalBukuBesarRecord->save();
			}
			
			$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Detail Penjualan Telah Dirubah");
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
					toastr.error("Stok Barang Kurang !");
					unloadContent();
					');
		}
	}
	
	public function hapusDetailClicked($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$DetailRecord = PenjualanDetailRecord::finder()->findByPk($id);
		$DetailRecord->deleted = '1';
		$DetailRecord->save();
		$idBarang = $DetailRecord->id_barang;
		$prevJml = $DetailRecord->jml;
		$sqlStockOut = "SELECT
							MAX(tbt_stok_in_out.id) AS id
						FROM
							tbt_stok_in_out 
						WHERE
							id_barang = '$idBarang'
							AND id_transaksi = '$id' 
							AND jns_transaksi = '0' 
							AND stok_out > 0 ";
		$arrQuery = $this->queryAction($sqlStockOut,'S');
		$idMaxStock = $arrQuery[0]['id'];
		
		$StockBarangRecord = StockBarangRecord::finder()->find('id_barang = ?',$idBarang);
		$StockBarang = $StockBarangRecord->stok;
		
		$StockOutRecord = StockInOutRecord::finder()->findByPk($idMaxStock);
		$prevStockOut = $StockOutRecord->stok_out;
		$newStockBarang = $StockBarang + $prevStockOut;
		$multiply = $prevStockOut / $prevJml;
		$newStockIn = $prevJml * $multiply;
		
		$StockInOutRecord = new StockInOutRecord();
		$StockInOutRecord->id_barang = $idBarang;
		$StockInOutRecord->stok_awal = $StockBarang;
		$StockInOutRecord->stok_in = $newStockIn;
		$StockInOutRecord->stok_out = 0;
		$StockInOutRecord->stok_akhir = $StockBarang + $newStockIn;
		$StockInOutRecord->keterangan = "Hapus Data Detail Penjualan";
		$StockInOutRecord->id_transaksi = $id;
		$StockInOutRecord->jns_transaksi = '0';
		$StockInOutRecord->tgl = date('Y-m-d');
		$StockInOutRecord->wkt = date('G:i:s');
		$StockInOutRecord->save();
			
		$StockBarangRecord->stok = $StockBarang + $newStockIn;
		$StockBarangRecord->save();
		
		$idTrans = $DetailRecord->id_transaksi;
		$sqlTot = "SELECT 
										SUM(tbt_penjualan_detail.total) AS total
									FROM 
										tbt_penjualan_detail
									WHERE 
										tbt_penjualan_detail.id_transaksi = '$idTrans' 
										AND tbt_penjualan_detail.deleted = '0' 
									GROUP BY 
										tbt_penjualan_detail.id_transaksi ";
										
			$arrTot = $this->queryAction($sqlTot,'S');
			$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$idTrans,'0','0','Penjualan Barang');
			if($JurnalBukuBesarRecord)
			{
				$JurnalBukuBesarRecord->jml_transaksi = $arrTot[0]['total'];
				$JurnalBukuBesarRecord->save();
			}
				
		$tblBody = $this->BindGrid();
		$tblBodyDetail = $this->BindGridDetail($DetailRecord->id_transaksi);
						
		$this->getPage()->getClientScript()->registerEndScript
				('','
					toastr.info("Detail Penjualan Telah Dihapus");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					jQuery("#tableDetail").dataTable().fnDestroy();
					jQuery("#tableDetail tbody").empty();
					jQuery("#tableDetail tbody").append("'.$tblBodyDetail.'");
					BindGrid();
					BindGridDetail();
					unloadContent();
				');
					
	}
	
	public function hapusClicked($sender,$param)
	{
		$idTrans = $param->CallBackParameter->id;
			$sqlDetail = "SELECT
							tbt_penjualan_detail.id,
							tbt_penjualan_detail.id_barang,
							tbt_penjualan_detail.id_harga,
							tbm_barang.nama AS barang,
							tbt_penjualan_detail.jml,
							tbt_penjualan_detail.harga,
							tbt_penjualan_detail.diskon,
							tbt_penjualan_detail.total
						FROM
							tbt_penjualan_detail
						INNER JOIN tbm_barang ON tbm_barang.id = tbt_penjualan_detail.id_barang
						WHERE
							tbt_penjualan_detail.deleted ='0'
							AND tbt_penjualan_detail.id_transaksi = '$idTrans' ";
			$arrDetail = $this->queryAction($sqlDetail,'S');
		foreach($arrDetail as $rowDetail)
		{
			$id = $rowDetail['id'];
			$DetailRecord = PenjualanDetailRecord::finder()->findByPk($id);
			$DetailRecord->deleted = '1';
			$DetailRecord->save();
			$idBarang = $DetailRecord->id_barang;
			$prevJml = $DetailRecord->jml;
			$sqlStockOut = "SELECT
								MAX(tbt_stok_in_out.id) AS id
							FROM
								tbt_stok_in_out 
							WHERE
								id_barang = '$idBarang'
								AND id_transaksi = '$id' 
								AND jns_transaksi = '0' 
								AND stok_out > 0 ";
			$arrQuery = $this->queryAction($sqlStockOut,'S');
			$idMaxStock = $arrQuery[0]['id'];
			
			$StockBarangRecord = StockBarangRecord::finder()->find('id_barang = ?',$idBarang);
			$StockBarang = $StockBarangRecord->stok;
			
			$StockOutRecord = StockInOutRecord::finder()->findByPk($idMaxStock);
			$prevStockOut = $StockOutRecord->stok_out;
			$newStockBarang = $StockBarang + $prevStockOut;
			$multiply = $prevStockOut / $prevJml;
			$newStockIn = $prevJml * $multiply;
			
			$StockInOutRecord = new StockInOutRecord();
			$StockInOutRecord->id_barang = $idBarang;
			$StockInOutRecord->stok_awal = $StockBarang;
			$StockInOutRecord->stok_in = $newStockIn;
			$StockInOutRecord->stok_out = 0;
			$StockInOutRecord->stok_akhir = $StockBarang + $newStockIn;
			$StockInOutRecord->keterangan = "Hapus Data Detail Penjualan";
			$StockInOutRecord->id_transaksi = $id;
			$StockInOutRecord->jns_transaksi = '0';
			$StockInOutRecord->tgl = date('Y-m-d');
			$StockInOutRecord->wkt = date('G:i:s');
			$StockInOutRecord->save();
				
			$StockBarangRecord->stok = $StockBarang + $newStockIn;
			$StockBarangRecord->save();
		}
		$PenjualanRecord = PenjualanRecord::finder()->findByPk($idTrans);
		$PenjualanRecord->deleted = '1';
		$PenjualanRecord->save();
		
		$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$idTrans,'0','0','Penjualan Barang');
		if($JurnalBukuBesarRecord)
		{
			$JurnalBukuBesarRecord->deleted = '1';
			$JurnalBukuBesarRecord->save();
		}
			
		$tblBody = $this->BindGrid();
						
		$this->getPage()->getClientScript()->registerEndScript
				('','
					toastr.info("Data Penjualan Telah Dihapus");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
				');
				
	}
	
	public function DDCetakanChanged()
	{
		$idCetakan = $this->DDnmCetakan->SelectedValue;
		if($idCetakan != '')
		{
			$sqlCetakan = "SELECT
							tbt_cetakan_detail.id,
							tbt_cetakan_detail.id_transaksi,
							tbt_cetakan_detail.nama_cetakan,
							tbt_cetakan_detail.jns_tinta,
							tbt_cetakan_detail.hrg_tinta,
							tbt_cetakan_detail.jns_laminasi,
							tbt_cetakan_detail.hrg_laminasi,
							tbt_cetakan_detail.jns_spiral,
							tbt_cetakan_detail.hrg_spiral,
							tbt_cetakan_detail.jns_hotprint,
							tbt_cetakan_detail.hrg_hotprint,
							tbt_cetakan_detail.total_modal,
							tbt_cetakan_detail.persen,
							tbt_cetakan_detail.jumlah_pesanan,
							tbt_cetakan_detail.est_hari,
							tbt_cetakan_detail.tt_hari,
							tbt_cetakan_detail.lembur,
							tbt_cetakan_detail.total_harga_jual
						FROM
							tbt_cetakan_detail
						WHERE
							tbt_cetakan_detail.deleted ='0'
							AND tbt_cetakan_detail.id = '$idCetakan' ";
			$arrCetakan = $this->queryAction($sqlCetakan,'S');
			var_dump($sqlCetakan);
			if($arrCetakan)
			{
				$jnsTinta = $arrCetakan[0]['jns_tinta'];
				$JnsLaminasi = $arrCetakan[0]['jns_laminasi'];
				$spiralUkuran = $arrCetakan[0]['jns_spiral'];
				$jnsHotprint = $arrCetakan[0]['jns_hotprint'];
				$hargaTinta = number_format($arrCetakan[0]['hrg_tinta'],2,".",",");
				$hargaLaminasi = number_format($arrCetakan[0]['hrg_laminasi'],2,".",",");
				$hargaSpiral = number_format($arrCetakan[0]['hrg_spiral'],2,".",",");
				$hargaHotprint = number_format($arrCetakan[0]['hrg_hotprint'],2,".",",");
				$totalModal = number_format($arrCetakan[0]['total_modal'],2,".",",");
				$totalPersen = number_format($arrCetakan[0]['persen'],2,".",",");
				$jmlPesanan = $arrCetakan[0]['jumlah_pesanan'];
				$estHari = $arrCetakan[0]['est_hari'];
				$ttHari = $arrCetakan[0]['tt_hari'];
				$lembur = $arrCetakan[0]['lembur'];
				$TotalHargaJual = number_format($arrCetakan[0]['total_harga_jual'],2,".",",");
				
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
				
				$sqlBahan = "SELECT
								tbt_cetakan_detail_bahan.id_barang,
								tbm_barang.nama,
								tbt_cetakan_detail_bahan.jml,
								tbt_cetakan_detail_bahan.ukuran,
								tbt_cetakan_detail_bahan.harga
							FROM
								tbt_cetakan_detail_bahan
							INNER JOIN tbm_barang ON tbm_barang.id = tbt_cetakan_detail_bahan.id_barang
							WHERE
								tbt_cetakan_detail_bahan.deleted = '0' 
								AND tbt_cetakan_detail_bahan.id_cetakan_detail = '$idCetakan' ";
				$arrBahan = $this->queryAction($sqlBahan,'S');
				$tblBodyBahan = "";
				if($arrBahan)
				{
					foreach($arrBahan as $rowBahan)
					{
						$tblBodyBahan .= '<tr>';
						$tblBodyBahan .= '<td>'.$rowBahan['nama'].'</td>';
						$tblBodyBahan .= '<td>'.$rowBahan['jml'].'</td>';
						$tblBodyBahan .= '<td>'.$rowBahan['ukuran'].'</td>';
						$tblBodyBahan .= '<td>'.number_format($rowBahan['harga'],2,".",",").'</td>';
						$tblBodyBahan .= '</tr>';
					}
					
				}
				
				$sqlParam = "SELECT
								tbt_cetakan_detail_param.parameter,
								tbt_cetakan_detail_param.harga
							FROM
								tbt_cetakan_detail_param
							WHERE
								tbt_cetakan_detail_param.deleted = '0' 
								AND tbt_cetakan_detail_param.id_cetakan_detail = '$idCetakan' ";
				$arrParam = $this->queryAction($sqlParam,'S');
				$tblBodyParam = "";
				if($arrParam)
				{
					foreach($arrParam as $rowParam)
					{
						$tblBodyParam .= '<tr>';
						$tblBodyParam .= '<td>'.$rowParam['parameter'].'</td>';
						$tblBodyParam .= '<td>'.number_format($rowParam['harga'],2,".",",").'</td>';
						$tblBodyParam .= '</tr>';
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
		} 
	}
	
	public function cetakBill($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$src = "index.php?page=Transaksi.CetakBillPenjualan&id=".$id;
			
		$this->getPage()->getClientScript()->registerEndScript('','
		jQuery("#modal-3  .modal-body").empty();
		jQuery("<iframe id=\"BillPdf\" style=\"width:100%;height:100%;\" frameborder=\"0\" src=\"'.$src.'\">").appendTo("#modal-3 .modal-body");
		jQuery("#modal-3").modal("show");
		unloadContent();
		');
	}
	
}
?>
