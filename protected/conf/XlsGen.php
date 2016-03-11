<?php
class XlsGen extends MainConf
{
	//Headering EXCEL file!
	public function HeaderingExcel($filename)
	{
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=$filename");
		header("Expires:0");
		header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
		header("Pragma: public");
	}
	
	public function AddFormat($format,$tipe,$mode,$size)//Format font jadi bold
    {
       	/*
			Katalog function:
			
			--Font manipulation--					
			B = Font Bold
			I = Font Italic
			U = Font Underline
			UI = Font Underline Italic
			BI = Font Bold Italic	
			HA = Horizontal Align	
			C = Set font color	
			
		*/
		
		$tipe=strtoupper($tipe);
		if($tipe == 'B'){			
			$format->set_bold();
		} 
		
		if($tipe == 'I'){			
			$format->set_italic();
		}
		
		if($tipe == 'U'){				
			$format->set_underline($mode);
		}
		
		if($tipe == 'UI'){				
			$format->set_underline($mode);
			$format->set_italic();			
		}
		
		if($tipe == 'BI'){				
			$format->set_bold();
			$format->set_italic();			
		}
		
		if($tipe == 'HA'){				
			$format->set_align($mode);			
		}
		
		if($tipe == 'C'){				
			$format->set_color($mode);		
		}
		
		if($tipe == 'BD'){				
			$format->set_border($mode);		
		}
		
		if($tipe == 'WR'){				
			$format->set_text_wrap($mode);		
		}
		
		if($tipe == 'NUMFORMAT'){			
			$format->set_num_format($mode);
		} 
		
		if(!empty($size)){			
			$format->set_size($size);
		}			
		return $format;
    }	
	
	public function AddWS($ws,$tipe,$size,$start,$end)//Format font jadi bold
    {
		$tipe=strtoupper($tipe);
		if($tipe == 'C'){//Set column property				
			$ws->set_column($start,$end,$size);			
		}
		
		if($tipe == 'R'){//Set column property				
			$ws->set_row($start,$size);			
		}			
		return $ws;	
		
	}	
}
?>
