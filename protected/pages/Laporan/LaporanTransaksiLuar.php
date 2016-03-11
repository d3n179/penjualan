<?PHP
class LaporanTransaksiLuar extends MainConf
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
						tbt_transaksi_luar.id,
						tbt_transaksi_luar.sumber_transaksi,
						tbt_transaksi_luar.jns_transaksi,
						tbt_transaksi_luar.tgl_transaksi,
						tbt_transaksi_luar.wkt_transaksi,
						tbt_transaksi_luar.keterangan,
						tbt_transaksi_luar.jml_transaksi,
						tbt_transaksi_luar.st_posting
					FROM 
						tbt_transaksi_luar
					WHERE
						tbt_transaksi_luar.deleted ='0' ";
		if($periode == '0')
		{
			$bulan = $this->DDBulan->SelectedValue;
			$tahun = $this->DDTahun->SelectedValue;
			if($bulan != '' && $tahun != '')
			{
				$sqlTrans .="AND MONTH(tbt_transaksi_luar.tgl_transaksi) = '$bulan' AND YEAR(tbt_transaksi_luar.tgl_transaksi) = '$tahun' ";
			}
		}
		elseif($periode == '1')
		{
			$tahun = $this->DDTahun->SelectedValue;
			if($tahun != '')
			{
				$sqlTrans .="AND YEAR(tbt_transaksi_luar.tgl_transaksi) = '$tahun' ";
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
				$sqlTrans .="AND tbt_transaksi_luar.tgl_transaksi BETWEEN '$tgl1' AND '$tgl2' ";
			}
		}		
		
		$arrTrans = $this->queryAction($sqlTrans,'S');
		$tblBody = '';
		if($arrTrans)
		{
			foreach($arrTrans as $row)
			{
				if($row['sumber_transaksi'] == '0')
					$sumberTrans = "Penggudangan";
				else
					$sumberTrans = "Percetakan";
				
				if($row['jns_transaksi'] == '0')
					$jnsTrans = "Pendapatan";
				else
					$jnsTrans = "Pengeluaran";
						
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['tgl_transaksi'].'</td>';
				$tblBody .= '<td>'.$row['wkt_transaksi'].'</td>';
				$tblBody .= '<td>'.$sumberTrans.'</td>';
				$tblBody .= '<td>'.$jnsTrans.'</td>';
				$tblBody .= '<td>'.$row['keterangan'].'</td>';
				$tblBody .= '<td><input id=\"jmlTrans'.$row['id'].'\" class=\"form-control autoJml\" type=\"text\" value=\"'.$row['jml_transaksi'].'\" ></td>';
				$tblBody .= '<td>';	
				
				if($row['st_posting'] =='0')	
					$tblBody .= '<a href=\"#\" class=\"btn btn-primary btn-sm btn-icon icon-left\" OnClick=\"postingClicked('.$row['id'].')\"><i class=\"entypo-doc-text-inv\"></i>Posting</a>&nbsp;&nbsp;';	
				
				if($this->User->IsUserGroup == '1')
				{	
					$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Simpan</a>&nbsp;&nbsp;';	
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
		$TransaksiLuarRecord = TransaksiLuarRecord::finder()->findByPk($idTrans);
		$TransaksiLuarRecord->st_posting = '1';
		$TransaksiLuarRecord->save();
			
			$this->InsertJurnalBukuBesar(
											$TransaksiLuarRecord->id,
											$TransaksiLuarRecord->sumber_transaksi,
											$TransaksiLuarRecord->jns_transaksi,
											$TransaksiLuarRecord->tgl_transaksi,
											$TransaksiLuarRecord->wkt_transaksi,
											$TransaksiLuarRecord->keterangan,
											$TransaksiLuarRecord->jml_transaksi
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
	
	
	
	public function simpanClicked($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$newHarga = $param->CallbackParameter->harga;
		
		$TransaksiLuarRecord = TransaksiLuarRecord::finder()->findByPk($id);
		$TransaksiLuarRecord->jml_transaksi = $newHarga;
		$TransaksiLuarRecord->save();

		$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$id,$TransaksiLuarRecord->sumber_transaksi,$TransaksiLuarRecord->jns_transaksi,$TransaksiLuarRecord->keterangan);
		if($JurnalBukuBesarRecord)
		{
			$JurnalBukuBesarRecord->jml_transaksi = $newHarga;
			$JurnalBukuBesarRecord->save();
		}	
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Data Telah Dirubah ");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');	
					
	}
	
	function hapusClicked($sender,$param)
	{
		$id = $param->CallBackParameter->id;
		$TransaksiLuarRecord = TransaksiLuarRecord::finder()->findByPk($id);
		$TransaksiLuarRecord->deleted = '1';
		$TransaksiLuarRecord->save();

		$JurnalBukuBesarRecord = JurnalBukuBesarRecord::finder()->find('id_transaksi = ? AND sumber_transaksi = ? AND jns_transaksi = ? AND keterangan = ?',$id,$TransaksiLuarRecord->sumber_transaksi,$TransaksiLuarRecord->jns_transaksi,$TransaksiLuarRecord->keterangan);
		if($JurnalBukuBesarRecord)
		{
			$JurnalBukuBesarRecord->deleted = '1';
			$JurnalBukuBesarRecord->save();
		}		
		
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Data Telah Dihapus ");
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					unloadContent();
					');	
		
	}
	
}
?>
