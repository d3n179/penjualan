<?PHP
class LaporanPercetakan extends MainConf
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
	
	public function BindGrid()
	{
		$periode = $this->Periode->SelectedValue;
						
		$sqlTrans2 = "SELECT 
						'1' AS st_trans,
						tbt_cetakan.id,
						tbt_cetakan.jns_transaksi,
						tbt_cetakan.nama_pelanggan,
						tbt_cetakan.tgl_transaksi,
						tbt_cetakan.wkt_transaksi,
						tbt_cetakan.total,
						tbt_cetakan.st_lunas
					FROM 
						tbt_cetakan
					WHERE
						tbt_cetakan.deleted ='0' ";
						 			
		if($periode == '0')
		{
			$bulan = $this->DDBulan->SelectedValue;
			$tahun = $this->DDTahun->SelectedValue;
			if($bulan != '' && $tahun != '')
			{
				$sqlTrans2 .="AND MONTH(tbt_cetakan.tgl_transaksi) = '$bulan' AND YEAR(tbt_cetakan.tgl_transaksi) = '$tahun' ";
			}
		}
		elseif($periode == '1')
		{
			$tahun = $this->DDTahun->SelectedValue;
			if($tahun != '')
			{
				$sqlTrans2 .="AND YEAR(tbt_cetakan.tgl_transaksi) = '$tahun' ";
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
				$sqlTrans2 .="AND tbt_cetakan.tgl_transaksi BETWEEN '$tgl1' AND '$tgl2' ";
			}
		}		
		
		$sqlFull = $sqlTrans2;
		$arrTrans = $this->queryAction($sqlFull,'S');
		var_dump($sqlFull);
		$tblBody = '';
		if($arrTrans)
		{
			foreach($arrTrans as $row)
			{
				$idTrans =  $row['id'];
				$jnsTrans = "Transaksi Cetakan";
				$sqlTot = "SELECT 
									COUNT(tbt_cetakan_detail.id) AS total_item,
									SUM(tbt_cetakan_detail.subtotal) AS total
								FROM 
									tbt_cetakan_detail
								WHERE 
									tbt_cetakan_detail.id_transaksi = '$idTrans' 
									AND tbt_cetakan_detail.deleted = '0' 
								GROUP BY 
									tbt_cetakan_detail.id_transaksi ";
					$arrTot = $this->queryAction($sqlTot,'S');
				
				$sqlCek = "SELECT 
									COUNT(tbt_cetakan_bayar.id) AS posting
								FROM 
									tbt_cetakan_bayar
								WHERE 
									tbt_cetakan_bayar.id_transaksi = '$idTrans' 
									AND tbt_cetakan_bayar.deleted = '0'
									AND tbt_cetakan_bayar.st_posting = '0' ";
				$arrPos = $this->queryAction($sqlCek,'S');
					
				if($row['jns_transaksi'] == '0')
					$jnsPelanggan = "Member / Regular";
				else
					$jnsPelanggan = "OTC";
				
				if($row['st_lunas'] == '0')
					$stLunas = "Belum Lunas";
				elseif($row['st_lunas'] == '1')
					$stLunas = "Lunas";
					
				//var_dump($arrTot[0]['total_item']);
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['tgl_transaksi'].'</td>';
				$tblBody .= '<td>'.$row['wkt_transaksi'].'</td>';
				$tblBody .= '<td>'.$jnsPelanggan.'</td>';
				$tblBody .= '<td>'.$row['nama_pelanggan'].'</td>';
				$tblBody .= '<td>'.$arrTot[0]['total_item'].'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['total'],2).'</td>';
				$tblBody .= '<td>'.$stLunas.'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"detailClicked('.$row['id'].')\"><i class=\"entypo-doc-text-inv\"></i>Detail</a>&nbsp;&nbsp;';	
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"cetakClicked('.$row['id'].')\"><i class=\"entypo-print\"></i>Cetak</a>&nbsp;&nbsp;';
				
				if($row['st_lunas'] == '0')	
					$tblBody .= '<a href=\"#\" class=\"btn btn-blue btn-sm btn-icon icon-left\" OnClick=\"bayarClicked('.$row['id'].')\"><i class=\"entypo-credit-card\"></i>Bayar</a>&nbsp;&nbsp;';	
				
				if($arrPos[0]['posting'] > 0)	
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
	
	public function postingClicked($sender,$param)
	{
		$idTrans = $param->CallbackParameter->id;
		$sql = "SELECT 
					tbt_cetakan_bayar.id
				FROM 
					tbt_cetakan_bayar
				WHERE 
					tbt_cetakan_bayar.id_transaksi = '$idTrans' 
					AND tbt_cetakan_bayar.deleted = '0'
					AND tbt_cetakan_bayar.st_posting = '0' ";
		$arr = $this->queryAction($sql,'S');
		foreach($arr as $row)
		{
			$CetakanBayarRecord = CetakanBayarRecord::finder()->findByPk($row['id']);
			$CetakanBayarRecord->st_posting = '1';
			$CetakanBayarRecord->save();
			$this->InsertJurnalBukuBesar(
											$CetakanBayarRecord->id,
											'1',
											'0',
											$CetakanBayarRecord->tgl_bayar,
											$CetakanBayarRecord->wkt_bayar,
											'Pembayaran Piutang Cetakan',
											$CetakanBayarRecord->jml_bayar
											);
											
		}
		
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
	
	public function hapusClicked($sender,$param)
	{
		$idTrans = $param->CallBackParameter->id;
		$sql = "SELECT 
					tbt_cetakan_bayar.id
				FROM 
					tbt_cetakan_bayar
				WHERE 
					tbt_cetakan_bayar.id_transaksi = '$idTrans' 
					AND tbt_cetakan_bayar.deleted = '0'";
		$arr = $this->queryAction($sql,'S');
		foreach($arr as $row)
		{
			$CetakanBayarRecord = CetakanBayarRecord::finder()->findByPk($row['id']);
			$CetakanBayarRecord->deleted = '1';
			$CetakanBayarRecord->save();
			$idDetail = $row['id'];
			
			$updateQuery = "UPDATE 
								tbt_jurnal_buku_besar 
							SET 
								deleted = '1' 
							WHERE  
								id_transaksi = '$idDetail' 
								AND sumber_transaksi = '1' 
								AND jns_transaksi = '0' 
								AND (
										keterangan = 'Pembayaran Piutang Cetakan' 
										OR keterangan = 'Pembayaran DP Cetakan'
									) ";
									
			$this->queryAction($updateQuery,'C');							
		}
		$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
		$CetakanRecord->deleted = '1';
		$CetakanRecord->save();
		
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Transaksi Telah Dihapus");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');
	}
	
	public function bayarBtnClicked($sender,$param)
	{
		$idTrans = $this->idTransCetakan->Value;
		$sisaBayar = $this->toInt($this->sisaBayar->Text);
		$jmlBayar = $this->toInt($this->jmlBayar->Text);
		if($jmlBayar >= $sisaBayar)
		{
			$kembalian = $jmlBayar - $sisaBayar;
			$jmlTrans = $sisaBayar;
			$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
			$CetakanRecord->st_lunas = '1';
			$CetakanRecord->save();
			$sisaPembayaran = 0;
		}
		else
		{
			$kembalian = 0;
			$jmlTrans = $jmlBayar;
			$sisaPembayaran = $sisaBayar - $jmlBayar;
		}
		
		$tglTrans = date('Y-m-d');
		$wktTrans = date('G:i:s');
		$CetakanBayarRecord = new CetakanBayarRecord();
		$CetakanBayarRecord->id_transaksi = $idTrans;
		$CetakanBayarRecord->tgl_bayar = $tglTrans;
		$CetakanBayarRecord->wkt_bayar = $wktTrans;
		$CetakanBayarRecord->jml_bayar = $jmlBayar;
		$CetakanBayarRecord->kembalian = $kembalian;
		$CetakanBayarRecord->sisa_bayar = $sisaPembayaran;
		$CetakanBayarRecord->st_dp = '1';
		$CetakanBayarRecord->st_posting = '1';
		$CetakanBayarRecord->save();
		
		$this->InsertJurnalBukuBesar(
											$CetakanBayarRecord->id,
											'1',
											'0',
											$tglTrans,
											$wktTrans,
											'Pembayaran Piutang Cetakan',
											$jmlTrans
											);
											
		
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Pelunasan Telah Diproses");
					jQuery("#modal-4").modal("hide");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					cetakCicilanClicked('.$CetakanBayarRecord->id.');
					unloadContent();
					');
	}
	
	public function bayarClicked($sender,$param)
	{
		$idTrans = $param->CallbackParameter->id;
		$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
		$totalCetakan = $CetakanRecord->total;
		$sqlSum = "SELECT
						SUM(
							tbt_cetakan_bayar.jml_bayar
						) AS total_bayar
					FROM
						tbt_cetakan_bayar
					WHERE
						tbt_cetakan_bayar.id_transaksi = '$idTrans'
						AND tbt_cetakan_bayar.deleted = '0'
					GROUP BY
						tbt_cetakan_bayar.id_transaksi";
		$arrSum = $this->queryAction($sqlSum,'S');
		$totalBayar = $arrSum[0]['total_bayar'];
		$sisabayar = $totalCetakan - $totalBayar;
		$this->idTransCetakan->Value = $idTrans;
		$this->sisaBayar->Text = $this->formatCurrency($sisabayar,2);
		$this->jmlBayar->Text = "";
		$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#modal-4").modal("show");
						unloadContent();
						');
	}
	
	public function BindGridDetail($idTrans,$stBayar = '0')
	{
		$sqlDetail = "SELECT
							tbt_cetakan_detail.id,
							tbt_cetakan_detail.id_cetakan,
							tbt_cetakan_detail.nm_cetakan,
							tbt_cetakan_detail.hrg_cetakan,
							tbt_cetakan_detail.jumlah_pesanan,
							tbt_cetakan_detail.est_hari,
							tbt_cetakan_detail.tt_hari,
							tbt_cetakan_detail.lembur,
							tbt_cetakan_detail.subtotal
						FROM
							tbt_cetakan_detail
						WHERE
							tbt_cetakan_detail.deleted ='0'
							AND tbt_cetakan_detail.id_transaksi = '$idTrans' ";
			$arrDetail = $this->queryAction($sqlDetail,'S');
			
			$tblBody = '';
			if($arrDetail > 0)
			{
				foreach($arrDetail as $row)
				{
					$tblBody .= '<tr>';
					$tblBody .= '<td><input type=\"hidden\" class=\"idCetakan\" id=\"idDet'.$row['id'].'\" value=\"'.$row['id'].'\"/></td>';
					$tblBody .= '<td>'.$row['nm_cetakan'].'</td>';
					$tblBody .= '<td><input id=\"jmlPesanDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control autoJml jmlCetakan\" data-a-sep=\".\" data-a-dec=\",\" type=\"text\" value=\"'.$row['jumlah_pesanan'].'\" ></td>';
					$tblBody .= '<td><input id=\"hrgCetakDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control autoJml hrgCetakan\" type=\"text\" value=\"'.$row['hrg_cetakan'].'\" ></td>';
					$tblBody .= '<td><input id=\"estHariDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control formatNumber estCetakan\" data-a-sep=\".\" data-a-dec=\",\" type=\"text\" value=\"'.$row['est_hari'].'\" ></td>';
					$tblBody .= '<td><input id=\"ttHariDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control formatNumber ttCetakan\" data-a-sep=\".\" data-a-dec=\",\" type=\"text\" value=\"'.$row['tt_hari'].'\" ></td>';
					$tblBody .= '<td><input id=\"lemburDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control formatNumber lemburCetakan\" data-a-sep=\".\" data-a-dec=\",\" type=\"text\" value=\"'.$row['lembur'].'\" > %</td>';
					$tblBody .= '<td><label id=\"labelTotal'.$row['id'].'\" class=\"autoJml subtotalCetakan\">'.$row['subtotal'].'</label></td>';		
					$tblBody .= '<td>';
					if($this->User->IsUserGroup == '1' && $stBayar == '0')
					{		
						$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"hapusDetClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
					}
					$tblBody .= '</td>';	
					$tblBody .= '</tr>';
				}
			}
			else
			{
				$tblBody = '';
			}
			
			return $tblBody;
	}
	
	function hapusDetailClicked($sender,$param)
	{
		$id = $param->CallBackParameter->id;
		$CetakanDetailRecord = CetakanDetailRecord::finder()->findbyPk($id);
		if($CetakanDetailRecord)
		{
			$CetakanDetailRecord->deleted = '1';
			$CetakanDetailRecord->save();
		}
		
		$tblBody = $this->BindGridDetail($CetakanDetailRecord->id_transaksi);
			
		$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.info("Data Detail Transaksi Cetakan Telah Dihapus");
						jQuery("#tableDetail").dataTable().fnDestroy();
						jQuery("#tableDetail tbody").empty();
						jQuery("#tableDetail tbody").append("'.$tblBody.'");
						BindGridDetail();
						calculateAll();
						saveRows();
						unloadContent();
						');
						
	}
	
	public function simpanPerubahanClicked($sender,$param)
	{
		$arrPercetakan = $param->CallbackParameter->arr;
		$diskonCetakan = $param->CallbackParameter->diskonCetakan;
		$subtotalCetakan = $param->CallbackParameter->subtotalCetakan;
		$totalCetakan = $param->CallbackParameter->totalCetakan;
		
		if(count($arrPercetakan) > 0)
		{
			foreach($arrPercetakan as $row)
			{
				$CetakanDetailRecord = CetakanDetailRecord::finder()->findByPk($row->idCetakan);
				$CetakanDetailRecord->hrg_cetakan = $row->hrgCetakan;
				$CetakanDetailRecord->jumlah_pesanan = $row->jmlPesanan;
				$CetakanDetailRecord->est_hari = $row->estHari;
				$CetakanDetailRecord->tt_hari = $row->ttHari;
				$CetakanDetailRecord->lembur = $row->lembur;
				$CetakanDetailRecord->subtotal = $row->subtotal;
				$CetakanDetailRecord->save();
				
				$idTrans = $CetakanDetailRecord->id_transaksi;
			}
			
			$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
			if($CetakanRecord)
			{
				$CetakanRecord->subtotal = $subtotalCetakan;
				$CetakanRecord->diskon = $diskonCetakan;
				$CetakanRecord->total = $totalCetakan;
				$CetakanRecord->save();
			}
		}
		
		$tblBody = $this->BindGridDetail($idTrans);
			
		$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.info("Perubahan Data Telah Disimpan");
						jQuery("#tableDetail").dataTable().fnDestroy();
						jQuery("#tableDetail tbody").empty();
						jQuery("#tableDetail tbody").append("'.$tblBody.'");
						BindGridDetail();
						calculateAll();
						unloadContent();
						');
	}
	
	public function BindGridBayar($idTrans)
	{
		$sqlBayar = "SELECT
							tbt_cetakan_bayar.id,
							tbt_cetakan_bayar.tgl_bayar,
							tbt_cetakan_bayar.wkt_bayar,
							tbt_cetakan_bayar.jml_bayar,
							tbt_cetakan_bayar.sisa_bayar,
							tbt_cetakan_bayar.st_dp
						FROM
							tbt_cetakan_bayar
						WHERE
							tbt_cetakan_bayar.id_transaksi = '$idTrans'
							AND tbt_cetakan_bayar.deleted = '0'
						ORDER BY
							tbt_cetakan_bayar.tgl_bayar ";
			$arrBayar = $this->queryAction($sqlBayar,'S');
			if($arrBayar)
			{
				$tblBodyBayar = '';
				foreach($arrBayar as $rowBayar)
				{
					if($rowBayar['st_dp'] == '0')
						$ket = "DP Awal";
					else
						$ket = "Pembayaran Cicilan";
						
					$tblBodyBayar .= '<tr>';
					$tblBodyBayar .= '<td>'.$rowBayar['tgl_bayar'].'</td>';
					$tblBodyBayar .= '<td>'.$rowBayar['wkt_bayar'].'</td>';
					$tblBodyBayar .= '<td>'.$this->formatCurrency($rowBayar['jml_bayar'],2).'</td>';
					//$tblBodyBayar .= '<td>'.$this->formatCurrency($rowBayar['sisa_bayar'],2).'</td>';	
					$tblBodyBayar .= '<td>'.$ket.'</td>';	
					$tblBodyBayar .= '<td>';
					
					if($rowBayar['st_dp'] != '0')
					{
								
						$tblBodyBayar .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"cetakCicilanClicked('.$rowBayar['id'].')\"><i class=\"entypo-print\"></i>Cetak</a>&nbsp;&nbsp;';
							
					}
					
					if($this->User->IsUserGroup == '1')
					{		
						$tblBodyBayar .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"hapusBayarClicked('.$rowBayar['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
					}
					$tblBodyBayar .= '</td>';
					$tblBodyBayar .= '</tr>';
				}
			}
			else
			{
				$tblBodyBayar = '';
			}
			return $tblBodyBayar;
	}
	
	public function detailClicked($sender,$param)
	{
		$idTrans = $param->CallbackParameter->id;
		$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
		$this->subtotalTrans->Text = '<strong>'.$this->formatCurrency($CetakanRecord->subtotal,2).'</strong>';
		$this->diskonTrans->Text = $this->formatCurrency($CetakanRecord->diskon,2);
		$this->totalTrans->Text = '<strong>'.$this->formatCurrency($CetakanRecord->total,2).'</strong>';
		$tblBodyBayar = $this->BindGridBayar($idTrans);
		if($tblBodyBayar == '')
		{
			$stBayar = '0';
			$stSimpan = 'show()';
		}
		else
		{
			$stBayar = '1';
			$stSimpan = 'hide()';
		}
			
		$tblBody = $this->BindGridDetail($idTrans,$stBayar);
		
			
			
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#tableDetail").dataTable().fnDestroy();
						jQuery("#tableDetail tbody").empty();
						jQuery("#tableDetail tbody").append("'.$tblBody.'");
						jQuery("#tablePelunasan").dataTable().fnDestroy();
						jQuery("#tablePelunasan tbody").empty();
						jQuery("#tablePelunasan tbody").append("'.$tblBodyBayar.'");
						jQuery("#simpanPerubahan").'.$stSimpan.';
						BindGridDetail();
						BindGridPelunasan();
						jQuery("#modal-2").modal("show");
						unloadContent();
						');
		
	}
	
	public function hapusBayarClicked($sender,$param)
	{
		$idBayar = $param->CallBackParameter->id;
		$CetakanBayarRecord = CetakanBayarRecord::finder()->findByPk($idBayar);
		$CetakanBayarRecord->deleted = '1';
		$CetakanBayarRecord->save();
		
		if($CetakanBayarRecord->st_dp == '0')
			$ket = "Pembayaran DP Cetakan";
		else
			$ket = "Pembayaran Piutang Cetakan";
			
		$idTrans = $CetakanBayarRecord->id_transaksi;
		$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
		if($CetakanRecord->st_lunas == '1')
		{
			$CetakanRecord->st_lunas = '0';
			$CetakanRecord->save();
		}
		
		$updateQuery = "UPDATE 
								tbt_jurnal_buku_besar 
							SET 
								deleted = '1' 
							WHERE  
								id_transaksi = '$idBayar' 
								AND sumber_transaksi = '1' 
								AND jns_transaksi = '0' 
								AND keterangan = '$ket' ";
		$this->queryAction($updateQuery,'C');	
		$tblBodyBayar = $this->BindGridBayar($idTrans);
			
			
			$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.info("Data Pelunasan Telah Dihapus");
						jQuery("#tablePelunasan").dataTable().fnDestroy();
						jQuery("#tablePelunasan tbody").empty();
						jQuery("#tablePelunasan tbody").append("'.$tblBodyBayar.'");
						BindGridPelunasan();
						unloadContent();
						');	
		
	}
	
	public function cetakBill($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		
		$src = "index.php?page=Transaksi.CetakBillCetakan&id=".$id;
			
		$this->getPage()->getClientScript()->registerEndScript('','
		jQuery("#modal-3  .modal-body").empty();
		jQuery("<iframe id=\"BillPdf\" style=\"width:100%;height:100%;\" frameborder=\"0\" src=\"'.$src.'\">").appendTo("#modal-3 .modal-body");
		jQuery("#modal-3").modal("show");
		unloadContent();
		');
	}
	
	public function cetakCicilan($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		
		$src = "index.php?page=Transaksi.CetakBillCicilan&id=".$id;
			
		$this->getPage()->getClientScript()->registerEndScript('','
		jQuery("#modal-5  .modal-body").empty();
		jQuery("<iframe id=\"BillPdf\" style=\"width:100%;height:100%;\" frameborder=\"0\" src=\"'.$src.'\">").appendTo("#modal-5 .modal-body");
		jQuery("#modal-5").modal("show");
		unloadContent();
		');
	}
	
}
?>
