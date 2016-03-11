<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportAntrian extends fpdf
{	
	
	//Page header	
	function Header()
	{
		
	}
	
	//Page footer
	function Footer()
	{    
		//Position at 1.5 cm from bottom
		$this->SetY(-10);
		//Arial italic 8
		$this->SetFont('Arial','I',4);
		//Page number					
		//$this->Cell(0,5,'LVRI DIGITAL LIBRARY ~ Hal. '.$this->PageNo().'/{nb}',0,0,'C');		
		$this->Cell(0,5,'*** KARTU ANTRIAN PASIEN RAWAT JALAN ***','T',0,'C');		
	}
}

?>
