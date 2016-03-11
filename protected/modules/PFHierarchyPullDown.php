<?php
class PFHierarchyPullDown extends SimakConf
{
	//put your code here

    private $StructureTable="";
    private $StructurePath="";
    private $RecordClassFinder;
    private $AllRecords;
    private $HasPrevious=array(); //puffer for the information if the node has an parent or not...
    private $PKField="id";
    private $PPKField="id_parent";
    private $BasisElements=array();
    private $FieldName = ""; //the name of the field, to be displayed in the listbox
    private $FieldCategories = array();
    private $Fields = array();
    public $myTree = array(); //inside this var, the complete tree is stored, will be the datasource for the ListBox
    private $SQLCondition = "";

    public function setStructureTable($TableName){
        $this->StructureTable = $TableName;
    }

    public function setRecordClass($RecordObjFinder){
        $this->RecordClassFinder = $RecordObjFinder;
    }

    public function setPKField($primaryKey){
        $this->PKField = $primaryKey;
        $this->PPKField = $primaryKey."_parent";
    }

    public function setField($FieldName){
        $this->FieldName = $FieldName;
    }

    public function setSQLCondition($SQLCondition){
        $this->SQLCondition = $SQLCondition;
    }

    public function run(){
        $mySQL = "SELECT ".$this->PKField.",".$this->PPKField.",".$this->FieldName." FROM ".$this->StructureTable;
        if($this->SQLCondition!=''){
            $mySQL.= " WHERE ".$this->SQLCondition;
        }
        $this->AllRecords = $this->RecordClassFinder->findAllBySQL($mySQL);
        $this->calc_Forward();
        //print_r($this->BasisElements);
    }

    public function calc_Forward(){
        foreach($this->AllRecords As $Record){
            $this->Fields[$Record->{$this->PKField}] = $Record->{$this->FieldName};
            $this->FieldCategories[$Record->{$this->PKField}] = $Record->{$this->PPKField};
        }
        $hierarchy = $this->hierarchize($this->FieldCategories, 0);
        $this->display_options($hierarchy, $this->Fields);
    }

    private function hierarchize($cats,$parent){
        $subs = array_keys($cats, $parent);
        $tree = array();
        foreach ($subs as $sub) {
            $tree[$sub] = $this->hierarchize($cats, $sub);
        }
        return count($tree) ? $tree : $parent;
    }

    function display_options($tree, $names, $nest = 0) {
        foreach ($tree as $key => $branch) {
            $indent = $nest ? str_repeat('--', $nest) . '| ' : '';
            $this->myTree[$key]=$indent.$names[$key];
            if (is_array($branch)) {
                $this->display_options($branch, $names, $nest + 1);
            }
        }
    }
}



?>
