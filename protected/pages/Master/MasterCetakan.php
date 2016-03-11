<?PHP
class MasterCetakan extends MainConf
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
			
			$sqlKateg = "SELECT id,nama FROM tbm_kategori_cetakan WHERE deleted ='0' ";
			$arrKateg = $this->queryAction($sqlKateg,'S');
			
			$this->DDKategCetakan->DataSource = $arrKateg;
			$this->DDKategCetakan->DataBind();
			
			$this->arrBarang->Value = json_encode($arrTemp,true);
			
			$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					');
					
		}
	}
	
	public function barangChanged($sender,$param)
	{
		$idBarang = $param->CallbackParameter->id;
		$index = $param->CallbackParameter->index;
			
		$sqlHarga = "SELECT
							tbm_barang_harga_potongan.id,
							tbm_barang_harga_potongan.ukuran AS text
						FROM
							tbm_barang_harga_potongan
						WHERE
							tbm_barang_harga_potongan.deleted = '0'
						AND tbm_barang_harga_potongan.id_barang = '$idBarang' ";
		$arrHarga = $this->queryAction($sqlHarga,'S');
		
		if($arrHarga)
		{	
			
				$this->getPage()->getClientScript()->registerEndScript
						('','
						BindUkuranData('.$index.','.json_encode($arrHarga,true).');
						');
			//}
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Harga Barang Belum Diset !");
					jQuery("#arrHargaUkuran'.$index.'").val("");
					jQuery("#ukuranBrg'.$index.'").val("");
					jQuery("#hargaBrg'.$index.'").val("");
					var valArr = [];
							valArr.push({
								"id": 0,
								"text": ""
							});
					jQuery("#ukuranBrg'.$index.'").select2({allowClear: true,data: valArr,width: "180px"});
					');	
		}
	}
	
	public function ukuranChanged($sender,$param)
	{
		$ukuran = $param->CallbackParameter->id;
		$index = $param->CallbackParameter->index;
		$Harga = BarangHargaPotonganRecord::finder()->findByPk($ukuran);
		$hargaCurrency = $this->formatCurrency($Harga->harga,2);
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#hargaBrg'.$index.'").val("'.$hargaCurrency.'");
					calculateAll();
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
		$totalBahan = $param->CallbackParameter->totalBahan;
		$totalParam = $param->CallbackParameter->totalParam;
		$totalModal = $param->CallbackParameter->totalModal;
		$totalPersen = $param->CallbackParameter->totalPersen;
		$SubtotalJual = $param->CallbackParameter->SubtotalJual;
		$TotalHargaJual = $param->CallbackParameter->TotalHargaJual;
		
		$jnsTinta = $this->JnsTinta->SelectedValue;
		$JnsLaminasi = $this->JnsLaminasi->SelectedValue;
		$kategCetakan = $this->DDKategCetakan->SelectedValue;
		
		if($this->index->Value == '')
		{
			$MasterCetakanRecord = new MasterCetakanRecord();
		}
		else
		{
			$MasterCetakanRecord = MasterCetakanRecord::finder()->findByPk($this->index->Value);
		}
			$MasterCetakanRecord->nama_cetakan = $nmCetakan;
			$MasterCetakanRecord->kategori_cetakan = $kategCetakan;
			$MasterCetakanRecord->jns_tinta = $jnsTinta;
			$MasterCetakanRecord->hrg_tinta = $hargaTinta;
			$MasterCetakanRecord->jns_laminasi = $JnsLaminasi;
			$MasterCetakanRecord->hrg_laminasi = $hargaLaminasi;
			
			$MasterCetakanRecord->total_bahan = $totalBahan;
			$MasterCetakanRecord->total_param = $totalParam;
			$MasterCetakanRecord->total_modal = $totalModal;
			$MasterCetakanRecord->total_persen = $totalPersen;
			$MasterCetakanRecord->subtotal = $SubtotalJual;
			$MasterCetakanRecord->total_harga_jual = $TotalHargaJual;
			
			if($MasterCetakanRecord->save())
			{
				$idCetakan = $MasterCetakanRecord->id;
				
				$sqlDelete = "DELETE FROM tbm_cetakan_detail_bahan WHERE id_cetakan = '$idCetakan' ";
				$this->queryAction($sqlDelete,'C');
				
				$sqlDelete = "DELETE FROM tbm_cetakan_detail_param WHERE id_cetakan = '$idCetakan' ";
				$this->queryAction($sqlDelete,'C');
				
				if(count($arrBahan) > 0)
				{
					foreach($arrBahan as $rowBahan)
					{
						$MasterCetakanDetailBahanRecord = new MasterCetakanDetailBahanRecord();
						$MasterCetakanDetailBahanRecord->id_cetakan = $idCetakan;
						$MasterCetakanDetailBahanRecord->id_barang = $rowBahan->idBahan;
						$MasterCetakanDetailBahanRecord->ukuran = $rowBahan->ukuranBahan;
						$MasterCetakanDetailBahanRecord->harga = $rowBahan->hargaBahan;
						$MasterCetakanDetailBahanRecord->save();
					}
				}
				
				if(count($arrParam) > 0)
				{
					foreach($arrParam as $rowParam)
					{
						$MasterCetakanDetailParamRecord = new MasterCetakanDetailParamRecord();
						$MasterCetakanDetailParamRecord->id_cetakan = $idCetakan;
						$MasterCetakanDetailParamRecord->parameter = $rowParam->nmParam;
						$MasterCetakanDetailParamRecord->harga = $rowParam->hargaParam;
						$MasterCetakanDetailParamRecord->save();
					}
				}
			}
				
		$tblBody = $this->BindGrid();
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#table-1").dataTable().fnDestroy();
					jQuery("#table-1 tbody").empty();
					jQuery("#table-1 tbody").append("'.$tblBody.'");
					BindGrid();
					clearForm();
					jQuery("#modal-1").modal("hide");
					unloadContent();
					');
	}
	
	public function BindGrid()
	{
		$sql = "SELECT
					tbm_cetakan.id,
					tbm_cetakan.nama_cetakan,
					tbm_cetakan.total_modal,
					tbm_cetakan.total_persen,
					tbm_cetakan.subtotal,
					tbm_cetakan.total_harga_jual
				FROM
					tbm_cetakan
				WHERE
					tbm_cetakan.deleted = '0'
				ORDER BY
					tbm_cetakan.id ASC";
		$arrCetakan = $this->queryAction($sql,'S');
		$tblBody = '';
		if(count(arrCetakan) > 0)
		{
			foreach($arrCetakan as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nama_cetakan'].'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['total_modal'],2).'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['total_persen'],2).'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['subtotal'],2).'</td>';
				$tblBody .= '<td>'.$this->formatCurrency($row['total_harga_jual'],2).'</td>';
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
		$this->index->Value = $id;
		$MasterCetakanRecord = MasterCetakanRecord::finder()->findByPk($id);
		
		$this->index->Value = $id;
		$this->nmCetakan->Text = $MasterCetakanRecord->nama_cetakan;
		$this->DDKategCetakan->SelectedValue = $MasterCetakanRecord->kategori_cetakan;
		$this->JnsTinta->SelectedValue = $MasterCetakanRecord->jns_tinta;
		$this->hrgTinta->Text = $this->formatCurrency($MasterCetakanRecord->hrg_tinta,2);
		$this->JnsLaminasi->SelectedValue = $this->formatCurrency($MasterCetakanRecord->jns_laminasi,2);
		$this->hrgLaminasi->Text = $this->formatCurrency($MasterCetakanRecord->hrg_laminasi,2);
		$this->totalModal->Text = $this->formatCurrency($MasterCetakanRecord->total_modal,2);
		$this->persen->Text = $this->formatCurrency($MasterCetakanRecord->total_persen,2);
		$this->subtotalJual->Text = $this->formatCurrency($MasterCetakanRecord->subtotal,2);
		$this->totalHrgJual->Text = $this->formatCurrency($MasterCetakanRecord->total_harga_jual,2);
		
		$sqlBahan = "SELECT
						tbm_cetakan_detail_bahan.id,
						tbm_cetakan_detail_bahan.id_barang,
						tbm_cetakan_detail_bahan.ukuran,
						tbm_cetakan_detail_bahan.harga
					FROM
						tbm_cetakan_detail_bahan
					WHERE
						tbm_cetakan_detail_bahan.deleted = '0'
						AND tbm_cetakan_detail_bahan.id_cetakan = '$id'
					ORDER BY
						tbm_cetakan_detail_bahan.id ASC";
		$arrBahan = $this->queryAction($sqlBahan,'S');
		if($arrBahan)
		{
			$i = 1;
			foreach($arrBahan as $row)
			{
				$idBahan = $row['id_barang'];
				$sqlHarga = "SELECT
									tbm_barang_harga_potongan.id,
									tbm_barang_harga_potongan.ukuran AS text
								FROM
									tbm_barang_harga_potongan
								WHERE
									tbm_barang_harga_potongan.deleted = '0'
									AND tbm_barang_harga_potongan.id_barang = '$idBahan' ";
				$arrHarga = $this->queryAction($sqlHarga,'S');
				
				
				var_dump($arrHarga);
				$arrObj[] = array("id_barang"=>$row['id_barang'],"ukuran"=>$row['ukuran'],"harga"=>$row['harga'],"arrHarga"=>$arrHarga);
				
			}
			$arrTemp = json_encode($arrObj,true);
		}
		
		$sqlParam = "SELECT
						tbm_cetakan_detail_param.id,
						tbm_cetakan_detail_param.parameter,
						tbm_cetakan_detail_param.harga
					FROM
						tbm_cetakan_detail_param
					WHERE
						tbm_cetakan_detail_param.deleted = '0'
						AND tbm_cetakan_detail_param.id_cetakan = '$id'
					ORDER BY
						tbm_cetakan_detail_param.id ASC";
		$arrParam = $this->queryAction($sqlParam,'S');
		$arrParamTemp = json_encode($arrParam,true);
		$this->getPage()->getClientScript()->registerEndScript
					('','
					BindTempBahan('.$arrTemp.');
					BindTempParam('.$arrParamTemp.');
					jQuery("#modal-1").modal("show");
					unloadContent();
					');
	}
	
	public function deleteData($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$MasterCetakanRecord = MasterCetakanRecord::finder()->findByPk($id);
		$MasterCetakanRecord->deleted = '1';
		$MasterCetakanRecord->save();
		
		$sqlDelete = "UPDATE tbm_cetakan_detail_bahan SET deleted = '1' WHERE id_cetakan = '$id' ";
		$this->queryAction($sqlDelete,'C');
				
		$sqlDelete = "UPDATE tbm_cetakan_detail_param SET deleted = '1' WHERE id_cetakan = '$id' ";
		$this->queryAction($sqlDelete,'C');
		
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
	
	
}
?>
