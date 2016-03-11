<?php
class DatatablesJson extends TJsonResponse
{
	public static function limit ( $request, $columns )
	{
		$limit = '';

		if(isset($request['iDisplayStart']) && $request['iDisplayLength'] != -1 ) 
		{
			$limit = "LIMIT ".intval($request['iDisplayStart']).", ".intval($request['iDisplayLength']);
		}

		return $limit;
	}
	
	public static function order ($request, $columns)
	{
		$dtColumns = self::pluck( $columns, 'dt' );
		$columnsCount = count($columns);
		if($columns[0]['dt']=="DT_RowId")
			$columnsCount -= 1;
			
		$order = '';
		//for($i=0; $i<$columnsCount ; $i++ ) 
		for($i=0; $i<$columnsCount ; $i++ ) 
		{
			$sortCol = $request["iSortCol_".$i];
			$sortDir = $request["sSortDir_".$i];
			
			$columnIdx = array_search((string)$sortCol, $dtColumns );
			$column = $columns[ $columnIdx ];
				
			if($request["bSortable_".$i]==true && $sortDir)
			{
				$requestColumn = $column["alias"]!=""?$column["alias"]:$column["db"];
				$dir = $sortDir === 'asc' ? "ASC" : "DESC";
				$orderBy[] = '`'.$requestColumn.'` '.$dir;
			}
		}
		
		$order = 'ORDER BY '.implode(', ', $orderBy);
		return $order;
	}
	
	public static function filter ($request,$columns,$bindings)
	{
		$columnsCount = count($columns);
		$globalSearch = array();
		$columnSearch = array();
		$dtColumns = self::pluck( $columns, 'dt' );
		if($columns[0]['dt']=="DT_RowId")
			$columnsCount -= 1;
		
		if(isset($request['sAction']) && $request['sAction']=='filter') 
		{
			for($i=0; $i<$columnsCount ; $i++ ) 
			{
				$searchValue = "";
				$compare2 = "";
				$compare3 = "";
				
				$columnIdx = array_search((string)$i, $dtColumns );
				$column = $columns[ $columnIdx ];
				
				$field = $column["table"].'.'.$column["db"];
				if($request["bSearchable_".$i]==true)
				{
					$fieldSearch = $column['alias']!='' ? $column["alias"]:$column["db"];
					$comparisonPattern = $column["comparison_pattern"] != "" ? strtoupper($column["comparison_pattern"]) : "CONTAINS";
					
					if($column["custom_query"]==false)
						$compare1 = $column["table"].".`".$column["db"]."`";
					else
						$compare1 = "`".$column["alias"]."`";
						//$compare1 = $column["table"].".`".$column["alias"]."`";
					
					if($column["field_search"])
						$compare1 = $column["field_search"];

					$compare2 = $request[$fieldSearch];
					
					if($column["comparison_type"]=="date" && $compare2!='')
					{
						$compare2 = SimakConf::convertDate($compare2,"2");
					}
					
					switch($comparisonPattern)
					{
						case "NOT_NULL":
							$searchValue = $compare2." IS NOT NULL";
							break;
						case "CONTAINS":
							$searchValue = "LOWER(".$compare1.") LIKE '%".strtolower($compare2)."%'";
							break;
						case "DOES_NOT_CONTAIN":
							$searchValue = "LOWER(".$compare1.") NOT LIKE '%".strtolower($compare2)."%'";
							break;
						case "EQUAL":
							$searchValue = $compare1." = '".$compare2."'";
							break;
						case "NOT_EQUAL":
							$searchValue = $compare1." <> '".$compare2."'";
							break;
						case "GREATER_THAN":
							$searchValue = $compare1." > '".$compare2."'";
							break;
						case "LESS_THAN":
							$searchValue = $compare1." < '".$compare2."'";
							break;
						case "GREATER_THAN_OR_EQUAL":
							$searchValue = $compare1." >= '".$compare2."'";
							break;
						case "LESS_THAN_OR_EQUAL":
							$searchValue = $compare1." <= '".$compare2."'";
							break;
						case "STARTS_WITH":
							$searchValue = "LOWER(".$compare1.") LIKE '".strtolower($compare2)."%'";
							break;
						case "ENDS_WITH":
							$searchValue = "LOWER(".$compare1.") LIKE '".strtolower($compare2)."'";
							break;
						case "BETWEEN":
							$compare2 = $request[$fieldSearch."_from"];
							$compare3 = $request[$fieldSearch."_to"];
							
							if($column["comparison_type"]=="date" && $compare2!='')
								$compare2 = SimakConf::convertDate($compare2,"2");
								
							if($column["comparison_type"]=="date" && $compare3!='')
								$compare3 = SimakConf::convertDate($compare3,"2");
							
							if($compare2!="" && $compare3=="")
								$searchValue = $compare1." >= '".$compare2."'";
							elseif($compare2=="" && $compare3!="")	
								$searchValue = $compare1." <= '".$compare3."'";
							else	
								$searchValue = $compare1." BETWEEN '".$compare2."' AND '".$compare3."'";
							break;
					}
					
					if($column["field_where_additional"])
						$searchValue .= " ".$column["field_where_additional"]." ";
						
					if(($searchValue && trim($compare2)!="") || ($searchValue && trim($compare3)!=""))
					{
						//$fieldSearch = $column["table"].".`".$column["db"]."`";
						$globalSearch[] = $searchValue;
					}
				}
			}
			
			$where = '';
			
			if ( count( $globalSearch ) ) {
				$where = implode(' AND ', $globalSearch);
			}
			
			if ( $where !== '' ) {
				$where = ' WHERE '.$where;
			}
		}
		
		if($request['table_where'])
		{
			if($where != '')
				$where .= ' AND '.$request['table_where'];
			else
				$where = ' WHERE '.$request['table_where'];
		}
		
		return $where;
	}
	
	public static function join($request,$columns,$table)
	{
		$arrJoin = json_decode($request['table_join'],true);
		foreach($arrJoin as $row)
		{
			if($row["table_name_alias"])
				$tableJoin = $row["table_name_alias"];
			else
				$tableJoin = $row["table_name"];
				
			if($row["custom_join"])
			{
				$join[] = $row["custom_join"];
			}
			else
			{
				if($row["table_join"])
					$join[] = $row["type"]." ".$row["table_name"]." ".$row["table_name_alias"]." ON (".$tableJoin.".".$row["pk"]." = ".$row["table_join"].".".$row["fk"].")";
				else	
					$join[] = $row["type"]." ".$row["table_name"]." ".$row["table_name_alias"]." ON (".$tableJoin.".".$row["pk"]." = ".$table.".".$row["fk"].")";
			}
		}
		
		$join = implode(' ', $join);
		return $join;
	}
	
	public static function data_output($columns,$data)
	{
		$out = array();
		for ( $i=0, $ien=count($data) ; $i<$ien ; $i++ ) {
			$row = array();
			
			for ( $j=0, $jen=count($columns) ; $j<$jen ; $j++ ) 
			{
				$column = $columns[$j];
				
				if(isset($column['formatter']))
				{
					$fieldName = $column['alias']!='' ? $column['alias']:$column['db'];
					$row[$column['dt']] = $column['formatter']( $data[$i][$fieldName], $data[$i] );
				}
				else 
				{
					$fieldName = $columns[$j]['alias']!='' ? 'alias':'db';
					$row[$column['dt']] = $data[$i][ $columns[$j][$fieldName]];
				}
			}
			
			$out[] = $row;
		}

		return $out;
	}
	
	public static function pluck($a,$prop,$table="",$alias="")
	{
		$out = array();

		for($i=0, $len=count($a); $i<$len; $i++ ) 
		{
			if($a[$i]['custom_query']==false)
			{
				if($table!="")
					$field = $a[$i][$table].'.`'.$a[$i][$prop]."`";
				else	
					$field = $a[$i][$prop];
				
				if($a[$i][$alias]!="")
					$field = $field.' AS '.$a[$i][$alias];
			}
			else
			{
				$field = $a[$i][$prop];
				
				if($a[$i][$alias]!="")
					$field = $field.' AS '.$a[$i][$alias];
			}
			$out[] = $field;	
			
			if(!empty($a[$i]['field_additional']))
				array_push($out,$a[$i]['field_additional']);
		}
		return $out;
	}
	
	public static function bind ( &$a, $val, $type )
	{
		$key = ':binding_'.count( $a );

		$a[] = array(
			'key' => $key,
			'val' => $val,
			'type' => $type
		);

		return $key;
	}
	
	public function getDatatablesColumnsFunc($activeRecord,$getDatatablesColumnsFunc)
	{
		if($this->Request["getDatatablesColumnsFuncParam"])
			return $activeRecord::$getDatatablesColumnsFunc(json_decode($this->Request["getDatatablesColumnsFuncParam"],true));
		else	
			return $activeRecord::$getDatatablesColumnsFunc();
	}
	
	public function getJsonContent()
	{
		$bindings = array();
		$request = $_POST;
		$primaryKey = $this->Request["primaryKey"];
		$activeRecord = $this->Request["activeRecord"];
		
		if($this->Request["getDatatablesSourceTable"])
		{
			$table = $this->Request["getDatatablesSourceTable"];
			
			$replaceTxtSourceTable = $this->Request["replaceTxtSourceTable"];
			if($replaceTxtSourceTable)
			{
				$replaceData = json_decode($replaceTxtSourceTable,true);
				$table = str_replace($replaceData['replaceTxt'], implode(' AND ',array_merge($replaceData['initData'],$replaceData['additionalData'])), $table);
			}
		}
		else
			$table = $activeRecord::TABLE;
		
		if($this->Request["getDatatablesColumnsFunc"])
			$columns = $this->getDatatablesColumnsFunc($activeRecord,$this->Request["getDatatablesColumnsFunc"]);
		else	
			$columns = $activeRecord::getDatatablesColumns();
		$columnsCount = count($columns);
		
		$limit = self::limit($request,$columns);
		$order = self::order($request,$columns);
		$join = self::join($request,$columns,$table);
		$where = self::filter($request,$columns,$bindings);
		
		if($this->Request['table_group'])
			$group = ' GROUP BY '.$this->Request['table_group'];
		
		//$sql =  "SELECT SQL_CALC_FOUND_ROWS `".implode("`, `", self::pluck($columns,'db'))."`
		$sqlWithoutOrderLimit = "SELECT SQL_CALC_FOUND_ROWS ".implode(",", self::pluck($columns,'db','table','alias'))."
			 FROM $table
			 $join
			 $where
			 $group";
		
		$sql = $sqlWithoutOrderLimit.' '.$order.' '.$limit;	 
		$data = SimakConf::queryAction($sql,"S");
		$sqlAll = $sql;
		
		$sql = "SELECT FOUND_ROWS() AS resFilterLength";
		$resFilterLength = SimakConf::queryAction($sql,"S");
		$recordsFiltered = $resFilterLength[0]['resFilterLength'];
		
		if($this->Request['table_where'])
			$tableWhere = ' WHERE '.$this->Request['table_where'];
		
		if($this->Request['table_group'])
			$sql = "SELECT COUNT(DISTINCT ".$this->Request['table_group'].") AS recordsTotal FROM $table $join $tableWhere";
		else
			$sql = "SELECT COUNT(*) AS recordsTotal FROM $table $join $tableWhere";
		/*if($this->Request['table_group'])
			$sql = "SELECT COUNT(DISTINCT ".$this->Request['table_group'].") AS recordsTotal FROM $table $join $tableWhere $group";
		else
			$sql = "SELECT COUNT(*) AS recordsTotal FROM $table $join $tableWhere $group";*/
				
		//$sql = "SELECT COUNT(`{$primaryKey}`) AS recordsTotal FROM $table";
		$resTotalLength = SimakConf::queryAction($sql,"S");
		$recordsTotal = $resTotalLength[0]['recordsTotal'];

		return array(
			"draw"            			=> intval($request['draw']),
			"iTotalRecords"    			=> intval( $recordsTotal ),
			"iTotalDisplayRecords"	=> intval( $recordsFiltered ),
			"recordsFiltered" 			=> intval( $recordsFiltered ),
			"aaData"            		=> self::data_output($columns,$data),
			"sEcho"            			=> $request['sEcho'],
			//"reqColumn"            	=> $request['reqColumn'],
			"where"            	=> $where,
			"sql"            	=> $sqlAll,
			"sqlWithoutOrderLimit"            	=> $sqlWithoutOrderLimit,
		);
	}
	
}

?>
