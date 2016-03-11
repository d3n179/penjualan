<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportSimak extends fpdf
{
	//Page header
	function Header()
	{
		$this->SetFont('Arial','B',16);
		//Title Header
		$this->Cell(0,10,'RSUD. KOTA BANDUNG',0,0,'C');
		$this->Ln(5);
		$this->SetFont('Arial','B',12);
		$this->Cell(0,10,'Jl. Rumah Sakit No. 22 Ujungberung - Bandung',0,0,'C');		
		$this->Ln(5);
		$this->Cell(0,10,'Tlp/Fax (022) 7811794',0,0,'C');
		$this->Ln(20);		
		$this->Cell(20,10,'NIP',1,0,'C');
		$this->Cell(60,10,'Nama',1,0,'C');
		$this->Cell(20,10,'Gol.',1,0,'C');
		$this->Cell(20,10,'TMT Gol.',1,0,'C');
		$this->Cell(40,10,'Jabatan',1,0,'C');
		$this->Cell(30,10,'TMT Jabatan',1,0,'C');
		$this->Ln();	
	}
	
	//Page footer
	function Footer()
	{    
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number
		$this->Cell(0,9,'CopyRight of Intergo Telematics 2007 ~ Hal. '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
?>