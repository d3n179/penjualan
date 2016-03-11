<?php
Prado::using('Application.modules.tcpdf.tcpdf');
Prado::using('Application.modules.tcpdf.htmlcolors');

class reportKasirRawatInap extends TCPDF
{	
	public function Header()
	{
		$this->SetFont('helvetica', 'B', 9);
		$this->Cell(0, 10, 'Halaman '.$this->getAliasNumPage(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
	}
}

?>
