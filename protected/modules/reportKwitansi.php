<?php
Prado::using('Application.modules.fpdf');
//$pdf='fpdf.php';
//require($pdf);

class reportKwitansi extends fpdf
{	
	
	//Page header	
	function Header()
	{
		if($this->water == '1')
		{
			//Put the watermark
			$this->SetFont('times','B',90);
			$this->SetTextColor(200,200,200);
			$this->RotatedText(25,115,"\t\t\t\t C O P Y ",20);
		}	
	}
	
	//Page footer
	function Footer()
	{    /*
		//Position at 1.5 cm from bottom
		$this->SetY(-20);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number					
		$this->Cell(0,10,'Kuitansi ini adalah bukti pembayaran yang sah ~ Hal. '.$this->PageNo().'/{nb}',0,0,'C');	*/	
	}


var $widths;
var $aligns;
var $borders;
var $ln=5;
var $angle=0;
var $water=0;
//Current column
	var $col=0;
	//Ordinate of column start
	var $y0;
	var $x0;
	var $footerTxt;

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

function SetBorders($b)
{
	//Set the array of column border
	$this->borders=$b;
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
		//Draw the border
		//$this->Rect($x,$y,$w,$h);
		//Print the text
		$this->MultiCell($w,$this->ln,$data[$i],0,$a);
		//Put the position to the right of the cell
		$this->SetXY($x+$w,$y);
	}
	//Go to the next line
	$this->Ln($h);
}

function RowBorder($data,$line='1')
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
		//$this->Rect($x,$y,$w,$h);
		//Print the text
		
		$this->MultiCell($w,$this->ln,$data[$i],$line,$a);
				
		//Put the position to the right of the cell
		$this->SetXY($x+$w,$y);
	}
	//Go to the next line
	$this->Ln($h);
}



function RowWithBorder($data)
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
		$b=$this->borders[$i];
		$a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
		$b=isset($this->borders[$i]) ? $this->borders[$i] : '1';
		
		//Save the current position
		$x=$this->GetX();
		$y=$this->GetY();
		//Draw the border
		//$this->Rect($x,$y,$w,$h);
		//Print the text
		$this->MultiCell($w,$this->ln,$data[$i],$b,$a);
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
		//Print the text
		$this->MultiCell($w,$this->ln,$data[$i],'0',$a);
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
}var $T128;                                             // tableau des codes 128
	var $ABCset="";                                        // jeu des caractères éligibles au C128
	var $Aset="";                                          // Set A du jeu des caractères éligibles
	var $Bset="";                                          // Set B du jeu des caractères éligibles
	var $Cset="";                                          // Set C du jeu des caractères éligibles
	var $SetFrom;                                          // Convertisseur source des jeux vers le tableau
	var $SetTo;                                            // Convertisseur destination des jeux vers le tableau
	var $JStart = array("A"=>103, "B"=>104, "C"=>105);     // Caractères de sélection de jeu au début du C128
	var $JSwap = array("A"=>101, "B"=>100, "C"=>99);       // Caractères de changement de jeu

//____________________________ Extension du constructeur _______________________
function reportBarcode($orientation='P',$unit='mm',$format='barcode') {

	parent::fpdf($orientation,$unit,$format);

	$this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               // composition des caractères
	$this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
	$this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
	$this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
	$this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
	$this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
	$this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
	$this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
	$this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
	$this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
	$this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
	$this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
	$this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
	$this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
	$this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
	$this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
	$this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
	$this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
	$this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
	$this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
	$this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
	$this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
	$this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
	$this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
	$this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
	$this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
	$this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
	$this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
	$this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
	$this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
	$this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
	$this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
	$this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
	$this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
	$this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
	$this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
	$this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
	$this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
	$this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
	$this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
	$this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
	$this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
	$this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
	$this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
	$this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
	$this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
	$this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
	$this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
	$this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
	$this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
	$this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
	$this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
	$this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
	$this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
	$this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
	$this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
	$this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
	$this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
	$this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
	$this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
	$this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
	$this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
	$this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
	$this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
	$this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
	$this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
	$this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
	$this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
	$this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
	$this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
	$this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
	$this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
	$this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
	$this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
	$this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
	$this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
	$this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
	$this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
	$this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
	$this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
	$this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
	$this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
	$this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
	$this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
	$this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
	$this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
	$this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
	$this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
	$this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
	$this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
	$this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
	$this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
	$this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
	$this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
	$this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
	$this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
	$this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
	$this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
	$this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
	$this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
	$this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]                
	$this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
	$this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
	$this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
	$this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
	$this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
	$this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
	$this->T128[] = array(2, 1);                       //107 : [END BAR]

	for ($i = 32; $i <= 95; $i++) {                                            // jeux de caractères
		$this->ABCset .= chr($i);
	}
	$this->Aset = $this->ABCset;
	$this->Bset = $this->ABCset;
	for ($i = 0; $i <= 31; $i++) {
		$this->ABCset .= chr($i);
		$this->Aset .= chr($i);
	}
	for ($i = 96; $i <= 126; $i++) {
		$this->ABCset .= chr($i);
		$this->Bset .= chr($i);
	}
	$this->Cset="0123456789";

	for ($i=0; $i<96; $i++) {                                                  // convertisseurs des jeux A & B  
		@$this->SetFrom["A"] .= chr($i);
		@$this->SetFrom["B"] .= chr($i + 32);
		@$this->SetTo["A"] .= chr(($i < 32) ? $i+64 : $i-32);
		@$this->SetTo["B"] .= chr($i);
	}
}

//________________ Fonction encodage et dessin du code 128 _____________________
function Code128($x, $y, $code, $w, $h) {
	$Aguid = "";                                                                      // Création des guides de choix ABC
	$Bguid = "";
	$Cguid = "";
	for ($i=0; $i < strlen($code); $i++) {
		$needle = substr($code,$i,1);
		$Aguid .= ((strpos($this->Aset,$needle)===false) ? "N" : "O"); 
		$Bguid .= ((strpos($this->Bset,$needle)===false) ? "N" : "O"); 
		$Cguid .= ((strpos($this->Cset,$needle)===false) ? "N" : "O");
	}

	$SminiC = "OOOO";
	$IminiC = 4;

	$crypt = "";
	while ($code > "") {
                                                                                    // BOUCLE PRINCIPALE DE CODAGE
		$i = strpos($Cguid,$SminiC);                                                // forçage du jeu C, si possible
		if ($i!==false) {
			$Aguid [$i] = "N";
			$Bguid [$i] = "N";
		}

		if (substr($Cguid,0,$IminiC) == $SminiC) {                                  // jeu C
			$crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  // début Cstart, sinon Cswap
			$made = strpos($Cguid,"N");                                             // étendu du set C
			if ($made === false) {
				$made = strlen($Cguid);
			}
			if (fmod($made,2)==1) {
				$made--;                                                            // seulement un nombre pair
			}
			for ($i=0; $i < $made; $i += 2) {
				$crypt .= chr(strval(substr($code,$i,2)));                          // conversion 2 par 2
			}
			$jeu = "C";
		} else {
			$madeA = strpos($Aguid,"N");                                            // étendu du set A
			if ($madeA === false) {
				$madeA = strlen($Aguid);
			}
			$madeB = strpos($Bguid,"N");                                            // étendu du set B
			if ($madeB === false) {
				$madeB = strlen($Bguid);
			}
			$made = (($madeA < $madeB) ? $madeB : $madeA );                         // étendu traitée
			$jeu = (($madeA < $madeB) ? "B" : "A" );                                // Jeu en cours

			$crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); // début start, sinon swap

			$crypt .= strtr(substr($code, 0,$made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversion selon jeu

		}
		$code = substr($code,$made);                                           // raccourcir légende et guides de la zone traitée
		$Aguid = substr($Aguid,$made);
		$Bguid = substr($Bguid,$made);
		$Cguid = substr($Cguid,$made);
	}                                                                          // FIN BOUCLE PRINCIPALE

	$check = ord($crypt[0]);                                                   // calcul de la somme de contrôle
	for ($i=0; $i<strlen($crypt); $i++) {
		$check += (ord($crypt[$i]) * $i);
	}
	$check %= 103;

	$crypt .= chr($check) . chr(106) . chr(107);                               // Chaine Cryptée complète

	$i = (strlen($crypt) * 11) - 8;                                            // calcul de la largeur du module
	$modul = $w/$i;

	for ($i=0; $i<strlen($crypt); $i++) {                                      // BOUCLE D'IMPRESSION
		$c = $this->T128[ord($crypt[$i])];
		for ($j=0; $j<count($c); $j++) {
			$this->Rect($x,$y,$c[$j]*$modul,$h,"F");
			$x += ($c[$j++]+$c[$j])*$modul;
		}
	}
}


}

?>
