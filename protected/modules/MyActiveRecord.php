<?php
class MyActiveRecord extends TActiveRecord 
{
	public $insert_log = false;
	public $return_query = false;
	public $db_conn;
	public $created_by;
	public $created_on;
	public $updated_by;
	public $updated_on;
	public $deleted_by;
	public $deleted_on;
	public $deleted = '0';
	public $description;
	
	public $document_type_id;
	public $document_no_digits = 5;
	
	public $id_parent;
	public $list_item_only_parent = true;
	public $list_item_assignto;
	public $list_item_limit;
	public $list_item_query;
	
	public $form_id;
	public $form_html;
	public $form_submit_callback;
	public $form_cancel_callback;
	public $set_last_insert_id;
		
	public function bindDataList($obj,$param)
	{
		$obj->id = $param['id_edit'];
		$obj->id_parent = $param['id_parent'];
		$obj->list_item_assignto = $param['list_item_assignto'];
		$obj->list_item_limit = $param['list_item_limit'];
		
		if(isset($param['list_item_only_parent']))
			$obj->list_item_only_parent = $param['list_item_only_parent'];
		else
			$obj->list_item_only_parent = true;
			
		$obj->listItems;
	}
	
	public function attributeLabels()
	{
		return array();
	}
	
	public function attributeMsgRequired()
	{
		return array();
	}
	
	public function getFormId() 
	{
		return $this->form_id;
	}
	
	public function setFormSubmitCallBack($form_submit_callback) 
	{
		return $this->form_submit_callback = $form_submit_callback;
	}
	
	public function getFormSubmitCallBack() 
	{
		return $this->form_submit_callback;
	}
	
	public function setFormCancelCallBack($form_cancel_callback) 
	{
		return $this->form_cancel_callback = $form_cancel_callback;
	}
	
	public function getFormCancelCallBack() 
	{
		return $this->form_cancel_callback;
	}
	
	public function getFormHtml()
	{		
		$obj = $this;
		$tableInfo = $obj::getRecordTableInfo();
		$pks = $tableInfo->getPrimaryKeys();
		$properties = $tableInfo->getColumns()->getKeys();
		$colums = $tableInfo->getColumns();
		
		$arrFK = $tableInfo->getForeignKeys();
		$attributeLabels = $obj::attributeLabels();
		$attributeMsgRequired = $obj::attributeMsgRequired();
		$excludeFieldForRender = array('created_by','created_on','updated_by','updated_on','left','right','st_active');
		$this->form_id = SimakConf::makeRandID();
		
		$html = '<div id="'.$this->form_id.'" class="form">';
		$html .= '<div class="form-body">';
		$html .= '<div class="alert alert-danger display-hide"><button class="close" data-close="alert"></button>You have some form errors. Please check below.</div>';
		$html .= '<div class="alert alert-success display-hide"><button class="close" data-close="alert"></button>Your form validation is successful!</div>';
		$html .= '<div class="row">';
		
		foreach($colums as $fieldName=>$fieldInfo)
		{
			if($fieldInfo->AllowNull == true || $fieldName == 'deleted')
			{
				$requiredClass = '';
				$dataRuleRequired = '';
			}
			else
			{
				$requiredClass = '<span class="required">*</span>';
				$dataRuleRequired = 'true';
				if(isset($attributeMsgRequired[$fieldName]))
					$dataMsgRequired = $attributeMsgRequired[$fieldName];
				else
					$dataMsgRequired = 'This field is required.';
			}
			
			$fieldType = $fieldInfo->DbType;
			
			if(in_array($fieldName,$excludeFieldForRender)==false)
			{
				if($fieldInfo->IsPrimaryKey === true)
				{
					$html .= '<input id="'.$fieldName.'" type="hidden" name="'.$fieldName.'" fieldname="'.$fieldName.'" value="'.$obj->$fieldName.'" primary-key>';
				}
				else
				{
					$html .= '<div class="col-xs-6 col-sm-6 col-md-4">';
					$html .= '<div class="form-group">';
					
					if($fieldName != 'deleted')
					{
						$html .= '<label id="'.$fieldName.'_label" class="control-label" for="'.$fieldName.'">'.$attributeLabels[$fieldName].' '.$requiredClass.'</label>';
						
						if($fieldInfo->IsForeignKey === true)
						{
							$html .= '<input id="'.$fieldName.'" class="form-control select2" type="hidden" name="'.$fieldName.'" fieldname="'.$fieldName.'" value="'.$obj->$fieldName.'" data-rule-required="'.$dataRuleRequired.'" data-msg-required="'.$dataMsgRequired.'">';
							foreach($arrFK as $rowKeyFK=>$rowValueFK)
							{
								foreach($rowValueFK['keys'] as $fieldNameFK=>$fieldNameTableFK)
								{
									if($fieldNameFK == $fieldName)
									{
										$arrForSelect2[] = array('table_name'=>$rowValueFK['table'],'el'=>$fieldNameFK);
										break;
									}
								}
							}
						}
						else	
						{
							if($fieldInfo->DbType == 'decimal')
							{
								$maskClass = 'mask_decimal';
								$value = explode('.',$obj->$fieldName);
								(intval($value[1]) == 0 ? $value = $value[0] : $value = SimakConf::formatCurrency($obj->$fieldName,2));
							}
							elseif($fieldInfo->DbType == 'int')
							{
								$maskClass = 'mask_integer';
								$value = SimakConf::formatCurrency($obj->$fieldName,'0');
							}
							else
							{
								$maskClass = '';	
								$value = $obj->$fieldName;
							}
							$html .= '<input id="'.$fieldName.'" class="form-control '.$maskClass.'" type="text" name="'.$fieldName.'" fieldname="'.$fieldName.'" value="'.$value.'" data-rule-required="'.$dataRuleRequired.'" data-msg-required="'.$dataMsgRequired.'">';
							//$html .= '<span class="help-block">&nbsp;</span>';
						}
					}
					else
					{
						($obj->$fieldName == '0' ? $checked = 'checked="checked"' : $checked = '');
						$html .= '<label class="control-label">&nbsp;</label>';
						$html .= '<div class="radio-list"><span id="'.$fieldName.'_span" class="radio-inline deleted" fieldname="'.$fieldName.'"><input id="'.$fieldName.'" type="checkbox" '.$checked.' name="'.$fieldName.'" value="'.$obj->$fieldName.'" class="checkbox-deleted"> <label id="'.$fieldName.'_label" for="'.$fieldName.'">'.$attributeLabels[$fieldName].'</label></span></div>';
					}
					
					$html .= '</div>';
					$html .= '</div>';
				}
			}
		}
		
		$html .= '</div></div>';
		$html .= '<div class="form-actions right margin-top-0">
								<div class="col-md-12">
									<button id="'.$this->form_id.'_submitBtn" data-formid="'.$this->form_id.'" class="btn green" type="button" >Submit</a>
									<button id="'.$this->form_id.'_cancelBtn" class="btn default cancelBtn" type="button">Back</button>
								</div>
							</div>';
		$html .= '</div>';					
		
		$html .= '<script type="text/javascript">
							//<![CDATA[
							jQuery(document).ready(function () {';
			
		foreach($arrForSelect2 as $k=>$v)
		{
			$html .= 'jQuery("#'.$v['el'].'").makeSelect2({table_name:"'.$v['table_name'].'"});';
		}
		
		$html .= 'jQuery("#'.$this->form_id.'_submitBtn").click(function() { 
								jQuery(this).formValidate({
									submitBtn:this,
									formCallback:'.$this->FormSubmitCallBack.',
									mainForm:'.Prado::getApplication()->getService()->getRequestedPage()->getPage()->Master->MainForm->getClientID().'
									});
							});';
		
		$html .= 'jQuery("#'.$this->form_id.'_cancelBtn").click(function() { clearForm("#'.$this->form_id.'"); '.$this->FormCancelCallBack.'});';
		$html .= 'jQuery(".mask_decimal").inputmask("decimal", {rightAlignNumerics: true,clearMaskOnLostFocus: true});';
		$html .= 'jQuery(".mask_integer").inputmask("integer", {rightAlignNumerics: true,clearMaskOnLostFocus: true});';
		$html .= '});//]]></script>';
			
		return $this->form_html = $html;
	}
	
	public function makeDocumentNo($param)
	{
		$digits = $this->document_no_digits;
		
		for($i=1;$i<=$digits;$i++)
		{
			$tmpDigit .= '0';
		}	
			
		$select_field = $param["select_field"];
		$where = $param["where"];
		$document_type_code = DocumentTypeRecord::finder()->findByPk($this->document_type_id)->code;
		
		$sql = "SELECT $select_field FROM ".$this::TABLE." ";
		if($where)
			$sql .= "WHERE $where ";
		$sql .= "ORDER BY $select_field DESC LIMIT 0,1";
		$record = $this->finder()->findBySql($sql);
		
		if($record==NULL)
		{
			$urut = 1;
			$tmp = $document_type_code."/".date("Y")."/".date("m")."/".substr($tmpDigit,-$digits,$digits-strlen($urut)).$urut;
		}
		else
		{
			$columnValue = $record->getColumnValue($select_field);
			$lastCount = intval(substr($columnValue,-($digits),$digits));
			$lastMonth = substr($columnValue,(-$digits-3),2);
			$month = date("m");
			
			if($lastMonth==$month)
			{
				$urut = $lastCount + 1;
				$tmp = substr($columnValue,0,(strlen($columnValue)-$digits)).substr($tmpDigit,-$digits,$digits-strlen($urut)).$urut;
			}
			else
			{
				$urut = 1;
				$tmp = $document_type_code."/".date("Y")."/".date("m")."/".substr($tmpDigit,-$digits,$digits-strlen($urut)).$urut;
			}
		}
		return $tmp;
	}
	
	public function makeNoAgendaSurat($param)
	{
		$digits = $this->document_no_digits;
		
		for($i=1;$i<=$digits;$i++)
		{
			$tmpDigit .= '0';
		}	
			
		$select_field = $param["select_field"];
		$where = $param["where"];
		
		$sql = "SELECT $select_field,tgl FROM ".$this::TABLE." ";
		if($where)
			$sql .= "WHERE $where ";
		$sql .= "ORDER BY $select_field DESC LIMIT 0,1";
		$record = $this->finder()->findBySql($sql);
		
		if($record==NULL)
		{
			$urut = 1;
			$tmp = substr($tmpDigit,-$digits,$digits-strlen($urut)).$urut;
		}
		else
		{
			$columnValue = $record->getColumnValue($select_field);
			$lastCount = intval(substr($columnValue,-($digits),$digits));
			$lastYear = substr($record->getColumnValue("tgl"),0,4);
			$year = date("Y");
			
			if($lastYear==$year)
			{
				$urut = $lastCount + 1;
				$tmp = substr($tmpDigit,-$digits,$digits-strlen($urut)).$urut;
			}
			else
			{
				$urut = 1;
				$tmp = substr($tmpDigit,-$digits,$digits-strlen($urut)).$urut;
			}
		}
		return $tmp;
	}

	public function getIsStateNew(){
		return ($this->_recordState==self::STATE_NEW);
	}
	
	public function getIsStateLoaded(){
		return ($this->_recordState==self::STATE_LOADED);
	}
}
?>
