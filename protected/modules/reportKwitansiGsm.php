<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportKwitansiGsm extends fpdf
{	
	protected $footerTxt;
	
	//Page header	
	function Header()
	{
		if($this->water == '1')
		{
			//Put the watermark
			$this->SetFont('times','B',90);
			$this->SetTextColor(200,200,200);
			$this->RotatedText(0,130,"\t\t\t\t C O P Y ",45);
		}	
		
		/*if($this->diagnosaLabel == '1' || $this->stResepBaru == '1' )
		{
			if($this->stResepBaru == '1')
			{
				//Put the watermark
				$this->SetFont('times','B',30);
				$this->SetTextColor(0,0,0);
				$this->RotatedText(103,27,"FFS",0);	
			}
			else
			{
				if($this->page == 1)
				{
					//Put the watermark
					$this->SetFont('times','B',30);
					$this->SetTextColor(0,0,0);
					$this->RotatedText(103,27,"FFS",0);
				}
			}
		}*/	
	}
	
	//Page footer
	function Footer()
	{    /*
		//Position at 1.5 cm from bottom*/
		
		if(isset($this->footerTxt))
		{
			
			$this->SetFont('Arial','',8);
			
			if(substr($this->footerTxt,0,14) == 'cetakCopyResep')
			{
				$this->SetY(-18);
				$txt = explode('#',$this->footerTxt);
				$txt = $txt[1];
				
				/*$this->SetFont('Arial','I',8);
				$this->Cell(0,5,'RESEP INI SAH KARENA DICETAK MENGGUNAKAN KOMPUTER',0,0,'R');
				$this->Ln(5);
				$this->SetFont('Arial','B',8);
				$this->Cell(0,5,'HANYA UNTUK RESEP BERLAKU DI LINGKUNGAN RS PELABUHAN JAKARTA',1,0,'C');
				$this->Ln(5);*/
				$this->SetFont('Arial','',7);
				$this->Cell(0,5,$txt,0,0,'L');
			}
			else
			{
				$this->SetY(-10);
				$this->Cell(0,5,$this->footerTxt,0,0,'C');

			}
			$this->Cell(0,10,''.$this->PageNo().'/{nb}',0,0,'C');		
		}
		else
		{
				/*$this->SetFont('Arial','I',8);
				$this->Cell(0,5,'RESEP INI SAH KARENA DICETAK MENGGUNAKAN KOMPUTER',0,0,'R');
				$this->Ln(5);
				$this->SetFont('Arial','B',8);
				$this->Cell(0,5,'HANYA UNTUK RESEP BERLAKU DI LINGKUNGAN RS PELABUHAN JAKARTA',1,0,'C');
				$this->Ln(5);*/
		}
	}


var $widths;
var $aligns;
var $angle=0;
var $water=0;
var $ln=5;
var $stResepBaru=0;

//------------------------- WATERMARK ---------------------------------
function Rotate($angle,$x=-1,$y=-1)
{
	if($x==-1)
		$x=$this->x;
	if($y==-1)
		$y=$this->y;
	if($this->angle!=0)
		$this->_out('Q');
	$this->angle=$angle;
	if($angle!=0)
	{
		$angle*=M_PI/180;
		$c=cos($angle);
		$s=sin($angle);
		$cx=$x*$this->k;
		$cy=($this->h-$y)*$this->k;
		$this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
	}
}

function _endpage()
{
	if($this->angle!=0)
	{
		$this->angle=0;
		$this->_out('Q');
	}
	parent::_endpage();
}

function RotatedText($x, $y, $txt, $angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->Text($x,$y,$txt);
	$this->Rotate(0);
}

function SetFooterTxt($footerTxt)
{
	//Set the array of column widths
	$this->footerTxt=$footerTxt;
}

//------------------------- MULTICELL ---------------------------------
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
		//Save the current position
		$x=$this->GetX();
		$y=$this->GetY();
		//Draw the border
		$this->Rect($x,$y,$w,$h);
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
}

?>
