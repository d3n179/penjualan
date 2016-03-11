<?php
class CetakBillCicilan extends MainConf
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
		$idCicilan = $this->Request['id'];
		$CetakanBayarRecord = CetakanBayarRecord::finder()->findByPk($idCicilan);
		$idTrans = $CetakanBayarRecord->id_transaksi;
		$CetakanRecord = CetakanRecord::finder()->findByPk($idTrans);
		
		$pdf=new reportKwitansiGsm('L','mm','kwt_gsm');
		$pdf->AliasNbPages();
		
		$pdf->SetLeftMargin(10);
		$pdf->SetRightMargin(10);
		$pdf->SetTopMargin(5);
		$pdf->SetLn(5);	
		$pdf->SetAutoPageBreak(true, $bottomMargin - 5 );
		
		
		$pdf->AddPage();	
		$pdf->Ln(5);
		$pdf->SetFont('Courier','B',10);
		$pdf->SetWidths(array(130));
		$pdf->SetAligns(array('C'));
		$PerusahaanRecord = PerusahaanRecord::finder()->findByPk('1');
		$pdf->RowNoBorder(array($PerusahaanRecord->nama));
		$pdf->SetFont('Courier','B',8);
		$pdf->RowNoBorder(array($PerusahaanRecord->alamat.", Telp ".$PerusahaanRecord->telepon.", Email ".$PerusahaanRecord->email));
		$pdf->RowNoBorder(array('...........................................................................'));
		$pdf->SetFont('Courier','',8);
		$pdf->SetWidths(array(40,5,60));
		$pdf->SetAligns(array('L','C','L'));
		$pdf->RowNoBorder(array('Pelanggan',":",$CetakanRecord->nama_pelanggan));
		$pdf->RowNoBorder(array('Tgl Cicilan',":",$this->ConvertDate($CetakanBayarRecord->tgl_bayar,'3')));
		$pdf->RowNoBorder(array('Wkt Cicilan',":",$CetakanBayarRecord->wkt_bayar));
		$sqlCount = "SELECT COUNT(id) as jml FROM tbt_cetakan_bayar WHERE id < '$idCicilan' AND st_dp = '1' AND id_transaksi = '$idTrans' ";
		$arrCount = $this->queryAction($sqlCount,'S');
		$cicilan = $arrCount[0]['jml'] + 1;
		$pdf->RowNoBorder(array('Cicilan Ke',":",$cicilan));
		$pdf->RowNoBorder(array('Jumlah Cicilan',":",$this->formatCurrency($CetakanBayarRecord->jml_bayar,2)));
		$pdf->RowNoBorder(array('Sisa Cicilan',":",$this->formatCurrency($CetakanBayarRecord->sisa_bayar,2)));
		$pdf->SetFont('Courier','B',8);
		$pdf->SetWidths(array(130));
		$pdf->SetAligns(array('C'));
		$pdf->RowNoBorder(array('...........................................................................'));
		$pdf->Output();
	}
}
?>
