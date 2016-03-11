<?PHP
class MainConf extends TPage
{
	public function queryAction($sql,$mode)
	{
		$conn = new TDbConnection("mysql:host=localhost;dbname="."penjualan","root","");				
		$conn->Persistent=true;
		$conn->Active=true;				
		if($mode == "C")//Use this with INSERT, DELETE and EMPTY operation
		{
			$comm=$conn->createCommand($sql);		
			$dataReader = $comm->query();						
		}
		else if($mode == "S")//Return for select statement
		{	
			$comm=$conn->createCommand($sql);		
			$dataReader = $comm->query();
			$rows=$dataReader->readAll();				
			
		}		
		else if($mode == "R") //Return set of rows
		{	
			$comm=$conn->createCommand($sql);		
			$dataReader = $comm->query();
			$rows=$dataReader;		
			
		}			
		else if($mode == "D") //Droped table
		{
			$que = "DROP TABLE IF EXISTS " . $sql;
			$comm=$conn->createCommand($que);		
			$dataReader = $comm->query();						
		}
		else if($mode == "X") //Droped table
		{
			$comm=$conn->createCommand($sql);
			$dataReader = $comm->query();
			$row=$dataReader->read();
			$rows = $row[count];	
		}	
		return $rows;
		$conn->Active=false;				
	}
	
	public function ConvertDate($tgl,$mode)
	{
		 if($mode == "1"){ //to normal
		 	$strtmp = substr($tgl,8,2) . "-" . substr($tgl,5,2)  . "-" . substr($tgl,0,4);
		}elseif($mode == "2"){ //to mysql		 
		 	$strtmp = substr($tgl,6,4) . "-" . substr($tgl,3,2)  . "-" . substr($tgl,0,2);
		}else{//to tgl indonesia 
				$blnIndo=$this->namaBulan(substr($tgl,5,2));
				$strtmp=substr($tgl,8,2) . " " . $blnIndo  . " " . substr($tgl,0,4);
			}		
		
		 return $strtmp;
	}
	
	public function namaBulan($month)
	{
		$nmBln=array('01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember');
		$sayBln = $nmBln[$month];
		return $sayBln;
	}
	
	public function collectSelectionResult($input)
    {
        $indices=$input->SelectedIndices;
        $result='';
        foreach($indices as $index)
        {
            $item=$input->Items[$index];
            $result.="$item->Value,";
        }
		$v = strlen($result) - 1;
		$res=substr($result,0,$v);
        return $res;
    }
    
    public function collectSelectionResultText($input)
    {
        $indices=$input->SelectedIndices;
        $result='';
        foreach($indices as $index)
        {
            $item=$input->Items[$index];
            $result.="$item->Text,";
        }
                $v = strlen($result) - 1;
                $res=substr($result,0,$v);
        return $res;
    }
    
    protected function collectSelectionListResult($input)
	{
		$indices=$input->SelectedIndices;		
		foreach($indices as $index)
		{
			$item=$input->Items[$index];
			return $index;
		}		
	}
	
	public function formatCurrency($data,$dec=0) 
	{
		return number_format($data,$dec,'.',',');
	}
	
	public function toInt($str)
	{
		return preg_replace("/([^0-9\\.])/i", "", $str);
	}
	
	public static function prosesLogout()
	{
		$user = Prado::getApplication()->User->Name;
		var_dump($user);
		$userData = UserRecord::finder()->findByPk($user);
		$userData->st_log = '0';
		$userData->ssid = '';
		$userData->save();
		
		Prado::getApplication()->getModule('auth')->logout();
		$url=Prado::getApplication()->Service->constructUrl(Prado::getApplication()->Service->DefaultPage);
		Prado::getApplication()->Response->redirect($url);
	}
	
	public function InsertJurnalBukuBesar($idTrans,$sumberTrans,$jnsTrans,$tglTrans,$wktTrans,$keterangan,$jmlTrans)
	{
		$JurnalBukuBesarRecord = new JurnalBukuBesarRecord();
		$JurnalBukuBesarRecord->id_transaksi = $idTrans;
		$JurnalBukuBesarRecord->sumber_transaksi = $sumberTrans;
		$JurnalBukuBesarRecord->jns_transaksi = $jnsTrans;
		$JurnalBukuBesarRecord->tgl_transaksi = $tglTrans;
		$JurnalBukuBesarRecord->wkt_transaksi = $wktTrans;
		$JurnalBukuBesarRecord->keterangan = $keterangan;
		$JurnalBukuBesarRecord->jml_transaksi = $jmlTrans;
		$JurnalBukuBesarRecord->save();
	}
	
	
}
?>
