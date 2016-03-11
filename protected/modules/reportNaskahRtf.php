<?php
/*require("barcode.php");		   
require("i25object.php");
require("c39object.php");
require("c128aobject.php");
require("c128bobject.php");
require("c128cobject.php");*/

$rtf='Rtf.php';
require($rtf);

class reportNaskahRtf extends Rtf
{	
	//Page header
	//function Header()
	//{
		//Title Header
		/*
		$this->Image('protected/modules/logo.PNG',12,12,25);	
		$this->SetFont('Arial','B',12);
		$this->Cell(10,10,'','0',0,'C');
		$this->Cell(0,10,'P E M E R I N T A H   K A B U P A T E N  S U K A B U M I','0',0,'C');
		$this->Ln(7);
		$this->SetFont('Arial','B',16);	
		$this->Cell(10,10,'','0',0,'C');
		$this->Cell(0,10,'DINAS PERIZINAN TERPADU DAN','0',0,'C');
		$this->Ln(7);	
		$this->Cell(10,10,'','0',0,'C');
		$this->Cell(0,10,'PENANAMAN MODAL','0',0,'C');
		$this->Ln(7);
		$this->SetFont('Arial','B',10);
		$this->Cell(10,10,'','0',0,'C');
		$this->Cell(0,10,'JL. Raya Cibolang Km. 7 Cisaat Telp./ Fax (0266) 22107, 237527 Kodepos 43152','0',0,'C');
		$this->Ln(5);
		$this->Cell(10,10,'','0',0,'C');
		$this->Cell(0,10,'Sukabumi','0',0,'C');
		$this->Ln(3);
		$this->Cell(0,5,'','B',1,'C');
		$this->Ln(5);
		*/	
	//}
	
	//Page footer
	//function Footer()
	//{    
		//Position at 1.5 cm from bottom
		//$this->SetY(-15);
		//Arial italic 8
		//$this->SetFont('Arial','I',8);
		//Page number					
		//$this->Cell(0,10,'CopyRight of Intergo Telematics ~ Hal. '.$this->PageNo().'/{nb}',0,0,'C');		
	//}
}

?>