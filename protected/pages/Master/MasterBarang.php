<?PHP
class MasterBarang extends MainConf
{

	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$sqlSatuan = "SELECT id,nama FROM tbm_satuan WHERE deleted ='0' ";
			$arrSatuan = $this->queryAction($sqlSatuan,'S');
			$this->DDSatuan->DataSource = $arrSatuan;
			$this->DDSatuan->DataBind();
			
			$this->DDPaket->DataSource = $arrSatuan;
			$this->DDPaket->DataBind();
			
			$sql = "SELECT id,CONCAT(nama,' (',ukuran,')') AS nama FROM tbm_barang WHERE deleted ='0' AND st_barang = '0' ";
			$arrBarang = $this->queryAction($sql,'S');
			$this->DDBarang->DataSource = $arrBarang;
			$this->DDBarang->DataBind();
		}
		
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#table-1 tbody").append("'.$tblBody.'");');	
		}
	}
	
	public function changedJnsbarang()
	{
		$jnsBarang = $this->RBJnsBarang->SelectedValue;
		if($jnsBarang == '0')
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#bannerBarangPanel").hide();
					');	
		}
		elseif($jnsBarang == '1')
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#bannerBarangPanel").show();
					');	
		}
	}
	
	public function BindGrid()
	{
		$sql = "SELECT 
					tbm_barang.id,
					tbm_barang.nama,
					tbm_barang.ukuran,
					tbm_satuan.nama AS satuan,
					tbm_barang.st_barang
				FROM 
					tbm_barang
				INNER JOIN tbm_satuan ON tbm_satuan.id = tbm_barang.satuan 
				WHERE 
					tbm_barang.deleted = '0' 
				ORDER BY 
					tbm_barang.id ASC ";
		$BarangRecord = $this->queryAction($sql,'S');
		
		$count = count($BarangRecord);
		$tblBody = '';
		if($count > 0)
		{
			foreach($BarangRecord as $row)
			{
				$StockBarang = StockBarangRecord::finder()->find('id_barang = ? AND deleted = ?',$row['id'],'0');
				if($StockBarang)
					$stok = $StockBarang->stok;
				else
					$stok = 0;
					
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nama'].'</td>';
				$tblBody .= '<td>'.$row['ukuran'].'</td>';
				$tblBody .= '<td>'.$row['satuan'].'</td>';
				$tblBody .= '<td>'.$stok.'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-default btn-sm btn-icon icon-left\" OnClick=\"editClicked('.$row['id'].')\"><i class=\"entypo-pencil\" ></i>Edit</a>&nbsp;&nbsp;';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteClicked('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>&nbsp;&nbsp;';	
				
				if($row['st_barang'] == '0')
					$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"hargaClicked('.$row['id'].')\"><i class=\"entypo-tag\"></i>Harga Barang</a>&nbsp;&nbsp;';				
				
				$tblBody .=	'</td>';			
				$tblBody .= '</tr>';
			}
		}
		else
		{
			$tblBody = '';
		}
		
		return 	$tblBody;
	}
	
	public function editForm($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$BarangRecord = BarangRecord::finder()->findByPk($id);
		if($BarangRecord)
		{
			$this->modalJudul->Text = 'Edit Barang';
			$this->idBarang->Value = $id;
			$this->RBJnsBarang->SelectedValue = $BarangRecord->st_barang;
			$this->nama->Text = $BarangRecord->nama;
			$this->ukuran->Text = $BarangRecord->ukuran;
			$this->DDSatuan->SelectedValue = $BarangRecord->satuan;
			$show = '.hide()';
			if($BarangRecord->st_barang == '1')
			{
				$show = '.show()';
				$idParent =  $BarangRecord->id;				
				$sqlBanner = "SELECT
									tbm_barang_banner.id_barang AS idBahan,
									tbm_barang.nama AS nmBahan,
									tbm_barang_banner.jml AS jmlBahan
								FROM
									tbm_barang_banner
								INNER JOIN tbm_barang ON tbm_barang.id = tbm_barang_banner.id_barang
								WHERE
									tbm_barang_banner.deleted = '0'
									AND tbm_barang_banner.id_parent_barang = '$idParent' ";
				$arrBahan = $this->queryAction($sqlBanner,'S');
				$this->arrBarang->Value = json_encode($arrBahan,true);
				foreach($arrBahan as $row)
				{
					$tblBody .= '<tr>';
					$tblBody .= '<td>'.$row['nmBahan'].'</td>';
					$tblBody .= '<td>'.$row['jmlBahan'].'</td>';
					$tblBody .= '<td>';
					$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteBahan('.$row['idBahan'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>';	
					$tblBody .=	'</td>';			
					$tblBody .= '</tr>';
				}
				
				$jQueryBahan = '
							jQuery("#tableBrngBahan").dataTable().fnDestroy();
							jQuery("#tableBrngBahan tbody").empty();
							jQuery("#tableBrngBahan tbody").append("'.$tblBody.'");
							BindGridBahan();';
							
				
			}
			else
			{
				$jQueryBahan = '
							jQuery("#tableBrngBahan").dataTable().fnDestroy();
							jQuery("#tableBrngBahan tbody").empty();
							jQuery("#tableBrngBahan tbody").append("");
							BindGridBahan();';
			}
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					jQuery("#bannerBarangPanel")'.$show.';
					'.$jQueryBahan.'
					jQuery("#modal-1").modal("show");
					');	
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					toastr.error("Data Tidak Ditemukan");
					');	
		}
	}
	
	public function deleteData($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$BarangRecord = BarangRecord::finder()->findByPk($id);
		if($BarangRecord)
		{
			$BarangRecord->deleted = '1';
			$BarangRecord->save();
			$tblBody = $this->BindGrid();
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.info("Data Telah Dihapus");
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
					unloadContent();
					toastr.error("Data gagal Dihapus");
					');
		}
	}
	
	public function submitBtnClicked($sender,$param)
	{
		$jnsBarang = $this->RBJnsBarang->SelectedValue;
		$nama = trim($this->nama->Text);
		$ukuran = trim($this->ukuran->Text);
		$satuan = $this->DDSatuan->SelectedValue;
		
		if($jnsBarang == '0')
			$stValid = "1";
		elseif($jnsBarang == '1')
		{
			$arrBarang = json_decode($this->arrBarang->Value,true);
			if(count($arrBarang) > 0)
				$stValid = "1";
			else
			{
				$stValid = "0";
				$msg = "Daftar Bahan Untuk banner belum dimasukkan !";
			}
			
		}
		
		if($stValid == "1")
		{
			if($this->idBarang->Value != '')
			{
				$BarangRecord = BarangRecord::finder()->findByPk($this->idBarang->Value);
				$msg = "Data Berhasil Diedit";
			}
			else
			{
				$BarangRecord = new BarangRecord();
				$msg = "Data Berhasil Disimpan";
			}
			
			$BarangRecord->nama = $nama;
			$BarangRecord->ukuran = $ukuran;
			$BarangRecord->satuan = $satuan;
			$BarangRecord->st_barang = $jnsBarang;
			$BarangRecord->save(); 
			
			$this->nama->Text = '';
			$this->ukuran->Text = '';
			$this->DDSatuan->SelectedValue = 'empty';
			$idParent = $BarangRecord->id;
			
			$sql = "UPDATE tbm_barang_banner SET deleted ='1' WHERE id_parent_barang = '$idParent' ";
			$this->queryAction($sql,'C');
			
			if($jnsBarang == '1')
			{
				foreach($arrBarang as $rowBarang)
				{
					$BarangBannerRecord = BarangBannerRecord::finder()->find('id_parent_barang = ? AND id_barang = ?',$idParent,$rowBarang['idBahan']);
					
					if(!$BarangBannerRecord)
						$BarangBannerRecord = new BarangBannerRecord();
					
					$BarangBannerRecord->id_parent_barang = $idParent;
					$BarangBannerRecord->id_barang = $rowBarang['idBahan'];
					$BarangBannerRecord->jml = $rowBarang['jmlBahan'];
					$BarangBannerRecord->deleted = '0';
					$BarangBannerRecord->save();
				}
			}
			$this->arrBarang->Value = '';
			
			$tblBody = $this->BindGrid();
			
			$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.info("'.$msg.'");
						jQuery("#modal-1").modal("hide");
						jQuery("#table-1").dataTable().fnDestroy();
						jQuery("#table-1 tbody").empty();
						jQuery("#table-1 tbody").append("'.$tblBody.'");
						BindGrid();');	
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
						('','
						toastr.error("'.$msg.'");');	
		}
	}
	
	public function clearForm()
	{
		$this->RBJnsBarang->SelectedValue = '0';
	}
	
	public function changedCB()
	{
		$CBSet = $this->CBhargaset->SelectedValue;
		
		if($CBSet == '0')
		{
			$this->DDPaket->Enabled = false;
			$this->jml->Enabled = false;
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#DDPaketPanel").hide();
					jQuery("#jumlahPanel").hide();');	
		}
		else
		{
			$this->DDPaket->Enabled = true;
			$this->jml->Enabled = true;
			$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#DDPaketPanel").show();
					jQuery("#jumlahPanel").show();');	
		}
	}
	
	public function tambahBahanBtnClicked()
	{
		$arrBahan = json_decode($this->arrBarang->Value,true);
		$idBahan = $this->DDBarang->SelectedValue;
		$jmlBahan = $this->jmlBahan->Text;
		$nmBahan = $this->collectSelectionResultText($this->DDBarang);
		$stFind = '0';
		if(count($arrBahan) > 0)
		{
			foreach($arrBahan as $row)
			{
				if($row['idBahan'] == $idBahan)
					$stFind = '1';
			}
		}
		
		if($stFind == '0')
		{
			$arrBahan[] = array('idBahan'=>$idBahan,'nmBahan'=>$nmBahan,'jmlBahan'=>$jmlBahan);
			$this->arrBarang->Value = json_encode($arrBahan,true);
			$this->BindGridBahan($arrBahan);
			$this->DDBarang->SelectedValue = '';
			$this->jmlBahan->Text = '';
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Bahan Tersebut Sudah Dimasukkan Sebelumnya !");
					');
		}
	}
	
	public function BindGridBahan($arr)
	{
		$tblBody = '';
		if(count($arr) > 0)
		{
			foreach($arr as $row)
			{
				$tblBody .= '<tr>';
				$tblBody .= '<td>'.$row['nmBahan'].'</td>';
				$tblBody .= '<td>'.$row['jmlBahan'].'</td>';
				$tblBody .= '<td>';
				$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteBahan('.$row['idBahan'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>';	
				$tblBody .=	'</td>';			
				$tblBody .= '</tr>';
			}
		}
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#tableBrngBahan").dataTable().fnDestroy();
					jQuery("#tableBrngBahan tbody").empty();
					jQuery("#tableBrngBahan tbody").append("'.$tblBody.'");
					BindGridBahan();');	
					
	}
	
	public function deleteBahan($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arr = json_decode($this->arrBarang->Value,true);
		
		foreach($arr as $subKey => $subArray)
		{
			if($subArray['idBahan'] == $id)
				unset($arr[$subKey]);	
		}
		
		$this->arrBarang->Value = json_encode($arr,true);
		$this->BindGridBahan($arr);
	}
	
	public function setHarga($sender,$param)
	{
		$idBarang = $param->CallbackParameter->id;
		$BarangRecord = BarangRecord::finder()->findByPk($idBarang);
		$this->nmBarangharga->Text = $BarangRecord->nama;
		$this->idSetBarang->Value = $idBarang;
		$this->CBhargaset->SelectedValue = '0';//$BarangRecord->st_harga;
		$this->DDPaket->Enabled = false;
		$this->jml->Enabled = false;
			
		$sqlHarga = "SELECt 
						tbm_barang_harga.id,
						tbm_barang_harga.jml,
						tbm_barang_harga.id_satuan,
						tbm_satuan.nama AS nm_paket,
						tbm_barang_harga.harga
					FROM
						tbm_barang_harga
					LEFT JOIN tbm_satuan ON tbm_satuan.id = tbm_barang_harga.id_satuan
					WHERE 
						tbm_barang_harga.id_barang = '$idBarang' 
						AND tbm_barang_harga.deleted ='0' 
						ORDER BY tbm_barang_harga.jml ASC ";
						
		$arrHarga = $this->queryAction($sqlHarga,'S');
		if($arrHarga)
		{
			foreach($arrHarga as $row)
			{
				if($row['id_satuan'] == '0')
					$nmPaket = 'Eceran';
				else
					$nmPaket = $row['nm_paket'];
					
				$arr[] = array("id"=>$row['id'],"jml"=>$row['jml'],"id_paket"=>$row['id_satuan'],"nm_paket"=>$nmPaket,"harga"=>$row['harga']);
			}
			
			$this->arrHarga->Value = json_encode($arr,true);
			
			$tblBodyharga = '';
			if(count($arr) > 0)
			{
				foreach($arr as $row)
				{
						$tblBodyharga .= '<tr>';
						$tblBodyharga .= '<td>'.$row['jml'].'</td>';
						$tblBodyharga .= '<td>'.$row['nm_paket'].'</td>';
						$tblBodyharga .= '<td>'.$this->formatCurrency($row['harga'],2).'</td>';
						$tblBodyharga .= '<td>';
						$tblBodyharga .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteHarga('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>';	
						$tblBodyharga .= '</td>';			
						$tblBodyharga .= '</tr>';
				}
			}
		}
		
		$sqlHargaPotong = "SELECT
						tbm_barang_harga_potongan.id,
						tbm_barang_harga_potongan.ukuran,
						tbm_barang_harga_potongan.harga
					FROM
						tbm_barang_harga_potongan
					WHERE 
						tbm_barang_harga_potongan.id_barang = '$idBarang' 
						AND tbm_barang_harga_potongan.deleted ='0' ";
						
		$arrHargaPotong = $this->queryAction($sqlHargaPotong,'S');
		if($arrHargaPotong)
		{
			foreach($arrHargaPotong as $row)
			{
				$arrPotong[] = array("id"=>$row['id'],"ukuranPotong"=>$row['ukuran'],"harga"=>$row['harga']);
			}
			
			$this->arrHargaPotong->Value = json_encode($arrPotong,true);
			$tblBodyPotong = '';
			if(count($arr) > 0)
			{
				foreach($arrPotong as $row)
				{
						$tblBodyPotong .= '<tr>';
						$tblBodyPotong .= '<td>'.$row['ukuranPotong'].'</td>';
						$tblBodyPotong .= '<td>'.$this->formatCurrency($row['harga'],2).'</td>';
						$tblBodyPotong .= '<td>';
						$tblBodyPotong .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteHargaPotong('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>';	
						$tblBodyPotong .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"editHargaPotong('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Edit</a>';
					
						$tblBodyPotong .=	'</td>';			
						$tblBodyPotong .= '</tr>';
				}
			}
		}
		
		$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#tableHarga").dataTable().fnDestroy();
						jQuery("#tableHarga tbody").empty();
						jQuery("#tableHarga tbody").append("'.$tblBodyharga.'");
						BindGridHarga();
						jQuery("#tableHargaPerPotong").dataTable().fnDestroy();
						jQuery("#tableHargaPerPotong tbody").empty();
						jQuery("#tableHargaPerPotong tbody").append("'.$tblBodyPotong.'");
						BindGridHargaPotong();');	
						
			
	}
	
	public function tambahBtnClicked($sender,$param)
	{
		$arr = json_decode($this->arrHarga->Value,true);
		$CBSet = $this->CBhargaset->SelectedValue;
		$jml = $this->jml->Text;
		$harga = $this->toInt($this->hargaBrg->Text);
		var_dump($harga);
		if($CBSet == '0')
		{
			$idPaket = 0;
			$nmPaket = "Eceran";
			$jml = 1;
		}
		else
		{
			$idPaket = $this->DDPaket->SelectedValue;
			$nmPaket = SatuanRecord::finder()->findByPk($idPaket)->nama;
		}
		
		$stFind = '0';
		if(count($arr) == 0)
		{
			$id = 1;
		}
		else
		{
			foreach($arr as $row)
			{
				$id = $row['id'] + 1;
				
				if($row['id_paket'] == $idPaket)
					$stFind = '1';
				
			}
		}
		
		if($stFind == '0')
		{
			$arr[] = array("id"=>$id,"jml"=>$jml,"id_paket"=>$idPaket,"nm_paket"=>$nmPaket,"harga"=>$harga);
			
			$this->jml->Text = '';;
			$this->hargaBrg->Text = '';
			$this->arrHarga->Value = json_encode($arr,true);
			$this->bindHarga();
		}
		else
		{
			$this->getPage()->getClientScript()->registerEndScript
					('','
					toastr.error("Harga Dengan Paket Tersebut Sudah Ada !");
					');
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
					$tblBody .= '<td>'.$row['nm_paket'].'</td>';
					$tblBody .= '<td>'.$this->formatCurrency($row['harga'],2).'</td>';
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
	
	public function tambahPotongClicked($sender,$param)
	{
		$arr = json_decode($this->arrHargaPotong->Value,true);
		$ukuranPotong = $this->ukuranPotong->Text;
		$harga = $this->toInt($this->hargaPotong->Text);
		$idEdit = $this->idHrgPotong->Value;
		
		if($idEdit != '')
		{
			foreach($arr as $subkey => $subArray)
			{
				if($subArray['id'] == $idEdit)
				{
					$arr[$subkey]['ukuranPotong'] = $ukuranPotong;
					$arr[$subkey]['harga'] = $harga;
				}
			}
				
		}
		else
		{
			
		
			if(count($arr) == 0)
			{
				$id = 1;
			}
			else
			{
				foreach($arr as $row)
				{
					$id = $row['id'] + 1;
				}
			}
			
				$arr[] = array("id"=>$id,"ukuranPotong"=>$ukuranPotong,"harga"=>$harga);
		}	
			$this->idHrgPotong->Value = '';
			$this->ukuranPotong->Text = '';;
			$this->hargaPotong->Text = '';
			$this->arrHargaPotong->Value = json_encode($arr,true);
			$this->bindHargaPotong();
	}
	
	public function importClicked($sender,$param)
	{
		$arr = json_decode($this->arrHargaPotong->Value,true);
		
		$sqlBarang = "SELECT  `id` 
						FROM  `tbm_barang` 
						WHERE deleted =  '0'
						AND st_barang =  '0' 
						AND id != '44'";
		$arrBarang = $this->queryAction($sqlBarang,'S');
		foreach($arrBarang as $row)
		{
			$idBarang = $row['id'];
			foreach($arr as $rowHarga)
			{
				$hargaRecord = new BarangHargaPotonganRecord();
				$hargaRecord->id_barang = $idBarang;
				$hargaRecord->ukuran = $rowHarga['ukuranPotong'];
				$hargaRecord->harga = $rowHarga['harga'];
				$hargaRecord->save();
			}
		}
		
		
		//$arr[] = array("id"=>$id,"ukuranPotong"=>$ukuranPotong,"harga"=>$harga);
			
	}
	
	public function deleteHargaPotong($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arr = json_decode($this->arrHargaPotong->Value,true);
		
		foreach($arr as $subKey => $subArray)
		{
			if($subArray['id'] == $id)
				unset($arr[$subKey]);	
		}
		
		$this->arrHargaPotong->Value = json_encode($arr,true);
		$this->bindHargaPotong();
	}
	
	public function editHargaPotong($sender,$param)
	{
		$id = $param->CallbackParameter->id;
		$arr = json_decode($this->arrHargaPotong->Value,true);
		foreach($arr as $row)
		{
			if($row['id'] == $id)
			{
				$hrg = $row['harga'];
				$ukuran = $row['ukuranPotong'];
			}
		}
		
		$this->idHrgPotong->Value = $id;
		$this->hargaPotong->Text = $this->formatCurrency($hrg,2);
		$this->ukuranPotong->Text = $ukuran;
		
		
	}
	
	public function bindHargaPotong()
	{
		$arr = json_decode($this->arrHargaPotong->Value,true);
		
		$tblBody = '';
		if(count($arr) > 0)
		{
			foreach($arr as $row)
			{
					$tblBody .= '<tr>';
					$tblBody .= '<td>'.$row['ukuranPotong'].'</td>';
					$tblBody .= '<td>'.$this->formatCurrency($row['harga'],2).'</td>';
					$tblBody .= '<td>';
					$tblBody .= '<a href=\"#\" class=\"btn btn-danger btn-sm btn-icon icon-left\" OnClick=\"deleteHargaPotong('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Hapus</a>';	
					$tblBody .= '<a href=\"#\" class=\"btn btn-gold btn-sm btn-icon icon-left\" OnClick=\"editHargaPotong('.$row['id'].')\"><i class=\"entypo-cancel\"></i>Edit</a>';
					$tblBody .=	'</td>';			
					$tblBody .= '</tr>';
			}
		}
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					jQuery("#tableHargaPerPotong").dataTable().fnDestroy();
					jQuery("#tableHargaPerPotong tbody").empty();
					jQuery("#tableHargaPerPotong tbody").append("'.$tblBody.'");
					BindGridHargaPotong();');	
	}
	
	public function submitHargaBtnClicked($sender,$param)
	{
		$idBarang = $this->idSetBarang->Value;
		$arr = json_decode($this->arrHarga->Value,true);
		$stHarga = $this->CBhargaset->SelectedValue; 
		$arrPotong = json_decode($this->arrHargaPotong->Value,true);	
		if(count($arr) > 0  || count($arrPotong) > 0 )
		{
			if(count($arr) > 0)
			{
				$sqlDelete = "DELETE FROM tbm_barang_harga WHERE id_barang = '$idBarang' ";
				$this->queryAction($sqlDelete,'C');
				
				foreach($arr as $row)
				{
					$hargaRecord = new BarangHargaRecord();
					$hargaRecord->id_barang = $idBarang;
					$hargaRecord->jml = $row['jml'];
					$hargaRecord->harga = $row['harga'];
					$hargaRecord->id_satuan = $row['id_paket'];
					$hargaRecord->save();
				}
				$this->arrHarga->Value = '';
				
			}
			
			if(count($arrPotong) > 0)
			{
				$sqlDelete = "DELETE FROM tbm_barang_harga_potongan WHERE id_barang = '$idBarang' ";
				$this->queryAction($sqlDelete,'C');
				
				foreach($arrPotong as $row)
				{
					$hargaRecord = new BarangHargaPotonganRecord();
					$hargaRecord->id_barang = $idBarang;
					$hargaRecord->ukuran = $row['ukuranPotong'];
					$hargaRecord->harga = $row['harga'];
					$hargaRecord->save();
				}
				$this->arrHargaPotong->Value = '';
			}
			
			$this->getPage()->getClientScript()->registerEndScript
						('','
						jQuery("#tableHarga").dataTable().fnDestroy();
						jQuery("#tableHarga tbody").empty();
						jQuery("#tableHarga tbody").append("");
						jQuery("#tableHargaPerPotong").dataTable().fnDestroy();
						jQuery("#tableHargaPerPotong tbody").empty();
						jQuery("#tableHargaPerPotong tbody").append("");
						BindGridHarga();
						BindGridHargaPotong();
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
