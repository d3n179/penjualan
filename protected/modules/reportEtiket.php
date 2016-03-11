<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportEtiket extends fpdf
{	
	
	//Page header	
	function Header()
	{
		
	}
	
	//Page footer
	function Footer()
	{    
		//Position at 1.5 cm from bottom
		
		//Arial italic 8
		
		//Page number					
		//$this->Cell(0,5,'LVRI DIGITAL LIBRARY ~ Hal. '.$this->PageNo().'/{nb}',0,0,'C');		
				
	}
}

?>
