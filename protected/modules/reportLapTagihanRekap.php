<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportLapTagihanRekap extends reportJurnal
{	
	
	var $judul;
	var $namaRs;
	var $kotaRs;
	var $kelompokPenjaminNama;
	var $perusAsuransiNama;
	var $periode;
	
	//Page header	
	function Header()
	{
		$this->SetFont('Arial','B',10);
		
		$this->SetLn(5);
		$this->SetWidths(array(200));
		$this->SetAligns(array('L'));
		$this->SetFontWeight(array(''));
		$this->SetBorderColumn(array('0'));
		$this->RowBorder(array($this->namaRs),'1','1');
		
		$this->SetFont('Arial','B',11);
		$this->Ln(2);
		$this->SetAligns(array('C'));
		$this->SetFontWeight(array('B'));
		$this->RowBorder(array($this->judul),'1','1');
		
		$this->SetFont('Arial','B',10);
		$this->SetFontWeight(array(''));
		$this->RowBorder(array(strtoupper($this->kelompokPenjaminNama.' / '.$this->perusAsuransiNama)),'1','1');
		$this->RowBorder(array(ucwords($this->periode)),'1','1');
		$this->SetFontWeight();
		$this->Ln(2);	
		
	}
	
}

?>
