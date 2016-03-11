<?PHP
class LaporanPembelian extends MainConf
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
						tbt_pembelian.id,
						tbt_pembelian.id_pemasok,
						tbm_pemasok.nama AS pemasok,
						tbt_pembelian.tgl_transaksi,
						tbt_pembelian.wkt_transaksi,
						tbt_pembelian.st_posting
					FROM 
						tbt_pembelian
					INNER JOIN tbm_pemasok ON tbm_pemasok.id = tbt_pembelian.id_pemasok
					WHERE
						tbt_pembelian.deleted ='0' ";
		if($periode == '0')
		{
			$bulan = $this->DDBulan->SelectedValue;
			$tahun = $this->DDTahun->SelectedValue;
			if($bulan != '' && $tahun != '')
			{
				$sqlTrans .="AND MONTH(tbt_pembelian.tgl_transaksi) = '$bulan' AND YEAR(tbt_pembelian.tgl_transaksi) = '$tahun' ";
			}
		}
		elseif($periode == '1')
		{
			$tahun = $this->DDTahun->SelectedValue;
			if($tahun != '')
			{
				$sqlTrans .="AND YEAR(tbt_pembelian.tgl_transaksi) = '$tahun' ";
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
				$sqlTrans .="AND tbt_pembelian.tgl_transaksi BETWEEN '$tgl1' AND '$tgl2' ";
			}
		}		
		
		$arrTrans = $this->queryAction($sqlTrans,'S');
		$tblBody = '';
		if($arrTrans)
		{
			foreach($arrTrans as $row)
			{
				$idTrans =  $row['id'];
				$sqlTot = "SELECT 
								COUNT(tbt_pembelian_detail.id) AS total_item,
								SUM(tbt_pembelian_detail.total) AS total
							FROM 
								tbt_pembelian_detail
							WHERE 
								tbt_pembelian_detail.id_transaksi = '$idTrans' 
								AND tbt_pembelian_detail.deleted = '0' 
							GROUP BY 
								tbt_pembelian_detail.id_transaksi ";
				$arrTot = $this->queryAction($sqlTot,'S');
				//var_dump($arrTot[0]['total_item']);
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['tgl_transaksi'].'</td>';
				$tblBody .= '<td>'.$row['wkt_transaksi'].'</td>';
				$tblBody .= '<td>'.$row['pemasok'].'</td>';
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
								SUM(tbt_pembelian_detail.total) AS total
							FROM 
								tbt_pembelian_detail
							WHERE 
								tbt_pembelian_detail.id_transaksi = '$idTrans' 
								AND tbt_pembelian_detail.deleted = '0' 
							GROUP BY 
								tbt_pembelian_detail.id_transaksi ";
			$arrTot = $this->queryAction($sqlTot,'S');
			$PembelianRecord = PembelianRecord::finder()->findByPk($idTrans);
			$PembelianRecord->st_posting = '1';
			$PembelianRecord->save();
			
			$this->InsertJurnalBukuBesar(
											$PembelianRecord->id,
											'0',
											'1',
											$PembelianRecord->tgl_transaksi,
											$PembelianRecord->wkt_transaksi,
											'Pembelian Barang',
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
	
	public function BindGridDetail($idTrans)
	{
		$sqlDetail = "SELECT
						tbt_pembelian_detail.id,
						tbt_pembelian_detail.id_barang,
						tbm_barang.nama AS barang,
						tbt_pembelian_detail.jml,
						tbt_pembelian_detail.harga,
						tbt_pembelian_detail.total
					FROM
						tbt_pembelian_detail
					INNER JOIN tbm_barang ON tbm_barang.id = tbt_pembelian_detail.id_barang
					WHERE
						tbt_pembelian_detail.deleted ='0'
						AND tbt_pembelian_detail.id_transaksi = '$idTrans' ";
		$arrDetail = $this->queryAction($sqlDetail,'S');
		
		$tblBody = '';
		if($arrDetail > 0)
		{
			foreach($arrDetail as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['barang'].'</td>';
				$tblBody .= '<td><input id=\"jmlBrgDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control autoJml\" data-a-sep=\".\" data-a-dec=\",\" type=\"text\" value=\"'.$row['jml'].'\" ></td>';
				$tblBody .= '<td><input id=\"hrgBrgDet'.$row['id'].'\" onChange=\"calculateClicked('.$row['id'].')\" class=\"form-control autoJml\" type=\"text\" value=\"'.$row['harga'].'\" ></td>';
				$tblBody .= '<td><label id=\"labelTotal'.$row['id'].'\" class=\"autoJml\">'.$row['total'].'</label></td>';		
				$tblBody .= '<td>';	
				if($this->User->IsUserGroup == '1')
				{
					$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-pencil\"></i>Simpan</a>&nbsp;&nbsp;';	
					$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"hapusDetailClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
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
	
	public function detailClicked($sender,$param)
	{
		$idTrans = $param->CallbackParameter->id;
		$tblBody = $this->BindGridDetail($idTrans);
		
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
	
	public function simpanClicked($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$newJml = $param->CallbackParameter->jml;
		$newHarga = $param->CallbackParameter->harga;
		$newTotal = $param->CallbackParameter->subtotal;
		
		$DetailRecord = PembelianDetailRecord::finder()->findByPk($id);
		
		$prevJml = $DetailRecord->jml;
		$idBarang = $DetailRecord->id_barang;
		
		$DetailRecord->jml = $newJml;
		$DetailRecord->harga = $newHarga;
		$DetailRecord->total = $newTotal;
		$DetailRecord->save();
			
		$StockBarangRecord = StockBarangRecord::finder()->find('id_barang = ?',$idBarang);
		$StockBarang = $StockBarangRecord->stok;
		
		$TempStock = $StockBarang - $prevJml;
		
		$StockInOutRecord = new StockInOutRecord();
		$StockInOutRecord->id_barang = $idBarang;
		$StockInOutRecord->stok_awal = $StockBarang;
		$StockInOutRecord->stok_in = 0;
		$StockInOutRecord->stok_out = $prevJml ;
		$StockInOutRecord->stok_akhir = $TempStock;
		$StockInOutRecord->keterangan = "Edit Data Detail Pembelian";
		$StockInOutRecord->id_transaksi = $id;
		$StockInOutRecord->jns_transaksi = '1';
		$StockInOutRecord->tgl = date('Y-m-d');
		$StockInOutRecord->wkt = date('G:i:s');
		$StockInOutRecord->save();
		
		$StockInOutRecord = new StockInOutRecord();
		$StockInOutRecord->id_barang = $idBarang;
		$StockInOutRecord->stok_awal = $TempStock;
		$StockInOutRecord->stok_in = $newJml;
		$StockInOutRecord->stok_out = 0;
		$StockInOutRecord->stok_akhir = $TempStock + $newJml;
		$StockInOutRecord->keterangan = "Edit Data Detail Pembelian";
		$StockInOutRecord->id_transaksi = $id;
		$StockInOutRecord->jns_transaksi = '1';
		$StockInOutRecord->tgl = date('Y-m-d');
		$StockInOutRecord->wkt = date('G:i:s');
		$StockInOutRecord->save();
		
		$StockBarangRecord->stok = $TempStock + $newJml;
		$StockBarangRecord->save();
		$idTrans = $DetailRecord->id_transaksi;
		$sqlTot = "SELECT 
								SUM(tbt_pembelian_detail.total) AS total
							FROM 
								tbt_pembelian_detail
							WHERE 
								tbt_pembelian_detail.id_transaksi = '$idTrans' 
								AND tbt_pembelian_detail.deleted = '0' 
							GROUP BY 
								tbt_pembelian_detail.id_transaksi ";
			$arrTot = $this->queryAction($sqlTot,'S');
		$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$idTrans,'0','1','Pembelian Barang');
		if($JurnalBukuBesarRecord)
		{
			$JurnalBukuBesarRecord->jml_transaksi = $arrTot[0]['total'];
			$JurnalBukuBesarRecord->save();
		}	
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Data Detail Pembelian Telah Dirubah ");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');	
					
	}
	
	function hapusDetailClicked($sender,$param)
	{
		$id = $param->CallBackParameter->id;
		$DetailRecord = PembelianDetailRecord::finder()->findByPk($id);
		$DetailRecord->deleted = '1';
		$DetailRecord->save();
		$idBarang = $DetailRecord->id_barang;
		$prevJml = $DetailRecord->jml;
			
		$StockBarangRecord = StockBarangRecord::finder()->find('id_barang = ?',$idBarang);
		$StockBarang = $StockBarangRecord->stok;
			
		$StockInOutRecord = new StockInOutRecord();
		$StockInOutRecord->id_barang = $idBarang;
		$StockInOutRecord->stok_awal = $StockBarang;
		$StockInOutRecord->stok_in = 0;
		$StockInOutRecord->stok_out = $prevJml ;
		$StockInOutRecord->stok_akhir = $StockBarang - $prevJml;
		$StockInOutRecord->keterangan = "Hapus Data Detail Pembelian";
		$StockInOutRecord->id_transaksi = $id;
		$StockInOutRecord->jns_transaksi = '1';
		$StockInOutRecord->tgl = date('Y-m-d');
		$StockInOutRecord->wkt = date('G:i:s');
		$StockInOutRecord->save();
				
		$StockBarangRecord->stok = $StockBarang - $prevJml;
		$StockBarangRecord->save();
		
		$idTrans = $DetailRecord->id_transaksi;
		$sqlTot = "SELECT 
								SUM(tbt_pembelian_detail.total) AS total
							FROM 
								tbt_pembelian_detail
							WHERE 
								tbt_pembelian_detail.id_transaksi = '$idTrans' 
								AND tbt_pembelian_detail.deleted = '0' 
							GROUP BY 
								tbt_pembelian_detail.id_transaksi ";
			$arrTot = $this->queryAction($sqlTot,'S');
		$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$idTrans,'0','1','Pembelian Barang');
		if($JurnalBukuBesarRecord)
		{
			$JurnalBukuBesarRecord->jml_transaksi = $arrTot[0]['total'];
			$JurnalBukuBesarRecord->save();
		}	
		
		$tblBodyDetail = $this->BindGridDetail($idTrans);
		
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Detail Pembelian Telah Dihapus ");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					jQuery("#tableDetail").dataTable().fnDestroy();
					jQuery("#tableDetail tbody").empty();
					jQuery("#tableDetail tbody").append("'.$tblBodyDetail.'");
					BindGridDetail();
					unloadContent();
					');	
		
	}
	function hapusClicked($sender,$param)
	{
		$idTrans = $param->CallBackParameter->id;
			$sqlDetail = "SELECT
							tbt_pembelian_detail.id,
							tbt_pembelian_detail.id_barang,
							tbm_barang.nama AS barang,
							tbt_pembelian_detail.jml,
							tbt_pembelian_detail.harga,
							tbt_pembelian_detail.total
						FROM
							tbt_pembelian_detail
						INNER JOIN tbm_barang ON tbm_barang.id = tbt_pembelian_detail.id_barang
						WHERE
							tbt_pembelian_detail.deleted = '0'
							AND tbt_pembelian_detail.id_transaksi = '$idTrans' ";
			$arrDetail = $this->queryAction($sqlDetail,'S');
		foreach($arrDetail as $rowDetail)
		{
			$id = $rowDetail['id'];
			$DetailRecord = PembelianDetailRecord::finder()->findByPk($id);
			$DetailRecord->deleted = '1';
			$DetailRecord->save();
			$idBarang = $DetailRecord->id_barang;
			$prevJml = $DetailRecord->jml;
			
			$StockBarangRecord = StockBarangRecord::finder()->find('id_barang = ?',$idBarang);
			$StockBarang = $StockBarangRecord->stok;
			
			$StockInOutRecord = new StockInOutRecord();
			$StockInOutRecord->id_barang = $idBarang;
			$StockInOutRecord->stok_awal = $StockBarang;
			$StockInOutRecord->stok_in = 0;
			$StockInOutRecord->stok_out = $prevJml ;
			$StockInOutRecord->stok_akhir = $StockBarang - $prevJml;
			$StockInOutRecord->keterangan = "Hapus Data Detail Pembelian";
			$StockInOutRecord->id_transaksi = $id;
			$StockInOutRecord->jns_transaksi = '1';
			$StockInOutRecord->tgl = date('Y-m-d');
			$StockInOutRecord->wkt = date('G:i:s');
			$StockInOutRecord->save();
				
			$StockBarangRecord->stok = $StockBarang - $prevJml;
			$StockBarangRecord->save();
		}
		$PembelianRecord = PembelianRecord::finder()->findByPk($idTrans);
		$PembelianRecord->deleted = '1';
		$PembelianRecord->save();
		
		$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$idTrans,'0','1','Pembelian Barang');
		if($JurnalBukuBesarRecord)
		{
			$JurnalBukuBesarRecord->deleted = '1';
			$JurnalBukuBesarRecord->save();
		}
			
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Data Pembelian Telah Dihapus ");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');				
	}
	
	
	public function cetakBill($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$src = "index.php?page=Transaksi.CetakBillPembelian&id=".$id;
		$this->getPage()->getClientScript()->registerEndScript('','
		jQuery("#modal-3 .modal-body").empty();
		jQuery("<iframe id=\"BillPdf\" style=\"width:100%;height:100%;\" frameborder=\"0\" src=\"'.$src.'\">").appendTo("#modal-3 .modal-body");
		jQuery("#modal-3").modal("show");
		unloadContent();
		');
	}
	
}
?>
