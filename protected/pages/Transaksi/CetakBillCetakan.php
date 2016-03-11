<?php
class CetakBillCetakan extends MainConf
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
		$pdf->SetWidths(array(200));
		$pdf->SetAligns(array('C'));
		$PerusahaanRecord = PerusahaanRecord::finder()->findByPk('1');
		$pdf->RowNoBorder(array($PerusahaanRecord->nama));
		$pdf->SetFont('Courier','B',8);
		$pdf->RowNoBorder(array($PerusahaanRecord->alamat.", Telp ".$PerusahaanRecord->telepon.", Email ".$PerusahaanRecord->email));
		$pdf->RowNoBorder(array('....................................................................................................................'));
		
		$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
		$pdf->SetFont('Courier','',8);
		$pdf->SetWidths(array(30,10,60));
		$pdf->SetAligns(array('L','C','L'));
		$pdf->RowNoBorder(array('Pelanggan',':',$CetakanRecord->nama_pelanggan));
		$pdf->RowNoBorder(array('Tgl Transaksi',':',$this->ConvertDate($CetakanRecord->tgl_transaksi,'3')));
		$pdf->RowNoBorder(array('Wkt Transaksi',':',$CetakanRecord->wkt_transaksi));
		
		$pdf->SetFont('Courier','B',8);
		$pdf->SetWidths(array(200));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('....................................................................................................................'));
		
		$pdf->SetFont('Courier','B',8);
		$pdf->SetWidths(array(10,30,25,25,25,25,25,35));
		$pdf->SetAligns(array('C','C','C','C','C','C','C','C'));
		$pdf->RowNoBorder(array('No','Nama Cetakan','Harga Cetakan','Jumlah Pesanan','Estimasi Hari','Tuntutan Hari','Lembur','Subtotal'))
		;
		$pdf->SetFont('Courier','B',8);
		$pdf->SetWidths(array(200));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('....................................................................................................................'));
		
		$sqlDetail = "SELECT
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
		
		$pdf->SetFont('Courier','',8);
		$pdf->SetWidths(array(10,30,25,25,25,25,25,35));
		$pdf->SetAligns(array('C','C','R','C','C','C','C','R'));
		
		$no = 1;
		$grandTotal = 0;
		foreach($arrDetail as $row)
		{
			$pdf->RowNoBorder(array($no,$row['nm_cetakan'],$this->formatCurrency($row['hrg_cetakan'],2),$row['jumlah_pesanan'],$row['est_hari'],$row['tt_hari'],$row['lembur'].' %',$this->formatCurrency($row['subtotal'],2)));
			$no++;
			$grandTotal += $row['subtotal']; 
		}
		$pdf->SetFont('Courier','B',8);
		$pdf->SetWidths(array(200));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('....................................................................................................................'));
		
		$pdf->SetFont('Courier','B',8);
		$pdf->SetWidths(array(10,30,25,25,25,25,25,35));
		$pdf->SetAligns(array('C','C','C','C','C','C','C','R'));
		$pdf->RowNoBorder(array('','','','','','','Subtotal',$this->formatCurrency($grandTotal,2)));
		$pdf->RowNoBorder(array('','','','','','','Diskon',$this->formatCurrency($CetakanRecord->diskon,2)));
		$pdf->RowNoBorder(array('','','','','','','Total',$this->formatCurrency($CetakanRecord->total,2)));
		$CetakanBayarRecord = CetakanBayarRecord::finder()->find('id_transaksi = ? AND st_dp = ?',$CetakanRecord->id,'0');
		
		if($CetakanBayarRecord)
		{
			$pdf->RowNoBorder(array('','','','','','','DP',$this->formatCurrency($CetakanBayarRecord->jml_bayar,2)));
			$pdf->RowNoBorder(array('','','','','','','Sisa Bayar',$this->formatCurrency($CetakanBayarRecord->sisa_bayar,2)));
		}
		
		$pdf->SetFont('Courier','B',8);
		$pdf->SetWidths(array(200));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('....................................................................................................................'));
		$pdf->Output();
	}
}
?>
