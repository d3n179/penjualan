<?php
class CetakBillPembelian extends MainConf
{
	public function onInit($param)
	{		
		parent::onInit($param);
	}		 
	
	public function onPreInit ($param)
	{
		parent::onPreInit($param);
	}
	
	public function onLoad($param)
	{	
		$idTrans = $this->Request['id'];
		$pdf=new reportKwitansiGsm('L','mm','kwt_rsp');
		$pdf->AliasNbPages();
		
		$pdf->SetLeftMargin(10);
		$pdf->SetRightMargin(10);
		$pdf->SetTopMargin(5);
		$pdf->SetLn(5);	
		$pdf->SetAutoPageBreak(true, $bottomMargin - 5 );
		
		
		$pdf->AddPage();	
		$pdf->Ln(5);
		$pdf->SetFont('Courier','B',11);
		$pdf->SetWidths(array(190));
		$pdf->SetAligns(array('C'));
		$PerusahaanRecord = PerusahaanRecord::finder()->findByPk('1');
		$pdf->RowNoBorder(array($PerusahaanRecord->nama));
		$pdf->SetFont('Courier','B',8);
		$pdf->RowNoBorder(array($PerusahaanRecord->alamat.", Telp ".$PerusahaanRecord->telepon.", Email ".$PerusahaanRecord->email));
		$pdf->SetFont('Courier','B',11);
		$pdf->RowNoBorder(array('................................................................................'));
		
		$PembelianRecord = PembelianRecord::finder()->findByPk($idTrans);
		$PemasokRecord = PemasokRecord::finder()->findByPk($PembelianRecord->id_pemasok);
		$pdf->SetFont('Courier','',9);
		$pdf->SetWidths(array(30,10,60));
		$pdf->SetAligns(array('L','C','L'));
		$pdf->RowNoBorder(array('Pemasok',':',$PemasokRecord->nama));
		$pdf->RowNoBorder(array('Tgl Transaksi',':',$this->ConvertDate($PembelianRecord->tgl_transaksi,'3')));
		$pdf->RowNoBorder(array('Wkt Transaksi',':',$PembelianRecord->wkt_transaksi));
		
		$pdf->SetFont('Courier','B',11);
		$pdf->SetWidths(array(190));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('................................................................................'));
		
		$pdf->SetFont('Courier','B',10);
		$pdf->SetWidths(array(10,60,40,40,40));
		$pdf->SetAligns(array('C','C','C','C','C'));
		$pdf->RowNoBorder(array('No','Barang','Jumlah','Harga','Subtotal'))
		;
		$pdf->SetFont('Courier','B',11);
		$pdf->SetWidths(array(190));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('................................................................................'));
		
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
		
		$pdf->SetFont('Courier','',9);
		$pdf->SetWidths(array(10,60,40,40,40));
		$pdf->SetAligns(array('L','L','C','R','R'));
		
		$no = 1;
		$grandTotal = 0;
		foreach($arrDetail as $row)
		{
			$pdf->RowNoBorder(array($no,$row['barang'],number_format($row['jml'],2,'.','.,'),$this->formatCurrency($row['harga'],2),$this->formatCurrency($row['total'],2)));
			$no++;
			$grandTotal += $row['total']; 
		}
		$pdf->SetFont('Courier','B',11);
		$pdf->SetWidths(array(190));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('................................................................................'));
		
		$pdf->SetFont('Courier','B',10);
		$pdf->SetWidths(array(10,60,40,40,40));
		$pdf->SetAligns(array('C','C','C','C','R'));
		$pdf->RowNoBorder(array('','','','',$this->formatCurrency($grandTotal,2)));
		
		$pdf->SetFont('Courier','B',11);
		$pdf->SetWidths(array(190));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('................................................................................'));
		$pdf->Output();
	}
}
?>
