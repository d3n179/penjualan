<?php

class SomeDataList
{
    private $connection;   
    
    public function __construct()
    {
        $this->connection = Prado::getApplication()->getModule('db')->DbConnection;
    }
    
    public function getSomeDataPageAdo($sql,$sqlCount="")
    {
			$db = Prado::getApplication()->getModule('adodb');
			
			if($sqlCount=='')
			{
				$string = trim($sql);
				$string_len=strlen($string);
				$replace = "";
				$search = "group by";
				$search_len=strlen($search);
				$pos = strrpos(strtolower($string),$search);
				if($pos>0)
				{
					$sqlCount = substr($string,0,$pos);
					$sqlCount = substr_replace(trim($sqlCount), " COUNT(*) AS TotalRows, ", 6, 0);
				}
				else
					$sqlCount = substr_replace(trim($string), " COUNT(*) AS TotalRows, ", 6, 0);
			}
			else
			{
				$string = trim($sqlCount);
				$sqlCount = substr_replace(trim($string), " COUNT(*) AS TotalRows, ", 6, 0);
			}
			
			$res_sql=$db->Execute($sqlCount);
			$TotalRows = $res_sql->Fields('TotalRows');
			
			$sql = $sql . " ORDER BY $this->sortExpression $this->sortDirection LIMIT $this->offset, $this->rowsPerPage ";
			$liste_prg=$db->Execute($sql);
			$arrData=$liste_prg->GetArray();
	
			return array('Rows'=>$arrData,'TotalRows'=>$TotalRows);
    }
    
    public function getSomeDataPageNew($sql,$sqlCount="",$clauseCount="")
    {
			
			$this->connection->Persistent=true;
			$this->connection->Active=true;				
			
			if($clauseCount=='')
				$clauseCount = "COUNT(*)";
				
			if($sqlCount=='')
			{
				$string = trim($sql);
				$string_len=strlen($string);
				$replace = "";
				$search = "group by";
				$search_len=strlen($search);
				$pos = strrpos(strtolower($string),$search);
				if($pos>0)
				{
					$sqlCount = substr($string,0,$pos);
					$sqlCount = substr_replace(trim($sqlCount), " ".$clauseCount." AS TotalRows, ", 6, 0);
				}
				else
					$sqlCount = substr_replace(trim($string), " ".$clauseCount." AS TotalRows, ", 6, 0);
			}
			else
			{
				$string = trim($sqlCount);
				$sqlCount = substr_replace(trim($string), " ".$clauseCount." AS TotalRows, ", 6, 0);
			}
			
			$comm=$this->connection->createCommand($sqlCount);		
			$dataReader = $comm->query();
			$arrCount=$dataReader->readAll();				
			
			//$arrCount = SimakConf::queryAction($sqlCount,'S');
			foreach($arrCount as $rowCount) 
			{
				$TotalRows = $rowCount['TotalRows'];
			}
			
			$sql = $sql . " ORDER BY $this->sortExpression $this->sortDirection LIMIT $this->offset, $this->rowsPerPage ";
			$comm=$this->connection->createCommand($sql);		
			$dataReader = $comm->query();
			$arrData=$dataReader->readAll();				
			
			//$arrData = SimakConf::queryAction($sql,'S');
			
			$this->connection->Active=false;			
			
			return array('Rows'=>$arrData,'TotalRows'=>$TotalRows);
    }
    
    public function getSomeDataPage($sql)
    {
        $this->connection->Active = true;
        
        if($this->sortExpression != '' &&  $this->sortDirection != '')
        {
			$orderSql = " ORDER BY $this->sortExpression $this->sortDirection ";
		}
		else
		{
			$orderSql = "";
		}
		
        $sql = $sql . $orderSql . " LIMIT $this->offset, $this->rowsPerPage ";
        
        $command = $this->connection->createCommand($sql);
        $dataReader = $command->query();
        
        $rows = $dataReader->readAll();
        
        $dataReader->close();
        $this->connection->Active = false;
		
        return $rows;
    }
        
    public function getSomeDataCount($sql)
    {
        $this->connection->Active = true;       
              
        $command = $this->connection->createCommand($sql);
        $dataReader = $command->query();
        
        $rows = $dataReader->readAll();
        
        $dataReader->close();
        $this->connection->Active = false;
		$jmlData = count($rows);

        return $jmlData;
    }   
}
?>
