<?php

class SomeDataList
{
    private $connection;   
    
    public function __construct()
    {
        $this->connection = Prado::getApplication()->getModule('db')->DbConnection;
    }
    
    public function getSomeDataPage($sql)
    {
        $this->connection->Active = true;
        
        $sql = $sql . " ORDER BY $this->sortExpression $this->sortDirection
					   LIMIT $this->offset, $this->rowsPerPage ";
        
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
