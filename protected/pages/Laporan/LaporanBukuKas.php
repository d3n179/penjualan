<?PHP
class LaporanBukuKas extends MainConf
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
		$SumberTrans = $this->DDTransaksi->SelectedValue;
		$tblBody = '';
		
		if($this->DDBulan->SelectedValue != '' && $this->DDTahun->SelectedValue != '')
		{
			$bulan = $this->DDBulan->SelectedValue;
			$tahun = $this->DDTahun->SelectedValue;
			
			if($bulan < 10)
				$bulan = '0'.$bulan;
				
			$dateForm = $tahun.'-'.$bulan.'-01';
			$ModalAwal = ModalTransaksiRecord::finder()->find('st_modal = ? AND deleted = ?',$SumberTrans,'0')->modal;
			
			
			$sqlSumDebet = "SELECT
							SUM(tbt_jurnal_buku_besar.jml_transaksi) AS debet
						FROM
							tbt_jurnal_buku_besar
						WHERE
							tbt_jurnal_buku_besar.deleted = '0' 
							AND tbt_jurnal_buku_besar.sumber_transaksi = '$SumberTrans' 
							AND tbt_jurnal_buku_besar.jns_transaksi = '0'
							AND tbt_jurnal_buku_besar.tgl_transaksi < '$dateForm' 
						GROUP BY tbt_jurnal_buku_besar.sumber_transaksi ";
			$arrDebet = $this->queryAction($sqlSumDebet,'S');
						
			$sqlSumKredit = "SELECT
							SUM(tbt_jurnal_buku_besar.jml_transaksi) AS kredit
						FROM
							tbt_jurnal_buku_besar
						WHERE
							tbt_jurnal_buku_besar.deleted = '0' 
							AND tbt_jurnal_buku_besar.sumber_transaksi = '$SumberTrans' 
							AND tbt_jurnal_buku_besar.jns_transaksi = '1' 
							AND tbt_jurnal_buku_besar.tgl_transaksi < '$dateForm' 
						GROUP BY tbt_jurnal_buku_besar.sumber_transaksi ";
			$arrKredit = $this->queryAction($sqlSumKredit,'S');	
				
			$ModalAwal += $arrDebet[0]['debet'];
			$ModalAwal -= $arrKredit[0]['kredit'];
			
			var_dump($arrDebet[0]['debet']);
			var_dump($arrKredit[0]['kredit']);
			var_dump($ModalAwal);
			$sqlTrans = "SELECT
							tbt_jurnal_buku_besar.id,
							tbt_jurnal_buku_besar.id_transaksi,
							tbt_jurnal_buku_besar.sumber_transaksi,
							tbt_jurnal_buku_besar.jns_transaksi,
							tbt_jurnal_buku_besar.tgl_transaksi,
							tbt_jurnal_buku_besar.wkt_transaksi,
							tbt_jurnal_buku_besar.keterangan,
							tbt_jurnal_buku_besar.jml_transaksi
						FROM
							tbt_jurnal_buku_besar
						WHERE
							tbt_jurnal_buku_besar.deleted = '0' 
							AND tbt_jurnal_buku_besar.sumber_transaksi = '$SumberTrans' ";
			if($periode == '0')
			{
				$bulan = $this->DDBulan->SelectedValue;
				$tahun = $this->DDTahun->SelectedValue;
				if($bulan != '' && $tahun != '')
				{
					$sqlTrans .="AND MONTH(tbt_jurnal_buku_besar.tgl_transaksi) = '$bulan' AND YEAR(tbt_jurnal_buku_besar.tgl_transaksi) = '$tahun' ";
				}
			}
			elseif($periode == '1')
			{
				$tahun = $this->DDTahun->SelectedValue;
				if($tahun != '')
				{
					$sqlTrans .="AND YEAR(tbt_jurnal_buku_besar.tgl_transaksi) = '$tahun' ";
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
					$sqlTrans .="AND tbt_jurnal_buku_besar.tgl_transaksi BETWEEN '$tgl1' AND '$tgl2' ";
				}
			}		
			
			$sqlTrans .= " ORDER BY
							tbt_jurnal_buku_besar.tgl_transaksi  ASC,tbt_jurnal_buku_besar.wkt_transaksi ";
			$arrTrans = $this->queryAction($sqlTrans,'S');
			$tblBody = '';
			var_dump($sqlTrans);
			if($arrTrans)
			{
				$saldo = $ModalAwal;
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$this->ConvertDate($dateForm,'3').'</td>';
				$tblBody .= '<td>Saldo Awal</td>';
				$tblBody .= '<td>'.$this->formatCurrency($ModalAwal,2).'</td>';
				$tblBody .= '<td></td>';
				$tblBody .= '<td>'.$this->formatCurrency($ModalAwal,2).'</td>';			
				$tblBody .= '</tr>';
					
				foreach($arrTrans as $row)
				{
					$jnsTrans = $row['jns_transaksi'];
					
					if($jnsTrans == '0')//Pendapatan
					{
						$saldo +=$row['jml_transaksi'];
						$debet = $this->formatCurrency($row['jml_transaksi'],2);
						$kredit = '';
					}
					else
					{
						$saldo -=$row['jml_transaksi'];
						$debet = '';
						$kredit = $this->formatCurrency($row['jml_transaksi'],2);
					}
					
					$tblBody .= '<tr>';
					$tblBody .= '<td>'.$this->ConvertDate($row['tgl_transaksi'],'3').'</td>';
					$tblBody .= '<td>'.$row['keterangan'].'</td>';
					$tblBody .= '<td>'.$debet.'</td>';
					$tblBody .= '<td>'.$kredit.'</td>';
					$tblBody .= '<td>'.$this->formatCurrency($saldo,2).'</td>';				
					$tblBody .= '</tr>';
				}
			}
			else
			{
				$tblBody = '';
			}
		
		}
		return $tblBody;
	}
	
	public function cariBtnClicked($sender,$param)
	{
		$ModalPenggudangan = ModalTransaksiRecord::finder()->find('st_modal = ?  AND deleted = ?','0','0');
		$ModalPercetakan = ModalTransaksiRecord::finder()->find('st_modal = ?  AND deleted = ?','1','0');
		if($ModalPenggudangan && $ModalPercetakan)
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
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#table-1").dataTable().fnDestroy();
						jQuery("#table-1 tbody").empty();
						jQuery("#table-1 tbody").append("");
						BindGrid();
						jQuery("#modal-2").modal("show");
						unloadContent();
						');
		}
	}
	
	public function prosesBtnClicked($sender,$param)
	{
		$modalGudang = $this->toInt($this->modalGudang->Text);
		$modalPercetakan = $this->toInt($this->modalPercetakan->Text);
		$ModalTransaksiRecord = new ModalTransaksiRecord();
		$ModalTransaksiRecord->st_modal = '0';
		$ModalTransaksiRecord->modal = $modalGudang;
		$ModalTransaksiRecord->save();
		
		$ModalTransaksiRecord = new ModalTransaksiRecord();
		$ModalTransaksiRecord->st_modal = '1';
		$ModalTransaksiRecord->modal = $modalPercetakan;
		$ModalTransaksiRecord->save();
		$this->cariBtnClicked($sender,$param);
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#modal-2").modal("hide");
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
											'1',
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
	
	public function detailClicked($sender,$param)
	{
		$idTrans = $param->CallbackParameter->id;
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
				$tblBody .= '<td>'.$row['jml'].'</td>';
				$tblBody .= '<td>'.$row['harga'].'</td>';
				$tblBody .= '<td>'.$row['total'].'</td>';			
				$tblBody .= '</tr>';
			}
		}
		else
		{
			$tblBody = '';
		}
		
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
