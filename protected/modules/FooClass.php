<?php
class FooClass extends TJsonResponse
{
     private $_bar;
     public function setBar($value)
     {
           $this->_bar = $value; //we can set properties in the <json> config
     }

    public function getJsonContent()
    {
		$examp = $this->Request["q"]; //query number

		$pages = $this->Request['pages']; // get the requested pages
		$limit = $this->Request['rows']; // get how many rows we want to have into the grid
		$sidx = $this->Request['sidx']; // get index row - i.e. user click to sort
		$sord = $this->Request['sord']; // get the direction
		if(!$sidx) $sidx =1;
		
		$totalrows = isset($this->Request['totalrows']) ? $this->Request['totalrows']: false;
		if($totalrows) {$limit = $totalrows;}
		
		// search options
		// IMPORTANT NOTE!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		// this type of constructing is not recommendet
		// it is only for demonstration
		//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		$wh = "";
		$searchOn = $this->Strip($this->Request['_search']);
		if($searchOn=='true') {
			$fld = $this->Strip($this->Request['searchField']);
			if( $fld=='no_trans' || $fld =='nama_pasien') {
				$fldata = $this->Strip($this->Request['searchString']);
				$foper = $this->Strip($this->Request['searchOper']);
				// costruct where
				$wh .= " AND ".$fld;
				switch ($foper) {
					case "bw":
						$fldata .= "%";
						$wh .= " LIKE '".$fldata."'";
						break;
					case "eq":
						if(is_numeric($fldata)) {
							$wh .= " = ".$fldata;
						} else {
							$wh .= " = '".$fldata."'";
						}
						break;
					case "ne":
						if(is_numeric($fldata)) {
							$wh .= " <> ".$fldata;
						} else {
							$wh .= " <> '".$fldata."'";
						}
						break;
					case "bn": //does not begin with
						$fldata .= "%";
						$wh .= " NOT LIKE '".$fldata."'";
						break;
					case "lt":
						if(is_numeric($fldata)) {
							$wh .= " < ".$fldata;
						} else {
							$wh .= " < '".$fldata."'";
						}
						break;
					case "le":
						if(is_numeric($fldata)) {
							$wh .= " <= ".$fldata;
						} else {
							$wh .= " <= '".$fldata."'";
						}
						break;
					case "gt":
						if(is_numeric($fldata)) {
							$wh .= " > ".$fldata;
						} else {
							$wh .= " > '".$fldata."'";
						}
						break;
					case "ge":
						if(is_numeric($fldata)) {
							$wh .= " >= ".$fldata;
						} else {
							$wh .= " >= '".$fldata."'";
						}
						break;
					case "ew": //ends with
						$wh .= " LIKE '%".$fldata."'";
						break;
					case "en": //does not ends with
						$wh .= " NOT LIKE '%".$fldata."'";
						break;	
					case "cn": //contains
						$wh .= " LIKE '%".$fldata."%'";
						break;
					case "nc": //does not contains
						$wh .= " NOT LIKE '%".$fldata."%'";
						break;
					case "nu": //is null
						$wh .= " IS NULL ";
						break;	
					case "nn": //is not null
						$wh .= " IS NOT NULL ";
						break;	
					case "in": //is in
						$data = explode(',',$fldata);
						for($i=0;$i<count($data);$i++)
						{
							if($i == (count($data) -1))	
								$arrData .= "'".$data[$i]."'";
							else
								$arrData .= "'".$data[$i]."',";
						}
						
						$wh .= " IN ( ".$arrData.")";
						break;		
					case "ni": //is not null
						$data = explode(',',$fldata);
						for($i=0;$i<count($data);$i++)
						{
							if($i == (count($data) -1))	
								$arrData .= "'".$data[$i]."'";
							else
								$arrData .= "'".$data[$i]."',";
						}
						
						$wh .= " NOT IN ( ".$arrData.")";
						break;			
					default :
						$wh = "";
				}
			}
		}
		
		$sql = "SELECT tbd_pasien_rsp.NAMA AS nama_pasien,tbd_pasien_rsp.KODE_PASIEN AS no_trans FROM tbd_pasien_rsp WHERE tbd_pasien_rsp.KODE_PASIEN IS NOT NULL ".$wh;
		$data = $this->queryAction($sql,'S');
		 
		$count = count($data);
		if( $count >0 ) {
			$total_pages = ceil($count/$limit);
		} else {
			$total_pages = 0;
		}
		
        if ($pages > $total_pages) $pages=$total_pages;
		$start = $limit*$pages - $limit; // do not put $limit*($pages - 1)
        if ($start<0) $start = 0;
		
		$sql = "SELECT tbd_pasien_rsp.NAMA AS nama_pasien , tbd_pasien_rsp.KODE_PASIEN AS no_trans FROM tbd_pasien_rsp WHERE tbd_pasien_rsp.KODE_PASIEN IS NOT NULL ".$wh." ORDER BY ".$sidx." ". $sord." LIMIT ".$start." , ".$limit;
		
		$data = $this->queryAction($sql,'S');
		
		$i=0;
		foreach($data as $row)
		{
			$rows[$i]['id']=$row['no_trans'];
			$rows[$i]['cell']=array($row['no_trans'],$row['nama_pasien'],$row['nama_pasien']);
			$i++;
		}
		
		 return array('pages'=> $pages, 'total'=> $total_pages, 'records'=> $count, 'rows'=>$rows );
          // $this->Request['param1'] 
         // return array('good'=> $this->_bar); 
		 //return $data; 
    }
	
	public function Strip($value)
	{
		if(get_magic_quotes_gpc() != 0)
		{
			if(is_array($value))  
				if ( $this->array_is_associative($value) )
				{
					foreach( $value as $k=>$v)
						$tmp_val[$k] = stripslashes($v);
					$value = $tmp_val; 
				}				
				else  
					for($j = 0; $j < sizeof($value); $j++)
						$value[$j] = stripslashes($value[$j]);
			else
				$value = stripslashes($value);
		}
		return $value;
	}
	
	public function array_is_associative ($array)
	{
		if ( is_array($array) && ! empty($array) )
		{
			for ( $iterator = count($array) - 1; $iterator; $iterator-- )
			{
				if ( ! array_key_exists($iterator, $array) ) { return true; }
			}
			return ! array_key_exists(0, $array);
		}
		return false;
	}

	public function queryAction($sql,$mode)
	{		
		$conn = new TDbConnection("mysql:host=localhost;dbname=simak_pelabuhan","simyantu","jackass");		
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
		else if($mode == "E") //Hapus isi table
		{
			$que = "TRUNCATE TABLE " . $sql;
			$comm=$conn->createCommand($que);		
			$dataReader = $comm->query();						
		}
		return $rows;
		$conn->Active=false;				
	}
	
	
}

?>