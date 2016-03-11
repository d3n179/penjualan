<?php
class DataProvider
{
	private $connection;
	private $pageOffset = 0;
	private $pageSize = 10;	
	private $tablename;
	private $pageNum;
	//$start = $pagenum * $pagesize;
	private $filter = array();
	private $sortExpression;
	private $groupBy;
	private $having;
	private $selectField;
	private $selectJoinField;
	private $clauseJoinField;
	private $limitStatus = true;
	private $unionSQL;
	
	public function __construct()
	{
		$this->connection = Prado::getApplication()->getModule('db')->DbConnection;
	}
	
	public function getLimitStatus() 
	{
		return $this->limitStatus;
		//return $this->limitStatus;
	}
	
	public function setLimitStatus($limitStatus) 
	{
		return $this->limitStatus = $limitStatus;
	}
	
	public function getGroupBy() 
	{
		return $this->groupBy;
	}
	
	public function setGroupBy($groupBy) 
	{
		return $this->groupBy = $groupBy;
	}
	
	public function getHaving() 
	{
		return $this->having;
	}
	
	public function setHaving($having) 
	{
		return $this->having = $having;
	}
	
	public function getFilter() 
	{
		return $this->filter;
	}
	
	public function setFilter($filter) 
	{
		return $this->filter = $filter;
	}
	
	public function getClauseJoinField() 
	{
		if ($this->clauseJoinField!==null) {
			return $this->clauseJoinField;
		}
		
		return $this->clauseJoinField;
	}
	
	public function setClauseJoinField($clauseJoinField) {
		return $this->clauseJoinField = $clauseJoinField;
	}
	
	public function getSelectJoinField() 
	{
		if ($this->selectJoinField!==null) {
			return $this->selectJoinField;
		}
		
		return $this->selectJoinField;
	}
	
	public function setSelectJoinField($selectJoinField) {
		return $this->selectJoinField = $selectJoinField;
	}
	
	public function getSelectField() 
	{
		if ($this->selectField!==null) {
			return $this->selectField;
		}
		
		return $this->selectField;
	}
	
	public function setSelectField($selectField) {
		return $this->selectField = $selectField;
	}
	
	public function getPageSize() 
	{
		if ($this->pageSize!==null) {
			return $this->pageSize;
		}
		
		return $this->pageSize;
	}
	
	public function setPageSize($pageSize) {
		return $this->pageSize = $pageSize;
	}
	
	public function getPageNum() 
	{
		if ($this->pageNum!==null) {
			return $this->pageNum;
		}
		
		return $this->pageNum;
	}
	
	public function setPageNum($pageNum) {
		return $this->pageNum = $pageNum;
	}
	
	public function getTablename() 
	{
		if ($this->tablename!==null) {
			return $this->tablename;
		}
		
		return $this->tablename;
	}
	
	public function setTablename($tablename) {
		return $this->tablename = $tablename;
	}
	
	public function getSortExpression() 
	{
		if ($this->sortExpression!==null) {
			return $this->sortExpression;
		}
		
		return $this->sortExpression;
	}
	
	public function setSortExpression($sortExpression) {
		return $this->sortExpression = $sortExpression;
	}
	
	public function getUnionSql() 
	{
		return $this->unionSQL;
	}
	
	public function setUnionSql($unionSQL) 
	{
		return $this->unionSQL = (!empty($unionSQL))?$unionSQL:"";
	}
	
	public function getData()
	{
		$nmTable = $this->Tablename;		
		$field = $this->SelectField;
		
		if($this->SelectJoinField)
			$fieldJoin = "," . $this->SelectJoinField;
			
		$clauseJoin = " " . $this->ClauseJoinField;
		
		if($this->GroupBy)
		{
			$groupBy = " GROUP BY " . $this->GroupBy;
			
			if($this->Having)	
				$groupBy .= " HAVING " . $this->Having;
		}
		
		if($this->SortExpression)	
			$sqlOrder = " ORDER BY " . $this->SortExpression;
		
		if($this->LimitStatus)
		{
			$start = $this->PageNum * $this->PageSize;
			$limit = "LIMIT " . $start . "," . $this->PageSize;
		}
		
		if(count($this->Filter) > 0)
		{
			$filterscount = count($this->Filter);
			
			$where = " WHERE (";
			$tmpdatafield = "";
			$tmpfilteroperator = "";
			for ($i=0; $i < $filterscount; $i++)
			{
				// get the filter's value.
				if($this->Filter[$i]["filtermysqlescapedisable"]!='1')
					$filtervalue = mysql_escape_string($this->Filter[$i]["filtervalue"]);
				else	
					$filtervalue = $this->Filter[$i]["filtervalue"];
				// get the filter's condition.
				$filtercondition = $this->Filter[$i]["filtercondition"];
				// get the filter's column.
				$filterdatafield = $this->Filter[$i]["filterdatafield"];
				// get the filter's operator.
				$filteroperator = $this->Filter[$i]["filteroperator"];
				
				if ($tmpdatafield == "")
				{
					$tmpdatafield = $filterdatafield;			
				}
				else if ($tmpdatafield <> $filterdatafield && $tmpfilteroperator=='0')
				{
					$where .= ") AND (";
				}
				else if ($tmpdatafield == $filterdatafield || ($tmpdatafield <> $filterdatafield && $tmpfilteroperator=='1'))
				{
					if ($tmpfilteroperator == 0)
					{
						$where .= " AND ";
					}
					else $where .= " OR ";	
				}
				
				// build the "WHERE" clause depending on the filter's condition, value and datafield.
				switch($filtercondition)
				{
					case "IS_NULL":
						$where .= "$filterdatafield IS NULL";
						break;
					case "NOT_NULL":
						$where .= "$filterdatafield IS NOT NULL";
						break;
					case "CONTAINS":
						$where .= "$filterdatafield LIKE '%$filtervalue%'";
						break;
					case "DOES_NOT_CONTAIN":
						$where .= "$filterdatafield NOT LIKE '%$filtervalue%'";
						break;
					case "EQUAL":
						($this->Filter[$i]["filtervaluestring"]=='0' && $this->Filter[$i]["filtervaluestring"]!='') ? $filtervalue = "'".$filtervalue."'" : $filtervalue = $filtervalue ;
						$where .= "$filterdatafield = '$filtervalue'";
						break;
					case "NOT_EQUAL":
						($this->Filter[$i]["filtervaluestring"]=='0' && $this->Filter[$i]["filtervaluestring"]!='') ? $filtervalue = "'".$filtervalue."'" : $filtervalue = $filtervalue ;
						$where .= "$filterdatafield <> $filtervalue";
						break;
					case "GREATER_THAN":
						($this->Filter[$i]["filtervaluestring"]=='0' && $this->Filter[$i]["filtervaluestring"]!='') ? $filtervalue = "'".$filtervalue."'" : $filtervalue = $filtervalue ;
						$where .= "$filterdatafield > $filtervalue";
						break;
					case "LESS_THAN":
						($this->Filter[$i]["filtervaluestring"]=='0' && $this->Filter[$i]["filtervaluestring"]!='') ? $filtervalue = "'".$filtervalue."'" : $filtervalue = $filtervalue ;
						$where .= "$filterdatafield < $filtervalue";
						break;
					case "GREATER_THAN_OR_EQUAL":
						($this->Filter[$i]["filtervaluestring"]=='0' && $this->Filter[$i]["filtervaluestring"]!='') ? $filtervalue = "'".$filtervalue."'" : $filtervalue = $filtervalue ;
						$where .= "$filterdatafield >= $filtervalue";
						break;
					case "LESS_THAN_OR_EQUAL":
						($this->Filter[$i]["filtervaluestring"]=='0' && $this->Filter[$i]["filtervaluestring"]!='') ? $filtervalue = "'".$filtervalue."'" : $filtervalue = $filtervalue ;
						$where .= "$filterdatafield <= $filtervalue";
						break;
					case "STARTS_WITH":
						$where .= "$filterdatafield LIKE '$filtervalue%'";
						break;
					case "ENDS_WITH":
						$where .= "$filterdatafield LIKE '%$filtervalue'";
						break;
					case "NOT_IN":
						$where .= "$filterdatafield NOT IN $filtervalue";
						break;
					case "IN":
						$where .= "$filterdatafield IN $filtervalue";
						break;
					case "BETWEEN":
						$val = explode(',',$filtervalue);
						$val1 = $val[0];
						$val2 = $val[1];
						$where .= "$filterdatafield BETWEEN $val1 AND $val2";
						break;
				}
								
				if ($i == $filterscount - 1)
				{
					$where .= ")";
				}
				
				$tmpfilteroperator = $filteroperator;
				$tmpdatafield = $filterdatafield;			
			}
			
			$sqlCount = "SELECT COUNT(*) AS jml FROM $nmTable $clauseJoin $where ";
			$arrCount = SimakConf::queryAction($sqlCount,'S');
			foreach($arrCount as $rowCount) 
			{
				$TotalRows = $rowCount['jml'];
			}
			
			if($this->UnionSql)
			{
				//$t = "UNION ALL";
				$sqlOrder = "";
				$sqlUnion= "UNION ALL ".$this->UnionSql;
				$arrCountUnion = SimakConf::queryAction($this->UnionSql,'S');
				//foreach($arrCountUnion as $rowCount) 
				//{
					$TotalRows += count($arrCountUnion);
				//}
			}
			
			$sql = "SELECT $field $fieldJoin FROM $nmTable $clauseJoin $where $groupBy $sqlUnion $sqlOrder $limit ";	
		}
		else
		{
			$sqlCount = "SELECT COUNT(*) AS jml FROM $nmTable $clauseJoin ";
			$arrCount = SimakConf::queryAction($sqlCount,'S');
			foreach($arrCount as $rowCount) 
			{
				$TotalRows = $rowCount['jml'];
			}
			
			if($this->UnionSql)
			{
				//$t = "UNION ALL";
				$sqlOrder = "";
				$sqlUnion= "UNION ALL ".$this->UnionSql;
				$arrCountUnion = SimakConf::queryAction($this->UnionSql,'S');
				//foreach($arrCountUnion as $rowCount) 
				//{
					$TotalRows += count($arrCountUnion);
				//}
			}
			
			$sql = "SELECT $field $fieldJoin FROM $nmTable $clauseJoin $where $groupBy $sqlUnion $sqlOrder $limit ";	
		}
		
		$Rows = SimakConf::queryAction($sql,'S');
		
		$data = array(
			'TotalRows' => $TotalRows,
			'Rows' => $Rows,
			'sql' => $sql,
			'sqlUnion' => $sqlUnion
		);
		/*$data[] = array(
			'PageSize' => $this->PageSize,
			'PageNum' => $this->PageNum,
			'Tablename' => $this->Tablename,
			'SelectField' => $this->SelectField,
			'SelectJoinField' => $this->SelectJoinField,
			'ClauseJoinField' => $this->ClauseJoinField,
			'Filter' => $this->Filter,
			'SortExpression' => $this->SortExpression,
			'Limit' => $limit,
			'TotalRows' => $TotalRows,
			'Rows' => $Rows,
			'sql' => $sql,
		);*/
		
		return $data;
	}
	
	public function getDataArr()
	{
		return $this->getData();
	}
	
	public function getDataJson()
	{
		return $this->jsonEncode($this->getData());
	}
	
	public static function jsonEncode($value)
	{
		if (function_exists('json_encode')) {
			return json_encode($value);
		}
		else {
			if(is_null(self::$_json))
				self::$_json = Prado::createComponent('System.Web.Javascripts.TJSON');
			return self::$_json->encode($value);
		}
	}
	
	public static function jsonDecode($value)
	{
		if (function_exists('json_decode')) return json_decode($value);
		else {
			if(is_null(self::$_json))
				self::$_json = Prado::createComponent('System.Web.Javascripts.TJSON');
			return self::$_json->decode($value);
		}
	}
}
?>
