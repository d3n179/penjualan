<?php
class cetakKartuStokXls extends XlsGen
{
	public function onLoad($param)
	{		
		$periode = $this->Request['periode'];
		$bln = $this->Request['bln'];
		$thn =$this->Request['thn'];
		
		if($bln == '1')
			$nmBulan = "Januari";
		elseif($bln == '2')
			$nmBulan = "Februari";
		elseif($bln == '3')
			$nmBulan = "Maret";
		elseif($bln == '4')
			$nmBulan = "April";
		elseif($bln == '5')
			$nmBulan = "Mei";
		elseif($bln == '6')
			$nmBulan = "Juni";
		elseif($bln == '7')
			$nmBulan = "Juli";
		elseif($bln == '8')
			$nmBulan = "Agustus";
		elseif($bln == '9')
			$nmBulan = "September";
		elseif($bln == '10')
			$nmBulan = "Oktober";
		elseif($bln == '11')
			$nmBulan = "Novemver";
		elseif($bln == '12')
			$nmBulan = "Desember";
		
		$thnBln = $thn."-".$bln."-";	
		
		$file = 'KartuStok.xls';
		
		//http headers	
		$this->HeaderingExcel($file);
		
		//membuat workbook
		$workbook=new Workbook("-");
		
		//membuat worksheet pertama
		$worksheet1= & $workbook->add_worksheet('Kartu Stok');
		
		$baris=0;
		$kolom=0;
		
		//set lebar tiap kolom
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','30',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','15',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','8',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','15',$baris,$kolom); $baris++; $kolom++;
		$this->AddWS($worksheet1,'c','15',$baris,$kolom); $baris++; $kolom++;
		
		$frmtTitleLeft =  & $workbook->add_format();
		$left= $this->AddFormat($frmtTitleLeft,'b','1','12');
		$left= $this->AddFormat($frmtTitleLeft,'bd','0','');
		$left= $this->AddFormat($frmtTitleLeft,'HA','left','');
		
		$frmtTitleLeft10 =  & $workbook->add_format();
		$left= $this->AddFormat($frmtTitleLeft10,'b','1','10');
		$left= $this->AddFormat($frmtTitleLeft10,'bd','0','');
		$left= $this->AddFormat($frmtTitleLeft10,'HA','left','');
		
		$frmtLeft =  & $workbook->add_format();
		$left= $this->AddFormat($frmtLeft,'b','1','10');
		$left= $this->AddFormat($frmtLeft,'bd','0','');
		$left= $this->AddFormat($frmtLeft,'HA','left','');		
		
		$frmtCenter =  & $workbook->add_format();
		$center= $this->AddFormat($frmtCenter,'b','1','10');
		$center= $this->AddFormat($frmtCenter,'bd','1','');
		$center= $this->AddFormat($frmtCenter,'HA','center','');
		
		$frmtWrap =  & $workbook->add_format();
		$wrap= $this->AddFormat($frmtWrap,'b','1','10');
		$wrap= $this->AddFormat($frmtWrap,'bd','1','');
		$wrap= $this->AddFormat($frmtWrap,'HA','center','');
		$wrap= $this->AddFormat($frmtWrap,'WR','1','');
		
		
		$baris=0;
		$kolom=0;
		$worksheet1->write_string($baris,0,'KARTU STOK' ,$frmtTitleLeft);
			
		$baris++;
		$worksheet1->write_string($baris,0,"Periode : ".$nmBulan." ".$thn ,$frmtTitleLeft10);
		$baris++;
		
		$frmtLeft =  & $workbook->add_format();
		$left= $this->AddFormat($frmtLeft,'','1','10');
		$left= $this->AddFormat($frmtLeft,'bd','1','');
		$left= $this->AddFormat($frmtLeft,'HA','left','');
		$left= $this->AddFormat($frmtLeft,'WR','1','');
		
		$frmtCenterHeader =  & $workbook->add_format();
		$centerHeader= $this->AddFormat($frmtCenterHeader,'b','1','10');
		$centerHeader= $this->AddFormat($frmtCenterHeader,'bd','1','');
		$centerHeader= $this->AddFormat($frmtCenterHeader,'HA','center','');
		$centerHeader= $this->AddFormat($frmtCenterHeader,'WR','1','');
		
		$frmtCenter =  & $workbook->add_format();
		$center= $this->AddFormat($frmtCenter,'','1','10');
		$center= $this->AddFormat($frmtCenter,'bd','1','');
		$center= $this->AddFormat($frmtCenter,'HA','center','');
		$center= $this->AddFormat($frmtCenter,'WR','1','');
		
		$frmtRight =  & $workbook->add_format();
		$right= $this->AddFormat($frmtRight,'','1','10');
		$right= $this->AddFormat($frmtRight,'bd','1','');
		$right= $this->AddFormat($frmtRight,'HA','right','');
		
		$baris++;
		$kolom = 0;
		//$worksheet1->set_row(6, 150,0); //set tinngi row ke-7
			
		$worksheet1->write_string($baris,$kolom,"NO",$centerHeader);	
		$worksheet1->merge_cells($baris, $kolom, $baris+1, $kolom);$kolom++;
		$worksheet1->write_string($baris,$kolom,"ITEM",$centerHeader);	
		$worksheet1->merge_cells($baris, $kolom, $baris+1, $kolom);$kolom++;
		$worksheet1->write_string($baris,$kolom,"HARGA BELI",$centerHeader);	
		$worksheet1->merge_cells($baris, $kolom, $baris+1, $kolom);$kolom++;
		$worksheet1->write_string($baris,$kolom,"TGL",$centerHeader);	
		$worksheet1->merge_cells($baris, $kolom, $baris+1, $kolom);$kolom++;
		$worksheet1->write_string($baris,$kolom,"ATK",$centerHeader);
		$worksheet1->merge_cells($baris, $kolom, $baris, $kolom + 30);
		$kolom = $kolom + 31;
		$worksheet1->write_string($baris,$kolom,"TTL",$centerHeader);	
		$worksheet1->merge_cells($baris, $kolom, $baris + 1, $kolom);$kolom++;
		$worksheet1->write_string($baris,$kolom,"LABA",$centerHeader);	
		$worksheet1->merge_cells($baris, $kolom, $baris + 1, $kolom);$kolom++;
		
		$baris++;
		$kolom = 0;
		$kolom = $kolom + 4;
		$i = 1;
		while($i <= 31)
		{
			$worksheet1->write_string($baris,$kolom,$i,$centerHeader);$kolom++;
			$i++;
		}
		$baris++;
		$kolom = 0;
		$sqlBarang = "SELECT id,CONCAT(nama,' (',ukuran,')') AS nama FROM tbm_barang WHERE deleted ='0' AND st_barang = '0' ";
		$arrBarang = $this->queryAction($sqlBarang,'S');
		$no = 1;
		foreach($arrBarang as $row)
		{
			$ttlStokAwal = 0;
			$ttlStokIn = 0;
			$ttlStokOut = 0;
			$ttlStokRusak = 0;
			$ttlStokAkhir = 0;
			
			$idBarang = $row['id'];
			$sqlHarga = "SELECT
								MAX(tbt_pembelian_detail.id) AS id
							FROM
								tbt_pembelian_detail
							INNER JOIN tbt_pembelian ON tbt_pembelian.id = tbt_pembelian_detail.id_transaksi
							WHERE
								tbt_pembelian_detail.id_barang = '$idBarang' ";
							//AND MONTH(tbt_pembelian.tgl_transaksi) = '$bln' AND YEAR(tbt_pembelian.tgl_transaksi) = '$thn'";
			$arrHarga = $this->queryAction($sqlHarga,'S');
			$idMaxHarga = $arrHarga[0]['id'];
			$hargaBeli = PembelianDetailRecord::finder()->findByPk($idMaxHarga)->harga;
			
			$worksheet1->write_string($baris,$kolom,$no,$frmtCenter);$kolom++;
			$worksheet1->write_string($baris,$kolom,$row['nama'],$frmtCenter);$kolom++;
			$worksheet1->write_string($baris,$kolom,$this->formatCurrency($hargaBeli,2),$frmtCenter);
			$worksheet1->write_string($baris+1,$kolom,"",$frmtCenter);
			$worksheet1->write_string($baris+2,$kolom,"",$frmtCenter);
			$worksheet1->write_string($baris+3,$kolom,"",$frmtCenter);
			$worksheet1->write_string($baris+4,$kolom,"",$frmtCenter);$kolom++;
			$worksheet1->write_string($baris,$kolom,"0",$frmtCenter);
			$worksheet1->write_string($baris+1,$kolom,"IN",$frmtCenter);
			$worksheet1->write_string($baris+2,$kolom,"OUT",$frmtCenter);
			$worksheet1->write_string($baris+3,$kolom,"L S/M",$frmtCenter);
			$worksheet1->write_string($baris+4,$kolom,"END",$frmtCenter);$kolom++;
			
			$i = 1;
			while($i <= 31)
			{
				$tgl = $thnBln.$i;
				$idBarang = $row['id'];
				$StockInOutRecord = StockInOutRecord::finder()->find('id_barang = ? AND tgl = ? AND deleted = ? ORDER BY id ASC LIMIT 1',$idBarang,$tgl,'0');
				$worksheet1->write_number($baris,$kolom,$StockInOutRecord->stok_awal,$frmtCenter);$kolom++;
				$ttlStokAwal = $ttlStokAwal + $StockInOutRecord->stok_awal;
				$i++;
			}
			$worksheet1->write_number($baris,$kolom,$ttlStokAwal,$frmtCenter);$kolom++;
			$baris++;
			$kolom = 0;
			$kolom = $kolom + 4;
			$i = 1;
			while($i <= 31)
			{
				$tgl = $thnBln.$i;
				$idBarang = $row['id'];
				$sqlStokIn = "SELECT
								SUM(tbt_stok_in_out.stok_in) AS stok_in
							FROM
								tbt_stok_in_out 
							WHERE 
								tbt_stok_in_out.tgl = '$tgl' 
								AND tbt_stok_in_out.deleted = '0' 
								AND tbt_stok_in_out.id_barang = '$idBarang' 
								AND tbt_stok_in_out.jns_transaksi = '1' 
							GROUP BY tbt_stok_in_out.id_barang ";
				$arrStokIn = $this->queryAction($sqlStokIn,'S');
				$stokIn = $arrStokIn[0]['stok_in'];
				
				$worksheet1->write_number($baris,$kolom,$stokIn,$frmtCenter);$kolom++;
				$ttlStokIn = $ttlStokIn + $stokIn;
				
				 
				$i++;
			}
			$worksheet1->write_number($baris,$kolom,$ttlStokIn,$frmtCenter);$kolom++;
			$baris++;
			$kolom = 0;
			$kolom = $kolom + 4;
			$i = 1;
			while($i <= 31)
			{
				$tgl = $thnBln.$i;
				$idBarang = $row['id'];
				$sqlStokOut = "SELECT
								SUM(tbt_stok_in_out.stok_out) AS stok_out
							FROM
								tbt_stok_in_out 
							WHERE 
								tbt_stok_in_out.tgl = '$tgl' 
								AND tbt_stok_in_out.deleted = '0' 
								AND tbt_stok_in_out.id_barang = '$idBarang' 
								AND tbt_stok_in_out.jns_transaksi = '0' 
							GROUP BY tbt_stok_in_out.id_barang ";
				$arrStokOut = $this->queryAction($sqlStokOut,'S');
				$stokOut = $arrStokOut[0]['stok_out'];
				
				$worksheet1->write_number($baris,$kolom,$stokOut,$frmtCenter);$kolom++;
				$ttlStokOut = $ttlStokOut + $stokOut;
				
				 
				$i++;
			}
			$worksheet1->write_number($baris,$kolom,$ttlStokOut,$frmtCenter);$kolom++;
			$baris++;
			$kolom = 0;
			$kolom = $kolom + 4;
			$i = 1;
			while($i <= 31)
			{
				$tgl = $thnBln.$i;
				$idBarang = $row['id'];
				$sqlStokRusak = "SELECT
								SUM(tbt_stok_in_out.stok_out) AS stok_rusak
							FROM
								tbt_stok_in_out 
							WHERE 
								tbt_stok_in_out.tgl = '$tgl' 
								AND tbt_stok_in_out.deleted = '0' 
								AND tbt_stok_in_out.id_barang = '$idBarang' 
								AND tbt_stok_in_out.jns_transaksi = '2' 
							GROUP BY tbt_stok_in_out.id_barang ";
				$arrStokRusak = $this->queryAction($sqlStokRusak,'S');
				$stokRusak = $arrStokRusak[0]['stok_rusak'];
				
				$worksheet1->write_number($baris,$kolom,$stokRusak,$frmtCenter);$kolom++;
				$ttlStokRusak = $ttlStokRusak + $stokRusak;
				
				 
				$i++;
			}
			$worksheet1->write_number($baris,$kolom,$ttlStokRusak,$frmtCenter);$kolom++;
			$baris++;
			$kolom = 0;
			$kolom = $kolom + 4;
			$i = 1;
			while($i <= 31)
			{
				$tgl = $thnBln.$i;
				$idBarang = $row['id'];
				$StockInOutRecord = StockInOutRecord::finder()->find('id_barang = ? AND tgl = ? AND deleted = ? ORDER BY id DESC LIMIT 1',$idBarang,$tgl,'0');
				$worksheet1->write_number($baris,$kolom,$StockInOutRecord->stok_akhir,$frmtCenter);$kolom++;
				 $ttlStokAkhir = $ttlStokAkhir + $StockInOutRecord->stok_akhir;
				$i++;
			}
			$totalOut = ($ttlStokRusak + $ttlStokOut) * $hargaBeli;
			$worksheet1->write_number($baris,$kolom,$ttlStokAkhir,$frmtCenter);$kolom++;
			$worksheet1->write_string($baris - 4,$kolom,$this->formatCurrency($totalOut,2),$frmtCenter);$kolom++;
			$no++;
			$baris++;
			$kolom = 0;
		}
		$workbook->close(); 
	}
	
	
}
?>
