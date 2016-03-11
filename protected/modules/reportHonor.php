<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportHonor extends fpdf
{	
	
	//Page header	
	function Header()
	{
		
	}
	
	//Page footer
	function Footer()
	{    /*
		//Position at 1.5 cm from bottom
		$this->SetY(-20);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number					
		$this->Cell(0,10,'Honor Dokter ~ Hal. '.$this->PageNo().'/{nb}',0,0,'C');*/		
	}
}

?>