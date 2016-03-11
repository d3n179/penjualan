<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportCari extends fpdf
{	
	
	//Current column
	var $col=0;
	//Ordinate of column start
	var $y0;
	var $x0;
	
	var $widths;
	var $aligns;
	var $lineSpace;
	
	function SetWidths($w)
	{
		//Set the array of column widths
		$this->widths=$w;
	}
	
	function SetAligns($a)
	{
		//Set the array of column alignments
		$this->aligns=$a;
	}
	
	function SetLineSpacing($l)
	{
		//Set the array of column alignments
		$this->lineSpace=$l;
	}
	
	function SetLn($ln)
	{
		$this->ln=$ln;
	}
	
	function RowNoBorder($data)
{
	//Calculate the height of the row
	$nb=0;
	for($i=0;$i<count($data);$i++)
		$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
	$h=$this->ln*$nb;
	//Issue a page break first if needed
	$this->CheckPageBreak($h);
	//Draw the cells of the row
	for($i=0;$i<count($data);$i++)
	{
		$w=$this->widths[$i];
		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
		//Save the current position
		$x=$this->GetX();
		$y=$this->GetY();
		//$this->Rect($x,$y,$w,$h);
		//$this->Rect($x,$y,$w,(3*(count($data)-2)));
		//Print the text
		$this->MultiCell($w,$this->ln,$data[$i],0,$a);
		//$this->MultiCell($w,3,$data[$i],0,$a);
		//Put the position to the right of the cell
		$this->SetXY($x+$w,$y);
	}
	//Go to the next line
	$this->Ln($h);
}

	function Row($data)
	{
		//Calculate the height of the row
		$nb=0;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=$this->ln*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			$l=$this->lineSpace[$i];
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x,$y,$w,$h);
			//Print the text
			$this->MultiCell($w,$l,$data[$i],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}
	
	function Row2($data)
	{
		//Calculate the height of the row
		$nb=5;
		for($i=0;$i<count($data);$i++)
			$nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
		$h=$this->ln*$nb;
		//Issue a page break first if needed
		$this->CheckPageBreak($h);
		//Draw the cells of the row
		for($i=0;$i<count($data);$i++)
		{
			$w=$this->widths[$i];
			$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
			$l=$this->lineSpace[$i];
			//Save the current position
			$x=$this->GetX();
			$y=$this->GetY();
			//Draw the border
			$this->Rect($x,$y,$w,$h);
			//Print the text
			$this->MultiCell($w,$l,$data[$i],0,$a);
			//Put the position to the right of the cell
			$this->SetXY($x+$w,$y);
		}
		//Go to the next line
		$this->Ln($h);
	}
	
	function CheckPageBreak($h)
	{
		//If the height h would cause an overflow, add a new page immediately
		if($this->GetY()+$h>$this->PageBreakTrigger)
			$this->AddPage($this->CurOrientation);
	}
	
	function NbLines($w,$txt)
	{
		//Computes the number of lines a MultiCell of width w will take
		$cw=&$this->CurrentFont['cw'];
		if($w==0)
			$w=$this->w-$this->rMargin-$this->x;
		$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
		$s=str_replace("\r",'',$txt);
		$nb=strlen($s);
		if($nb>0 and $s[$nb-1]=="\n")
			$nb--;
		$sep=-1;
		$i=0;
		$j=0;
		$l=0;
		$nl=1;
		while($i<$nb)
		{
			$c=$s[$i];
			if($c=="\n")
			{
				$i++;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
				continue;
			}
			if($c==' ')
				$sep=$i;
			$l+=$cw[$c];
			if($l>$wmax)
			{
				if($sep==-1)
				{
					if($i==$j)
						$i++;
				}
				else
					$i=$sep+1;
				$sep=-1;
				$j=$i;
				$l=0;
				$nl++;
			}
			else
				$i++;
		}
		return $nl;
	}

	//Page header
	function Header()
	{
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
		
		if($this->page > 1)
		{
			$this->SetFont('Arial','B',8);
			
			$this->SetWidths(array(20,25,25,45,60,20));
			$this->SetAligns(array('C','C','C','C','C','C','C'));
			$this->Setln('4');
			$this->SetLineSpacing(array('4','4','4','4','4','4','4'));
						
			$this->Row(array(
						'Tgl. Kunjungan',
						'Dokter',
						'Poli',
						'Diagnosa',
						'Terapi',
						'Keterangan'
					));
			$this->SetFont('Arial','',8);	
			$this->SetAligns(array('C','C','C','L','L','L','C'));
		}	
	}
	
	//Page footer
	function Footer()
	{    
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number					
		$this->Cell(0,10,'Hasil Pencarian Data Pasien ~ Hal. '.$this->PageNo().'/{nb}',0,0,'C');		
	}
}

?>
