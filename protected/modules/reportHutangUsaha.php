<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportHutangUsaha extends reportJurnal
{	
	
	var $namaRs;
	var $kotaRs;
	var $pbf;
	var $periode;
	
	//Page header	
	function Header()
	{
		$this->SetFont('Arial','B',11);
		
		$this->SetLn(5);
		$this->SetWidths(array(85,115,85));
		$this->SetAligns(array('L','C','R'));
		$this->SetFontWeight(array('','B',''));
		$this->SetBorderColumn(array('0','0','0'));
		$this->RowBorder(array($this->namaRs,'KARTU HUTANG USAHA',''),'1','1');
		$this->SetFontWeight(array('','',''));
		$this->RowBorder(array($this->kotaRs,$this->periode,'Halaman : '.$this->PageNo().'/{nb}'),'1','1');	
		
		$this->Ln(2);
		
		$this->SetFont('Arial','',10);
		
		if($this->pbf)
		{
			$this->SetWidths(array(30,5,250));
			$this->SetAligns(array('L','L','L'));
			$this->SetFontWeight(array('','',''));
			$this->SetBorderColumn(array('TBL','TB','TBR'));
			$this->RowBorder(array('Perusahaan',':',$this->pbf),'1','1');
		}
		
		$this->SetLn(7);
		$this->SetWidths(array(10,25,30,110,35,40,35));
		$this->SetAligns(array('L','C','L','L','L','L','R'));
		$this->SetFontWeight(array('B','B','B','B','B','B','B'));
		$this->SetBorderColumn();
		$this->SetBorderColumn(array('TBL','TB','TB','TB','TB','TB','TBR'));
		$this->RowBorder(array('NO','NO.JB','NO.FAKTUR','PERUSAHAAN','JATUH TEMPO','UMUR HUTANG','JUMLAH'),'1','1');
		
		$this->SetFontWeight();
		$this->SetLn(5);
	}
	
}

?>
