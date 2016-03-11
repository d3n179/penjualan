<?php
class UnitKerjaJson extends TJsonResponse
{
	private $_bar;
	protected $routeList = array();
	public function setBar($value)
	{
		$this->_bar = $value; //we can set properties in the <json> config
	}
	
	public function dataParent($clause = '')
	{
		$organization = Prado::getApplication()->User->Organization;
		$org_left = Prado::getApplication()->User->OrgLeft;
		$org_right = Prado::getApplication()->User->OrgRight;
		
		if($this->Request['mode']=='UserGroup')
		{
			$table="view_user_group";
			$sql = "SELECT * FROM view_user_group WHERE deleted='0' AND id>1  ";
		}
		elseif($this->Request['mode']=='Roles' || $this->Request['mode']=='Permissions')
		{
			if($this->Request['mode']=='Roles')
			{
				$table="tbm_rbac_roles";
				foreach($this->User->UserRoles as $row)
				{
					if(!$roles[$row['id']])
						$roles[$row['id']] = 'node.id='.$row['id'];
				}
				
				$rolesClause = "AND (".implode(' OR ',$roles).")";
			}
			else
				$table="tbm_rbac_permissions";
			
			$sql = "select
								node.ID AS id,
								node.Lft AS 'left',
								node.Rght AS 'right',
								node.deleted AS deleted,
								(((node.Rght - node.Lft) - 1) / 2) AS child_count,
								UPPER(node.Title) AS name,
								(count(parent.Title) - 1) AS depth,
								(
									select
										t2.id
									from
										$table t2
									where
										((t2.Lft < node.Lft) and (t2.Rght > node.Rght) and (t2.deleted='0'))
									order by
										(t2.Rght - node.Rght)
									limit
										1
								) AS id_parent
							from
								($table node
								join $table parent)
							where
								-- (node.Lft between parent.Lft and parent.Rght) AND parent.deleted = '0' AND node.deleted = '0' AND node.ID >= '1' 
								parent.deleted = '0' AND node.deleted = '0' AND node.ID >= '1' ".$rolesClause."
							group by
								node.id
							".$clause."	
							order by
								node.Lft,
								node.Title  ";
		}
		elseif($this->Request['mode']=='Menu')
		{
			$table="view_menu";
			$applicationId = $this->Request['applicationId'];
			$sql = "SELECT * FROM view_menu WHERE id > 0 AND deleted='0' AND application='$applicationId' ".$clause."	 ORDER BY view_menu.left";
		}
		elseif($this->Request['mode']=='ProductCategory')
		{
			$table="view_product_category";
			$sql = "SELECT  view_product_category.*
					FROM view_product_category INNER JOIN view_organization ON (view_product_category.organization_id=view_organization.id)
					WHERE view_product_category.id > 0 AND view_product_category.deleted='0' AND view_product_category.parent_deleted='0' AND view_product_category.organization_deleted='0' 
					AND (view_organization.id='$organization' OR view_organization.id_parent='$organization' OR view_organization.id='1') ".$clause." GROUP BY view_product_category.id ORDER BY view_product_category.`left`  ";
		}
		elseif($this->Request['mode']=='division')
		{
			$table="view_division";
			$sql = "SELECT view_division.* FROM view_division INNER JOIN view_organization ON (view_division.organization_id=view_organization.id)  
					WHERE view_division.id > 0 AND view_division.deleted='0' and view_division.parent_deleted='0' and view_division.organization_deleted='0' 
					AND (view_organization.`left`>='$org_left' AND view_organization.`right`<='$org_right') ".$clause." GROUP BY view_division.id ORDER BY view_division.`left`";	
		}
		
		elseif($this->Request['mode']=='position')
		{
			$table="view_position";
			$sql = "SELECT view_position.* FROM view_position INNER JOIN view_organization ON (view_position.organization_id=view_organization.id) 
					WHERE view_position.id > 0 AND view_position.deleted !='1' AND view_position.parent_deleted !='1' AND view_position.division_deleted !='1' AND view_position.organization_deleted !='1' 
					AND (view_organization.`left`>='".$org_left."' AND view_organization.`right`<='".$org_right."') ".$clause." GROUP BY view_position.id ORDER BY view_position.`left`";			
		}
			
		elseif($this->Request['mode']=='region')
			$sql = "SELECT * FROM view_region WHERE id > 0 AND deleted='0' and id>0 and parent_deleted ='0' and country_deleted ='0' GROUP BY id ORDER BY `left`";			
		
		elseif($this->Request['mode']=='Organization')
		{
			foreach($this->User->OrgList as $row)
			{
				if(!$org[$row['id']])
					$org[$row['id']] = 'id='.$row['id'];
			}
			
			$sql = "SELECT * FROM view_organization WHERE deleted='0' AND (".implode(' OR ',$org).") ".$clause." ORDER BY view_organization.left";
			
			$table="view_organization";
			/*$sql = "SELECT * FROM view_organization 
				WHERE deleted='0' AND `left`>='".$org_left."' AND `right`<='".$org_right."' ".$clause." GROUP BY id ORDER BY `left`";*/
			//$sql = "SELECT * FROM view_organization WHERE deleted='0' AND parent_deleted='0' AND `left`>='".$org_left."' AND `right`<='".$org_right."' ".$clause." GROUP BY id ORDER BY `left`";
		}
		else
		{
			$table="view_unit_kerja";
			$sql = "SELECT * FROM view_unit_kerja WHERE deleted='0' GROUP BY id ORDER BY `left`";
		}	
		return array("table"=>$table,"sqlResult"=>PageConf::queryAction($sql,'S'));
		
	}

	public function buatMenu($idParent=0, $clause='')
	{
		$arr = $this->dataParent($clause);
		
		foreach($arr["sqlResult"] as $row) 
		{
			//var_dump($idParent.' - '.$row['id_parent']);
			$id = $row['id'];
			$left = $row['left'];
			$right = $row['right'];
			$child_count = $row['child_count'];
			$id_parent = $row['id_parent'];
			$title = $row['name'];
			
			if($child_count > 0)
				$isFolder = false;
			else
				$isFolder = false;	
			
			if($row['id_parent']==NULL)
				$idParent = $row['id_parent'];
				
			if($row['id_parent'] == $idParent)
			{
				if($child_count > 0)
				{
					if($this->Request['mode']=='Roles' || $this->Request['mode']=='Permissions')
						$data[] = array('id'=>$id,'title'=> $title,'isFolder'=>$isFolder,'key'=>$id,'children'=>$this->buatMenu($id," HAVING id_parent = '$id' "));
					else	
						$data[] = array('id'=>$id,'title'=> $title,'isFolder'=>$isFolder,'key'=>$id,'children'=>$this->buatMenu($id," AND ".$arr["table"].".id_parent = '$id' "));
				}	
				else
				{
					$data[] = array('id'=>$id,'title'=> $title,'isFolder'=>$isFolder,'key'=>$id);	
				}
			}
		}
		
		return $data;
	}
	
	public function select2DataList()
	{
		$Tablename = $this->Request['table_name'];
		$DataProvider = new DataProvider();
		
		if(isset($this->Request['page_limit']))
		{
			$DataProvider->LimitStatus = true;
			$DataProvider->PageSize = $this->Request['page_limit'];
		}
		
		if(isset($this->Request['page_number']))
			$DataProvider->PageNum = $this->Request['page_number']-1;
			
		if(isset($this->Request['field_id']) && trim($this->Request['field_id'])!='')
			$fieldId = $this->Request['field_id'];
		else
			$fieldId = $Tablename.'.id';
				
		if(isset($this->Request['field_name']) && trim($this->Request['field_name'])!='')
			$fieldName = $this->Request['field_name'];
		else
			$fieldName = $Tablename.'.name';
				
		if(isset($this->Request['field_groupby']) && trim($this->Request['field_groupby'])!='')
		{
			$exp = explode('.',$this->Request['field_groupby']);
			$GroupBy = (count($exp)>1)?$this->Request['field_groupby']:$Tablename.'.'.$this->Request['field_groupby'];
		}
		else
			$GroupBy = $fieldId;
				
		if(isset($this->Request['field_sortby']) && trim($this->Request['field_sortby'])!='')
			$SortExpression = $this->Request['field_sortby'];
		else
			$SortExpression = $Tablename.'.name ASC';
				
		$DataProvider->Tablename = $Tablename;
		
		if(isset($this->Request['field_id']) && trim($this->Request['field_id'])!='')
			$DataProvider->SelectField = $this->Request['field_id']." AS id, $fieldName AS text";
		else	
			$DataProvider->SelectField = "$Tablename.id, $fieldName AS text";
		
		if(is_array($this->Request['field_additional']) && count($this->Request['field_additional'])>0)
		{
			foreach($this->Request['field_additional'] as $row)
			{
				$field_additional[]= $row;
			}
			
			$DataProvider->SelectField .= ",".implode(",",$field_additional);
		}
		
		if($this->Request['field_join'])
			$DataProvider->SelectJoinField = $this->Request['field_join'];
		else	
			$DataProvider->SelectJoinField = "";
		
		if($this->Request['table_join'])
			$DataProvider->ClauseJoinField = $this->Request['table_join'];
		else	
			$DataProvider->ClauseJoinField = "";
			
		if($this->Request['field_having'])
			$DataProvider->Having = $this->Request['field_having'];
		else	
			$DataProvider->Having = "";
			
		$DataProvider->GroupBy = "$GroupBy";
		$DataProvider->SortExpression = "$SortExpression";
		
		//$DataProvider->Filter[] = array('filterdatafield'=>$Tablename.'.deleted','filtercondition'=>'EQUAL','filtervalue'=>"0",'filteroperator'=>'0');
		
		if(isset($this->Request['id']) && trim($this->Request['id'])!='' && $this->Request['multiple']=="false")
			$DataProvider->Filter[] = array('filterdatafield'=>$fieldId,'filtercondition'=>'EQUAL','filtervalue'=>$this->Request['id'],'filteroperator'=>'0');
		elseif(isset($this->Request['id']) && trim($this->Request['id'])!='' && $this->Request['multiple']=="true")
			$DataProvider->Filter[] = array('filterdatafield'=>$fieldId,'filtercondition'=>'IN','filtervalue'=>"(".$this->Request['id'].")",'filteroperator'=>'0');
			
		if(isset($this->Request['q']) && trim($this->Request['q'])!='')
			$DataProvider->Filter[] = array('filterdatafield'=>$fieldName,'filtercondition'=>'CONTAINS','filtervalue'=>$this->Request['q'],'filteroperator'=>'0');
		
		if(is_array($this->Request['custom_query']) && count($this->Request['custom_query'])>0)
		{
			foreach($this->Request['custom_query'] as $row)
			{
				if($row[3])
					$filteroperator = $row[3];
				else	
					$filteroperator = '0';
					
				if($row[4])
					$filtermysqlescapedisable = $row[4];
				else	
					$filtermysqlescapedisable = '0';
					
				if($row[5])
					$filtervaluestring = $row[5];
				else	
					$filtervaluestring = '0';
					
				$DataProvider->Filter[] = array('filterdatafield'=>$row[0],'filtercondition'=>$row[1],'filtervalue'=>$row[2],'filteroperator'=>$filteroperator,'filtermysqlescapedisable'=>$filtermysqlescapedisable,'filtervaluestring'=>$filtervaluestring);
			}
		}
			
		if($this->Request['sql_union']!='false' && $this->Request['sql_union']!='')
			$DataProvider->UnionSql = $this->Request['sql_union'];
		
		$data = $DataProvider->getDataArr();
		
		return $data;
	}
	
	public function productImages()
	{
		$Tablename = "tbm_product_images";
		$DataProvider = new DataProvider();
		$DataProvider->LimitStatus = false;
		$DataProvider->Tablename = $Tablename;
		$DataProvider->SelectField = "$Tablename.id,
										$Tablename.product_id,
										$Tablename.file_size,
										$Tablename.file_type,
										$Tablename.name,
										$Tablename.description,
										$Tablename.st_default,
										$Tablename.deleted";
		$DataProvider->SelectJoinField = "";
		$DataProvider->ClauseJoinField = "";
		$DataProvider->GroupBy = "$Tablename.id";
		
		$DataProvider->Filter[] = array('filterdatafield'=>$Tablename.'.deleted','filtercondition'=>'EQUAL','filtervalue'=>'0','filteroperator'=>'0');
		
		if($this->Request['product_id']!='')
			$DataProvider->Filter[] = array('filterdatafield'=>$Tablename.'.product_id','filtercondition'=>'EQUAL','filtervalue'=>$this->Request['product_id'],'filteroperator'=>'0');
			
		$DataProvider->SortExpression = "$Tablename.st_default DESC, $Tablename.id";
		$data = $DataProvider->getDataArr();
			
		$pathImage = dirname($_SERVER['REQUEST_URI']).'/gallery/Product/';
		$i=0;
		foreach($data['Rows'] as $k=>$v)
		{
			$files[$i]['name'] = $v['name']; 
			$files[$i]['img_description'] = $v['description']; 
			$files[$i]['size'] = PageConf::convertFileSize($v['file_size']); 
			$files[$i]['type'] = $v['file_type']; 
			$files[$i]['st_default'] = $v['st_default']; 
			$files[$i]['url'] = $pathImage.$v['name']; 	
			$files[$i]['thumbnailUrl'] = $pathImage.'thumbnail/'.$v['name']; 	
			$files[$i]['deleteUrl'] = $pathImage.'?file='.$v['name'].'&_method=DELETE'; 	
			$files[$i]['deleteType'] = 'POST'; 
			$i++;
		}
		
		return array('files'=>$files);
	}
	
	public function organizationImages()
	{
		$Tablename = "tbm_organization_images";
		$DataProvider = new DataProvider();
		$DataProvider->LimitStatus = false;
		$DataProvider->Tablename = $Tablename;
		$DataProvider->SelectField = "$Tablename.id,
										$Tablename.organization_id,
										$Tablename.file_size,
										$Tablename.file_type,
										$Tablename.name,
										$Tablename.description,
										$Tablename.st_default,
										$Tablename.deleted";
		$DataProvider->SelectJoinField = "";
		$DataProvider->ClauseJoinField = "";
		$DataProvider->GroupBy = "$Tablename.id";
		
		$DataProvider->Filter[] = array('filterdatafield'=>$Tablename.'.deleted','filtercondition'=>'EQUAL','filtervalue'=>'0','filteroperator'=>'0');
		
		if($this->Request['organization_id']!='')
			$DataProvider->Filter[] = array('filterdatafield'=>$Tablename.'.organization_id','filtercondition'=>'EQUAL','filtervalue'=>$this->Request['organization_id'],'filteroperator'=>'0');
			
		$DataProvider->SortExpression = "$Tablename.st_default DESC, $Tablename.id";
		$data = $DataProvider->getDataArr();
			
		$pathImage = dirname($_SERVER['REQUEST_URI']).'/gallery/Organization/';
		$i=0;
		foreach($data['Rows'] as $k=>$v)
		{
			$files[$i]['name'] = $v['name']; 
			$files[$i]['img_description'] = $v['description']; 
			$files[$i]['size'] = PageConf::convertFileSize($v['file_size']); 
			$files[$i]['type'] = $v['file_type']; 
			$files[$i]['st_default'] = $v['st_default']; 
			$files[$i]['url'] = $pathImage.$v['name']; 	
			$files[$i]['thumbnailUrl'] = $pathImage.'thumbnail/'.$v['name']; 	
			$files[$i]['deleteUrl'] = $pathImage.'?file='.$v['name'].'&_method=DELETE'; 	
			$files[$i]['deleteType'] = 'POST'; 
			$i++;
		}
		
		return array('files'=>$files);
	}
	
	public function activeRecordTable()
	{
		if($this->Request['modeList']=='pageroute')
		{
			if($this->Request['dir'])
				$dir = $this->Request['dir'];
				
			if($this->Request['dirExclude'])
				$dirExclude = $this->Request['dirExclude'];
				
			if($this->Request['fileExclude'])
				$fileExclude = $this->Request['fileExclude'];
				
			if($this->Request['fileExt'])
				$fileExt = $this->Request['fileExt'];
			
			$this->dirToArray(array('dir'=>$dir,'dirExclude'=>$dirExclude,'fileExt'=>$fileExt,'fileExclude'=>$fileExclude));
			$data = $this->routeList;
		}
		else
		{
			$dir = realpath('protected/database');
			$dirExclude = array('Wsdl','ErrorTemplates','Test','Tmp','.','..');
			$fileExclude = array('Forbidden.page');
			$fileExt = array('php');
				
			$r = PageConf::dirToArray(array('dir'=>$dir,'dirExclude'=>$dirExclude,'fileExt'=>$fileExt,'fileExclude'=>$fileExclude));
			$data = [];
			$i=0;
			foreach($r as $ar)
			{
				$tableName = $ar::TABLE;
				$data[] = array('id'=>$tableName,'text'=>$tableName);
			}
		}
		
		return $data;
	}
	
	public function getTransApotik()
	{
		$jns_pasien = $this->Request['jns_pasien'];
		$no_trans = $this->Request['no_trans'];
		$satelit = $this->Request['satelit'];
		$st_asuransi = $this->Request['st_asuransi'];
		$id_perusahaan_asuransi = $this->Request['id_perusahaan_asuransi'];
		$id_depo = $this->Request['id_depo'];
		
		if($jns_pasien=='0'){
			$sql = "
			SELECT
				tbt_obat_jual.id,
				tbt_obat_jual.id_obat AS nama,
				tbt_obat_jual.no_reg AS no_reg,
				tbt_obat_jual.id_obat AS id_obat_old,
				tbt_obat_jual.id_harga AS id_harga,
				tbt_obat_jual.tujuan AS tujuan,
				tbt_obat_jual.expired AS expired,
				FORMAT(SUM(tbt_obat_jual.jumlah),0) AS jml_ambil_old,
				FORMAT(SUM(tbt_obat_jual.jumlah),0) AS jml_ambil,
				tbt_obat_harga.hrg_netto AS hrg_netto,
				tbt_obat_harga.hrg_ppn AS hrg_ppn,
				tbt_obat_harga.hrg_netto_disc AS hrg_netto_disc,
				tbt_obat_harga.hrg_ppn_disc AS hrg_ppn_disc,
				(t.persentase/100) AS persentase,
				(t.persentase_asuransi/100) AS persentase_asuransi,
				(t.persen_tanggungan/100) AS persen_tanggungan,
				'0' AS disc_penjamin,
				t.hrg_khusus AS hrg_khusus,
				t.nm_satuan AS satuan,
				(t.jml_stok+FORMAT(SUM(tbt_obat_jual.jumlah),0)) AS jml_stok,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 2)) AS aturan_jml,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 3)) AS aturan_jangka,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 4)) AS aturan_sediaan_jml,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 5)) AS aturan_sediaan,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 6)) AS aturan_waktu,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 7)) AS aturan_periode,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 8)) AS aturan_carapakai
			FROM		
				(
					SELECT 
						tbm_obat.kode AS id, 
						tbm_obat.nama, 
						tbm_satuan_obat.nama AS nm_satuan, SUM(if(tbt_stok_lain.jumlah>0,IF(tbt_stok_lain.jumlah_ambil_pending>0,IF(tbt_stok_lain.jumlah_ambil_pending>tbt_stok_lain.jumlah,0,tbt_stok_lain.jumlah-tbt_stok_lain.jumlah_ambil_pending),tbt_stok_lain.jumlah),0)) AS jml_stok, 
						(SELECT MAX(id) FROM tbt_obat_harga WHERE kode=tbm_obat.kode) AS id_harga, 
						IFNULL((SELECT hrg_jual FROM tbm_obat_hrg_khusus WHERE id_obat = tbm_obat.kode),0) AS hrg_khusus, 
						tMargin.persentase AS persentase, 
						tMargin.persentase_asuransi AS persentase_asuransi, 
						IF('".$st_asuransi."'='1',(SELECT (nilai_tanggungan/100) FROM tbm_provider_detail_obat_cover WHERE id_provider='".$id_perusahaan_asuransi."' AND id_obat=tbm_obat.kode AND st_delete='0'),0) AS persen_tanggungan 
					FROM tbm_obat 
						INNER JOIN tbt_stok_lain ON (tbt_stok_lain.id_obat = tbm_obat.kode) 
						INNER JOIN tbm_obat_kelompok_margin ON (tbm_obat_kelompok_margin.id = tbm_obat.kel_margin) 
						INNER JOIN (SELECT persentase,persentase_asuransi,nama,satelit FROM tbm_obat_kelompok_margin) AS tMargin ON (tMargin.nama = tbm_obat_kelompok_margin.nama AND tMargin.satelit='".$satelit."') 
						LEFT JOIN tbm_satuan_obat ON (tbm_satuan_obat.kode = tbm_obat.satuan) 
					WHERE  
						tbm_obat.st_delete = '0' 
						AND tbt_stok_lain.st_delete = '0' 
						AND tbt_stok_lain.tujuan='".$id_depo."' 
					GROUP BY tbm_obat.kode ORDER BY tbm_obat.nama ASC
				) as t
			INNER JOIN tbt_obat_harga ON (tbt_obat_harga.id = t.id_harga)
			INNER JOIN tbt_obat_jual ON (tbt_obat_jual.id_obat = t.id)
			WHERE
				tbt_obat_jual.no_trans_rwtjln = '".$no_trans."'
				AND tbt_obat_jual.tujuan = '".$id_depo."'
				AND tbt_obat_jual.id_bhp != '999999999'
				AND tbt_obat_jual.flag = '0'
				AND tbt_obat_jual.st_delete = '0'
				AND tbt_obat_jual.id_kel_racik = '0'
				AND (tbt_obat_jual.no_reg_bayar IS NULL OR tbt_obat_jual.no_reg_bayar = '')
			GROUP BY
				tbt_obat_jual.id_obat
			ORDER BY tbt_obat_jual.id ";
			
			$dataNonRacikan = SimakConf::queryAction($sql,'S');	
		
			$sql = "
			SELECT
				tbt_obat_jual.id,
				tbt_obat_jual.id_obat AS nama,
				tbt_obat_jual.no_reg AS no_reg,
				tbt_obat_jual.id_obat AS id_obat_old,
				tbt_obat_jual.id_harga AS id_harga,
				tbt_obat_jual.tujuan AS tujuan,
				tbt_obat_jual.expired AS expired,
				FORMAT(SUM(tbt_obat_jual.jumlah),0) AS jml_ambil_old,
				FORMAT(SUM(tbt_obat_jual.jumlah),0) AS jml_ambil,
				tbt_obat_harga.hrg_netto AS hrg_netto,
				tbt_obat_harga.hrg_ppn AS hrg_ppn,
				tbt_obat_harga.hrg_netto_disc AS hrg_netto_disc,
				tbt_obat_harga.hrg_ppn_disc AS hrg_ppn_disc,
				(t.persentase/100) AS persentase,
				(t.persentase_asuransi/100) AS persentase_asuransi,
				(t.persen_tanggungan/100) AS persen_tanggungan,
				t.hrg_khusus AS hrg_khusus,
				t.nm_satuan AS satuan,
				(t.jml_stok+FORMAT(SUM(tbt_obat_jual.jumlah),0)) AS jml_stok,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 2)) AS aturan_jml,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 3)) AS aturan_jangka,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 4)) AS aturan_sediaan_jml,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 5)) AS aturan_sediaan,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 6)) AS aturan_waktu,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 7)) AS aturan_periode,
				(SELECT SPLIT_STR(tbt_obat_jual.aturan_pakai, '-', 8)) AS aturan_carapakai,
				tbt_obat_jual.id_kel_racik AS id_kel_racik,
				tbt_obat_jual.jml_bungkus AS jml_bungkus,
				tbt_obat_jual.id_kemasan AS id_kemasan
			FROM		
				(
					SELECT 
						tbm_obat.kode AS id, 
						tbm_obat.nama, 
						tbm_satuan_obat.nama AS nm_satuan, SUM(if(tbt_stok_lain.jumlah>0,IF(tbt_stok_lain.jumlah_ambil_pending>0,IF(tbt_stok_lain.jumlah_ambil_pending>tbt_stok_lain.jumlah,0,tbt_stok_lain.jumlah-tbt_stok_lain.jumlah_ambil_pending),tbt_stok_lain.jumlah),0)) AS jml_stok, 
						(SELECT MAX(id) FROM tbt_obat_harga WHERE kode=tbm_obat.kode) AS id_harga, 
						IFNULL((SELECT hrg_jual FROM tbm_obat_hrg_khusus WHERE id_obat = tbm_obat.kode),0) AS hrg_khusus, 
						tMargin.persentase AS persentase, 
						tMargin.persentase_asuransi AS persentase_asuransi, 
						IF('".$st_asuransi."'='1',(SELECT (nilai_tanggungan/100) FROM tbm_provider_detail_obat_cover WHERE id_provider='".$id_perusahaan_asuransi."' AND id_obat=tbm_obat.kode AND st_delete='0'),0) AS persen_tanggungan 
					FROM tbm_obat 
						INNER JOIN tbt_stok_lain ON (tbt_stok_lain.id_obat = tbm_obat.kode) 
						INNER JOIN tbm_obat_kelompok_margin ON (tbm_obat_kelompok_margin.id = tbm_obat.kel_margin) 
						INNER JOIN (SELECT persentase,persentase_asuransi,nama,satelit FROM tbm_obat_kelompok_margin) AS tMargin ON (tMargin.nama = tbm_obat_kelompok_margin.nama AND tMargin.satelit='".$satelit."') 
						LEFT JOIN tbm_satuan_obat ON (tbm_satuan_obat.kode = tbm_obat.satuan) 
					WHERE  
						tbm_obat.st_delete = '0' 
						AND tbt_stok_lain.st_delete = '0' 
						AND tbt_stok_lain.tujuan='".$id_depo."' 
					GROUP BY tbm_obat.kode ORDER BY tbm_obat.nama ASC
				) as t
			INNER JOIN tbt_obat_harga ON (tbt_obat_harga.id = t.id_harga)
			INNER JOIN tbt_obat_jual ON (tbt_obat_jual.id_obat = t.id)
			WHERE
				tbt_obat_jual.no_trans_rwtjln = '".$no_trans."'
				AND tbt_obat_jual.tujuan = '".$id_depo."'
				AND tbt_obat_jual.id_bhp != '999999999'
				AND tbt_obat_jual.flag = '0'
				AND tbt_obat_jual.st_delete = '0'
				AND tbt_obat_jual.id_kel_racik != '0'
				AND (tbt_obat_jual.no_reg_bayar IS NULL OR tbt_obat_jual.no_reg_bayar = '')
			GROUP BY
				tbt_obat_jual.id_kel_racik, tbt_obat_jual.id_obat 
			ORDER BY tbt_obat_jual.id_kel_racik, tbt_obat_jual.id ";
			
			$dataRacikan = [];
			$idKelRacik = '';
			$dataRacikanItems = SimakConf::queryAction($sql,'S');	
			$i=0;
			foreach($dataRacikanItems as $row){
				if($idKelRacik!=$row['id_kel_racik']){
					$idKelRacik = $row['id_kel_racik'];
					$dataRacikan[$i]['jml_ambil'] = $row['jml_bungkus'];
					$dataRacikan[$i]['kemasan'] = $row['id_kemasan'];
					$dataRacikan[$i]['aturan_jml'] = $row['aturan_jml'];
					$dataRacikan[$i]['aturan_jangka'] = $row['aturan_jangka'];
					$dataRacikan[$i]['aturan_sediaan_jml'] = $row['aturan_sediaan_jml'];
					$dataRacikan[$i]['aturan_sediaan'] = $row['aturan_sediaan'];
					$dataRacikan[$i]['aturan_waktu'] = $row['aturan_waktu'];
					$dataRacikan[$i]['aturan_periode'] = $row['aturan_periode'];
					$dataRacikan[$i]['aturan_carapakai'] = $row['aturan_carapakai'];
					$dataRacikan[$i]['SubGridData'] = [];
					$i++;
					$j=0;
				}
				
				$item = array(
					'id' => $row['id'],
					'nama' => $row['nama'],
					'jml_stok' => $row['jml_stok'],
					'satuan' => $row['satuan'],
					'jml_ambil' => $row['jml_ambil'],
					'hrg_netto' => $row['hrg_netto'],
					'hrg_ppn' => $row['hrg_ppn'],
					'hrg_netto_disc' => $row['hrg_netto_disc'],
					'hrg_ppn_disc' => $row['hrg_ppn_disc'],
					'hrg_khusus' => $row['hrg_khusus'],
					'persentase' => $row['persentase'],
					'persentase_asuransi' => $row['persentase_asuransi'],
					'persen_tanggungan' => $row['persen_tanggungan'],
					'disc_penjamin' => 0,
					'id_obat_old' => $row['id_obat_old'],
					'jml_ambil_old' => $row['jml_ambil_old'],
					'id_harga' => $row['id_harga'],
					'tujuan' => $row['tujuan'],
					'expired' => $row['expired'],
					'no_reg' => $row['no_reg'],
					'id_kel_racik' => $row['id_kel_racik']
				);
				
				array_push($dataRacikan[$i-1]['SubGridData'], $item);
				$j++;
			}
			
			if(count($dataNonRacikan)==0 && count($dataRacikan)==0){
				$sql = "
				SELECT
					'0' AS id,
					tbt_obat_jual_tunda.id_obat AS nama,
					tbt_obat_jual_tunda.no_reg AS no_reg,
					'0' AS id_obat_old,
					tbt_obat_jual_tunda.id_harga AS id_harga,
					tbt_obat_jual_tunda.tujuan AS tujuan,
					tbt_obat_jual_tunda.expired AS expired,
					FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil_old,
					FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil,
					tbt_obat_harga.hrg_netto AS hrg_netto,
					tbt_obat_harga.hrg_ppn AS hrg_ppn,
					tbt_obat_harga.hrg_netto_disc AS hrg_netto_disc,
					tbt_obat_harga.hrg_ppn_disc AS hrg_ppn_disc,
					(t.persentase/100) AS persentase,
					(t.persentase_asuransi/100) AS persentase_asuransi,
					(t.persen_tanggungan/100) AS persen_tanggungan,
					'0' AS disc_penjamin,
					t.hrg_khusus AS hrg_khusus,
					t.nm_satuan AS satuan,
					(t.jml_stok+FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0)) AS jml_stok,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 2)) AS aturan_jml,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 3)) AS aturan_jangka,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 4)) AS aturan_sediaan_jml,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 5)) AS aturan_sediaan,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 6)) AS aturan_waktu,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 7)) AS aturan_periode,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 8)) AS aturan_carapakai
				FROM		
					(
						SELECT 
							tbm_obat.kode AS id, 
							tbm_obat.nama, 
							tbm_satuan_obat.nama AS nm_satuan, SUM(if(tbt_stok_lain.jumlah>0,IF(tbt_stok_lain.jumlah_ambil_pending>0,IF(tbt_stok_lain.jumlah_ambil_pending>tbt_stok_lain.jumlah,0,tbt_stok_lain.jumlah-tbt_stok_lain.jumlah_ambil_pending),tbt_stok_lain.jumlah),0)) AS jml_stok, 
							(SELECT MAX(id) FROM tbt_obat_harga WHERE kode=tbm_obat.kode) AS id_harga, 
							IFNULL((SELECT hrg_jual FROM tbm_obat_hrg_khusus WHERE id_obat = tbm_obat.kode),0) AS hrg_khusus, 
							tMargin.persentase AS persentase, 
							tMargin.persentase_asuransi AS persentase_asuransi, 
							IF('".$st_asuransi."'='1',(SELECT (nilai_tanggungan/100) FROM tbm_provider_detail_obat_cover WHERE id_provider='".$id_perusahaan_asuransi."' AND id_obat=tbm_obat.kode AND st_delete='0'),0) AS persen_tanggungan 
						FROM tbm_obat 
							INNER JOIN tbt_stok_lain ON (tbt_stok_lain.id_obat = tbm_obat.kode) 
							INNER JOIN tbm_obat_kelompok_margin ON (tbm_obat_kelompok_margin.id = tbm_obat.kel_margin) 
							INNER JOIN (SELECT persentase,persentase_asuransi,nama,satelit FROM tbm_obat_kelompok_margin) AS tMargin ON (tMargin.nama = tbm_obat_kelompok_margin.nama AND tMargin.satelit='".$satelit."') 
							LEFT JOIN tbm_satuan_obat ON (tbm_satuan_obat.kode = tbm_obat.satuan) 
						WHERE  
							tbm_obat.st_delete = '0' 
							AND tbt_stok_lain.st_delete = '0' 
							AND tbt_stok_lain.tujuan='".$id_depo."' 
						GROUP BY tbm_obat.kode ORDER BY tbm_obat.nama ASC
					) as t
				INNER JOIN tbt_obat_harga ON (tbt_obat_harga.id = t.id_harga)
				INNER JOIN tbt_obat_jual_tunda ON (tbt_obat_jual_tunda.id_obat = t.id)
				WHERE
					tbt_obat_jual_tunda.no_trans_rawat = '".$no_trans."'
					AND tbt_obat_jual_tunda.tujuan = '".$id_depo."'
					AND tbt_obat_jual_tunda.id_bhp != '999999999'
					AND tbt_obat_jual_tunda.st = '0'
					AND tbt_obat_jual_tunda.st_delete = '0'
					AND tbt_obat_jual_tunda.id_kel_racik = '0'
				GROUP BY
					tbt_obat_jual_tunda.id_obat
				ORDER BY tbt_obat_jual_tunda.id ";
				
				$dataNonRacikan = SimakConf::queryAction($sql,'S');	
			
			
				$sql = "
				SELECT
					'0' AS id,
					tbt_obat_jual_tunda.id_obat AS nama,
					tbt_obat_jual_tunda.no_reg AS no_reg,
					'0' AS id_obat_old,
					tbt_obat_jual_tunda.id_harga AS id_harga,
					tbt_obat_jual_tunda.tujuan AS tujuan,
					tbt_obat_jual_tunda.expired AS expired,
					FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil_old,
					FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil,
					tbt_obat_harga.hrg_netto AS hrg_netto,
					tbt_obat_harga.hrg_ppn AS hrg_ppn,
					tbt_obat_harga.hrg_netto_disc AS hrg_netto_disc,
					tbt_obat_harga.hrg_ppn_disc AS hrg_ppn_disc,
					(t.persentase/100) AS persentase,
					(t.persentase_asuransi/100) AS persentase_asuransi,
					(t.persen_tanggungan/100) AS persen_tanggungan,
					t.hrg_khusus AS hrg_khusus,
					t.nm_satuan AS satuan,
					(t.jml_stok+FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0)) AS jml_stok,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 2)) AS aturan_jml,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 3)) AS aturan_jangka,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 4)) AS aturan_sediaan_jml,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 5)) AS aturan_sediaan,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 6)) AS aturan_waktu,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 7)) AS aturan_periode,
					(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 8)) AS aturan_carapakai,
					tbt_obat_jual_tunda.id_kel_racik AS id_kel_racik,
					tbt_obat_jual_tunda.jml_bungkus AS jml_bungkus,
					tbt_obat_jual_tunda.id_kemasan AS id_kemasan
				FROM		
					(
						SELECT 
							tbm_obat.kode AS id, 
							tbm_obat.nama, 
							tbm_satuan_obat.nama AS nm_satuan, SUM(if(tbt_stok_lain.jumlah>0,IF(tbt_stok_lain.jumlah_ambil_pending>0,IF(tbt_stok_lain.jumlah_ambil_pending>tbt_stok_lain.jumlah,0,tbt_stok_lain.jumlah-tbt_stok_lain.jumlah_ambil_pending),tbt_stok_lain.jumlah),0)) AS jml_stok, 
							(SELECT MAX(id) FROM tbt_obat_harga WHERE kode=tbm_obat.kode) AS id_harga, 
							IFNULL((SELECT hrg_jual FROM tbm_obat_hrg_khusus WHERE id_obat = tbm_obat.kode),0) AS hrg_khusus, 
							tMargin.persentase AS persentase, 
							tMargin.persentase_asuransi AS persentase_asuransi, 
							IF('".$st_asuransi."'='1',(SELECT (nilai_tanggungan/100) FROM tbm_provider_detail_obat_cover WHERE id_provider='".$id_perusahaan_asuransi."' AND id_obat=tbm_obat.kode AND st_delete='0'),0) AS persen_tanggungan 
						FROM tbm_obat 
							INNER JOIN tbt_stok_lain ON (tbt_stok_lain.id_obat = tbm_obat.kode) 
							INNER JOIN tbm_obat_kelompok_margin ON (tbm_obat_kelompok_margin.id = tbm_obat.kel_margin) 
							INNER JOIN (SELECT persentase,persentase_asuransi,nama,satelit FROM tbm_obat_kelompok_margin) AS tMargin ON (tMargin.nama = tbm_obat_kelompok_margin.nama AND tMargin.satelit='".$satelit."') 
							LEFT JOIN tbm_satuan_obat ON (tbm_satuan_obat.kode = tbm_obat.satuan) 
						WHERE  
							tbm_obat.st_delete = '0' 
							AND tbt_stok_lain.st_delete = '0' 
							AND tbt_stok_lain.tujuan='".$id_depo."' 
						GROUP BY tbm_obat.kode ORDER BY tbm_obat.nama ASC
					) as t
				INNER JOIN tbt_obat_harga ON (tbt_obat_harga.id = t.id_harga)
				INNER JOIN tbt_obat_jual_tunda ON (tbt_obat_jual_tunda.id_obat = t.id)
				WHERE
					tbt_obat_jual_tunda.no_trans_rawat = '".$no_trans."'
					AND tbt_obat_jual_tunda.tujuan = '".$id_depo."'
					AND tbt_obat_jual_tunda.id_bhp != '999999999'
					AND tbt_obat_jual_tunda.st = '0'
					AND tbt_obat_jual_tunda.st_delete = '0'
					AND tbt_obat_jual_tunda.id_kel_racik != '0'
				GROUP BY
					tbt_obat_jual_tunda.id_kel_racik, tbt_obat_jual_tunda.id_obat 
				ORDER BY tbt_obat_jual_tunda.id_kel_racik, tbt_obat_jual_tunda.id ";
				
				$dataRacikan = [];
				$idKelRacik = '';
				$dataRacikanItems = SimakConf::queryAction($sql,'S');	
				$i=0;
				foreach($dataRacikanItems as $row){
					if($idKelRacik!=$row['id_kel_racik']){
						$idKelRacik = $row['id_kel_racik'];
						$dataRacikan[$i]['jml_ambil'] = $row['jml_bungkus'];
						$dataRacikan[$i]['kemasan'] = $row['id_kemasan'];
						$dataRacikan[$i]['aturan_jml'] = $row['aturan_jml'];
						$dataRacikan[$i]['aturan_jangka'] = $row['aturan_jangka'];
						$dataRacikan[$i]['aturan_sediaan_jml'] = $row['aturan_sediaan_jml'];
						$dataRacikan[$i]['aturan_sediaan'] = $row['aturan_sediaan'];
						$dataRacikan[$i]['aturan_waktu'] = $row['aturan_waktu'];
						$dataRacikan[$i]['aturan_periode'] = $row['aturan_periode'];
						$dataRacikan[$i]['aturan_carapakai'] = $row['aturan_carapakai'];
						$dataRacikan[$i]['SubGridData'] = [];
						$i++;
						$j=0;
					}
					
					$item = array(
						'id' => $row['id'],
						'nama' => $row['nama'],
						'jml_stok' => $row['jml_stok'],
						'satuan' => $row['satuan'],
						'jml_ambil' => $row['jml_ambil'],
						'hrg_netto' => $row['hrg_netto'],
						'hrg_ppn' => $row['hrg_ppn'],
						'hrg_netto_disc' => $row['hrg_netto_disc'],
						'hrg_ppn_disc' => $row['hrg_ppn_disc'],
						'hrg_khusus' => $row['hrg_khusus'],
						'persentase' => $row['persentase'],
						'persentase_asuransi' => $row['persentase_asuransi'],
						'persen_tanggungan' => $row['persen_tanggungan'],
						'disc_penjamin' => 0,
						'id_obat_old' => $row['id_obat_old'],
						'jml_ambil_old' => $row['jml_ambil_old'],
						'id_harga' => $row['id_harga'],
						'tujuan' => $row['tujuan'],
						'expired' => $row['expired'],
						'no_reg' => $row['no_reg'],
						'id_kel_racik' => $row['id_kel_racik']
					);
					
					array_push($dataRacikan[$i-1]['SubGridData'], $item);
					$j++;
				}
			}
		}
		
		$data = array(
			'dataNonRacikan' => $dataNonRacikan,
			'dataRacikan' => $dataRacikan,
		);
		
		return $data;
	}
	
	public function getTransTindakanRajal()
	{
		$no_trans = $this->Request['no_trans'];
		$satelit = $this->Request['satelit'];
		$st_asuransi = $this->Request['st_asuransi'];
		$id_perusahaan_asuransi = $this->Request['id_perusahaan_asuransi'];
		$id_depo = $this->Request['id_depo'];
		$tindakan = $this->Request['tindakan'];
		$bhp = $this->Request['bhp'];
		$icdNine = $this->Request['icdNine'];
		
		if($tindakan=='1')
		{
			$sql = "SELECT 
								tbt_kasir_rwtjln.id,
								tbt_kasir_rwtjln.id_tindakan,
								tbt_kasir_rwtjln.id_tindakan AS id_tindakan_old,
								tbt_kasir_rwtjln.tarif,
								tbt_kasir_rwtjln.total AS tanggungan_pasien,
								FORMAT(tbt_kasir_rwtjln.disc,0) AS disc,
								tbt_kasir_rwtjln.tanggungan_asuransi AS tanggungan_penjamin,
								tbt_kasir_rwtjln.tarif_jasmed AS jasmed_dokter,
								FORMAT(tbt_kasir_rwtjln.pengali,0)AS jml,
								FORMAT(tbt_kasir_rwtjln.pengali,0)AS jml_old,
								tbm_tarif_tindakan.biaya1 AS tarif_normal,
								IFNULL(tbm_provider_detail_tindakan.tarif,0) AS tarif_penjamin,
								IFNULL(tbm_provider_detail_tindakan.st_tarif,'') AS st_tarif_penjamin,
								COUNT(DISTINCT tbm_provider_detail_tindakan.id) AS count_tindakan_cover
							FROM
								tbt_kasir_rwtjln
								INNER JOIN tbm_nama_tindakan ON (tbm_nama_tindakan.id = tbt_kasir_rwtjln.id_tindakan)
								INNER JOIN tbm_tarif_tindakan ON (
									tbm_tarif_tindakan.id_tdk = tbm_nama_tindakan.id
									AND tbm_tarif_tindakan.id_kls = '0'
									AND tbm_tarif_tindakan.satelit = '".$satelit."'
									AND tbm_tarif_tindakan.st_delete = '0'
								)
								LEFT JOIN tbm_provider_detail_tindakan ON (
									tbm_provider_detail_tindakan.id_tindakan = tbm_nama_tindakan.id
									AND tbm_provider_detail_tindakan.id_poli = tbm_nama_tindakan.id_klinik
									AND tbm_provider_detail_tindakan.st_delete = '0'
								)
							WHERE
								tbt_kasir_rwtjln.no_trans_rwtjln = '".$no_trans."'
								AND tbt_kasir_rwtjln.st_flag='0'
								AND tbt_kasir_rwtjln.st_delete='0'
							GROUP BY 
								tbt_kasir_rwtjln.id";
			
			$dataTindakan = SimakConf::queryAction($sql,'S');
		}
		if($bhp=='1')
		{
			$sqlBhp = "
			SELECT
				tbt_obat_jual.id,
				tbt_obat_jual.id_obat AS nama,
				tbt_obat_jual.no_reg AS no_reg,
				tbt_obat_jual.id_obat AS id_obat_old,
				tbt_obat_jual.id_harga AS id_harga,
				tbt_obat_jual.tujuan AS tujuan,
				tbt_obat_jual.expired AS expired,
				FORMAT(SUM(tbt_obat_jual.jumlah),0) AS jml_ambil_old,
				FORMAT(SUM(tbt_obat_jual.jumlah),0) AS jml_ambil,
				tbt_obat_harga.hrg_netto AS hrg_netto,
				tbt_obat_harga.hrg_ppn AS hrg_ppn,
				tbt_obat_harga.hrg_netto_disc AS hrg_netto_disc,
				tbt_obat_harga.hrg_ppn_disc AS hrg_ppn_disc,
				(t.persentase/100) AS persentase,
				(t.persentase_asuransi/100) AS persentase_asuransi,
				(t.persen_tanggungan/100) AS persen_tanggungan,
				t.hrg_khusus AS hrg_khusus,
				t.nm_satuan AS satuan,
				(t.jml_stok+FORMAT(SUM(tbt_obat_jual.jumlah),0)) AS jml_stok
			FROM		
				(
					SELECT 
						tbm_obat.kode AS id, 
						tbm_obat.nama, 
						tbm_satuan_obat.nama AS nm_satuan, SUM(if(tbt_stok_lain.jumlah>0,IF(tbt_stok_lain.jumlah_ambil_pending>0,IF(tbt_stok_lain.jumlah_ambil_pending>tbt_stok_lain.jumlah,0,tbt_stok_lain.jumlah-tbt_stok_lain.jumlah_ambil_pending),tbt_stok_lain.jumlah),0)) AS jml_stok, 
						(SELECT MAX(id) FROM tbt_obat_harga WHERE kode=tbm_obat.kode) AS id_harga, 
						IFNULL((SELECT hrg_jual FROM tbm_obat_hrg_khusus WHERE id_obat = tbm_obat.kode),0) AS hrg_khusus, 
						tMargin.persentase AS persentase, 
						tMargin.persentase_asuransi AS persentase_asuransi, 
						IF('".$st_asuransi."'='1',(SELECT (nilai_tanggungan/100) FROM tbm_provider_detail_obat_cover WHERE id_provider='".$id_perusahaan_asuransi."' AND id_obat=tbm_obat.kode AND st_delete='0'),0) AS persen_tanggungan 
					FROM tbm_obat 
						INNER JOIN tbt_stok_lain ON (tbt_stok_lain.id_obat = tbm_obat.kode) 
						INNER JOIN tbm_obat_kelompok_margin ON (tbm_obat_kelompok_margin.id = tbm_obat.kel_margin) 
						INNER JOIN (SELECT persentase,persentase_asuransi,nama,satelit FROM tbm_obat_kelompok_margin) AS tMargin ON (tMargin.nama = tbm_obat_kelompok_margin.nama AND tMargin.satelit='".$satelit."') 
						LEFT JOIN tbm_satuan_obat ON (tbm_satuan_obat.kode = tbm_obat.satuan) 
					WHERE  
						tbm_obat.st_delete = '0' 
						AND tbt_stok_lain.st_delete = '0' 
						AND tbt_stok_lain.tujuan='".$id_depo."' 
					GROUP BY tbm_obat.kode ORDER BY tbm_obat.nama ASC
				) as t
			INNER JOIN tbt_obat_harga ON (tbt_obat_harga.id = t.id_harga)
			INNER JOIN tbt_obat_jual ON (tbt_obat_jual.id_obat = t.id)
			WHERE
				tbt_obat_jual.no_trans_rwtjln = '".$no_trans."'
				AND tbt_obat_jual.tujuan = '".$id_depo."'
				AND tbt_obat_jual.id_bhp = '999999999'
				AND tbt_obat_jual.flag = '0'
				AND tbt_obat_jual.st_delete = '0'
			GROUP BY
				tbt_obat_jual.id_obat
			ORDER BY tbt_obat_jual.id ";
			
			$dataBhp = SimakConf::queryAction($sqlBhp,'S');	
		}
		
		if($icdNine=='1')
		{
			$sqlNine = "SELECT 
										tbt_icd_nine.id, 
										tbt_icd_nine.id_icd, 
										tbt_icd_nine.id_icd AS id_icd_old, 
										tbm_icd_nine.desc_short AS nama,
										tbm_icd_nine.category AS category
									FROM 
										tbm_icd_nine 
										INNER JOIN tbt_icd_nine ON (tbt_icd_nine.id_icd = tbm_icd_nine.id)
									WHERE
										tbt_icd_nine.no_trans = '".$no_trans."'
										AND tbt_icd_nine.st_delete='0'
									GROUP BY 
										tbt_icd_nine.id 
									ORDER BY 
										tbt_icd_nine.id ASC";
										
			$dataIcdNine = SimakConf::queryAction($sqlNine,'S');
			
			$sqlTen = "SELECT 
										tbt_icd.id, 
										tbt_icd.kode_icd AS id_icd, 
										tbt_icd.kode_icd AS id_icd_old, 
										tbm_icd_global.indonesia AS nama
									FROM 
										tbm_icd_global 
										INNER JOIN tbt_icd ON (tbt_icd.kode_icd = tbm_icd_global.id)
									WHERE
										tbt_icd.no_trans = '".$no_trans."'
										AND tbt_icd.st_delete='0'
									GROUP BY 
										tbt_icd.id 
									ORDER BY 
										tbt_icd.id ASC";
										
			$dataIcdTen = SimakConf::queryAction($sqlTen,'S');
		}
		
		$data = array(
			'dataTindakan' => $dataTindakan,
			'dataBhp' => $dataBhp,
			'dataIcdNine' => $dataIcdNine,
			'dataIcdTen' => $dataIcdTen,
		);
		
		return $data;
	}
	
	public function getDataTransPasien()
	{
		$no_reg = $this->Request['no_reg'];
		
		$sql = "SELECT
							trans_order,
							trans_type,
							tgl_visit,
							wkt_visit,
							cm,
							no_reg_daftar,
							no_trans_rwt,
							no_trans,
							nm_klinik,
							id_klinik,
							id_dokter,
							nm_dokter,
							id_tindakan,
							nama,
							FORMAT(jml,0) AS jml,
							FORMAT(tarif,2,'id_ID') AS tarif,
							FORMAT(tagihan_pasien,2,'id_ID') AS tagihan_pasien,
							FORMAT(disc,2,'id_ID') AS disc,
							FORMAT(tagihan_penjamin,2,'id_ID') AS tagihan_penjamin,
							id_table_trans,
							'1' AS checked
						FROM
						(
							(SELECT
										'0' AS trans_order,
										'Retribusi' AS trans_type,
										a.tgl_visit,
										a.wkt_visit,
										a.cm,
										a.no_reg AS no_reg_daftar,
										a.no_trans AS no_trans_rwt,
										b.no_trans,
										d.id AS id_klinik,
										d.nama AS nm_klinik,
										'' AS id_dokter,
										'' AS nm_dokter,
										'' AS id_tindakan,
										CONCAT('Retribusi Poli ',d.nama) AS nama,
										1 AS jml,
										b.tarif,
										b.tarif AS tagihan_pasien,
										0 AS disc,
										0 AS tagihan_penjamin,
										b.id AS id_table_trans
									FROM
										tbt_rawat_jalan a
										INNER JOIN tbt_kasir_pendaftaran b ON (b.no_trans = a.no_trans)
										INNER JOIN tbm_poliklinik d ON (d.id = a.id_klinik)
									WHERE b.tarif>0 AND a.st_delete='0' AND a.st_alih = '0' AND b.st_delete='0' AND b.st_flag='0' AND a.no_reg='".$no_reg."'
									ORDER BY tgl_visit, wkt_visit) 

							UNION ALL
							
							(SELECT
										'1' AS trans_order,
										CONCAT('Tindakan Poli ',d.nama) AS trans_type,
										a.tgl_visit,
										a.wkt_visit,
										a.cm,
										a.no_reg AS no_reg_daftar,
										a.no_trans AS no_trans_rwt,
										b.no_trans,
										d.id AS id_klinik,
										d.nama AS nm_klinik,
										e.id AS id_dokter,
										e.nama AS nm_dokter,
										b.id_tindakan,
										c.nama,
										b.pengali AS jml,
										b.tarif,
										b.total AS tagihan_pasien,
										b.disc,
										b.tanggungan_asuransi AS tagihan_penjamin,
										b.id AS id_table_trans
									FROM
										tbt_rawat_jalan a
										INNER JOIN tbt_kasir_rwtjln b ON (b.no_trans_rwtjln = a.no_trans)
										INNER JOIN tbm_nama_tindakan c ON (b.id_tindakan = c.id)
										INNER JOIN tbm_poliklinik d ON (d.id = a.id_klinik)
										INNER JOIN tbd_pegawai e ON (e.id = b.dokter_pelaksana_tdk)
									WHERE a.st_delete='0' AND a.st_alih = '0' AND b.st_delete='0' AND b.st_flag='0' AND a.no_reg='".$no_reg."'
									ORDER BY tgl_visit, wkt_visit) 

							UNION ALL
									
							(SELECT
										'2' AS trans_order,
										UPPER(CONCAT('RESEP ',f.no_resep,' : Poli ',d.nama)) AS trans_type,
										a.tgl_visit,
										a.wkt_visit,
										a.cm,
										a.no_reg AS no_reg_daftar,
										a.no_trans AS no_trans_rwt,
										b.no_trans,
										d.id AS id_klinik,
										d.nama AS nm_klinik,
										e.id AS id_dokter,
										e.nama AS nm_dokter,
										b.id_obat AS id_tindakan,
										c.nama,
										b.jumlah AS jml,
										(b.total)/b.jumlah AS tarif,
										b.total AS tagihan_pasien,
										0 AS disc,
										0 AS tagihan_penjamin,
										b.id AS id_table_trans
									FROM
										tbt_rawat_jalan a
										INNER JOIN tbt_obat_jual b ON (b.no_trans_rwtjln = a.no_trans)
										INNER JOIN tbm_obat c ON (b.id_obat = c.kode)
										INNER JOIN tbm_poliklinik d ON (d.id = a.id_klinik)
										INNER JOIN tbd_pegawai e ON (e.id = b.dokter)
										INNER JOIN tbt_obat_resep f ON (f.no_reg = b.no_reg)
									WHERE a.st_delete='0' AND a.st_alih = '0' AND b.st_delete='0' AND b.flag='0' AND b.id_kel_racik='0' AND a.no_reg='".$no_reg."'
									GROUP BY b.id
									ORDER BY tgl_visit, wkt_visit) 
									
							UNION ALL
							
							(SELECT
										'2' AS trans_order,
										UPPER(CONCAT('RESEP ',f.no_resep,' : Poli ',d.nama)) AS trans_type,
										a.tgl_visit,
										a.wkt_visit,
										a.cm,
										a.no_reg AS no_reg_daftar,
										a.no_trans AS no_trans_rwt,
										b.no_trans,
										d.id AS id_klinik,
										d.nama AS nm_klinik,
										e.id AS id_dokter,
										e.nama AS nm_dokter,
										b.id_kel_racik AS id_tindakan,
										CONCAT('Racikan ',b.id_kel_racik) AS nama,
										b.jml_bungkus AS jml,
										SUM(b.total)/b.jml_bungkus AS tarif,
										SUM(b.total) AS tagihan_pasien,
										0 AS disc,
										0 AS tagihan_penjamin,
										b.id AS id_table_trans
									FROM
										tbt_rawat_jalan a
										INNER JOIN tbt_obat_jual b ON (b.no_trans_rwtjln = a.no_trans)
										INNER JOIN tbm_obat c ON (b.id_obat = c.kode)
										INNER JOIN tbm_poliklinik d ON (d.id = a.id_klinik)
										INNER JOIN tbd_pegawai e ON (e.id = b.dokter)
										INNER JOIN tbt_obat_resep f ON (f.no_reg = b.no_reg)
									WHERE a.st_delete='0' AND a.st_alih = '0' AND b.st_delete='0' AND b.flag='0' AND b.id_kel_racik!='0' AND a.no_reg='".$no_reg."'
									GROUP BY b.id_kel_racik
									ORDER BY tgl_visit, wkt_visit) 
									
							UNION ALL
							
							(SELECT
										'2' AS trans_order,
										UPPER(CONCAT('RESEP ',f.no_resep,' : OTC ')) AS trans_type,
										b.tgl AS tgl_visit,
										b.wkt AS wkt_visit,
										a.cm,
										b.no_reg AS no_reg_daftar,
										a.no_trans AS no_trans_rwt,
										b.no_trans,
										'' AS id_klinik,
										'' AS nm_klinik,
										'' AS id_dokter,
										'' AS nm_dokter,
										b.id_obat AS id_tindakan,
										c.nama,
										b.jumlah AS jml,
										(b.total)/b.jumlah AS tarif,
										b.total AS tagihan_pasien,
										0 AS disc,
										0 AS tagihan_penjamin,
										b.id AS id_table_trans
									FROM
										tbd_pasien_luar a
										INNER JOIN tbt_obat_jual_lain b ON (b.no_trans_pas_luar = a.no_trans)
										INNER JOIN tbm_obat c ON (b.id_obat = c.kode)
										INNER JOIN tbt_obat_resep f ON (f.no_reg = b.no_reg)
									WHERE a.st_delete='0' AND b.st_delete='0' AND b.flag='0' AND b.id_kel_racik='0' AND b.no_trans_pas_luar='".$no_reg."'
									GROUP BY b.id
									ORDER BY tgl_visit, wkt_visit) 
									
							UNION ALL
							
							(SELECT
										'3' AS trans_order,
										'Laboratorium' AS trans_type,
										a.tgl_visit,
										a.wkt_visit,
										a.cm,
										a.no_reg AS no_reg_daftar,
										a.no_trans AS no_trans_rwt,
										b.no_trans,
										d.id AS id_klinik,
										d.nama AS nm_klinik,
										e.id AS id_dokter,
										e.nama AS nm_dokter,
										b.id_tindakan,
										c.nama,
										b.pengali AS jml,
										CAST(b.harga/b.pengali AS SIGNED)  AS tarif,
										CAST(b.harga-b.disc AS SIGNED) AS tagihan_pasien,
										b.disc,
										b.tanggungan_asuransi AS tagihan_penjamin,
										b.id AS id_table_trans
									FROM
										tbt_rawat_jalan a
										INNER JOIN tbt_lab_penjualan b ON (b.no_trans_rwtjln = a.no_trans)
										INNER JOIN tbm_lab_tindakan c ON (b.id_tindakan = c.kode)
										INNER JOIN tbm_poliklinik d ON (d.id = a.id_klinik)
										INNER JOIN tbd_pegawai e ON (e.id = a.dokter)
									WHERE a.st_delete='0' AND a.st_alih = '0' AND b.st_delete='0' AND b.flag='0' AND b.st_medical_checkup='0' AND b.st_travel_clinic='0' AND a.no_reg='".$no_reg."'
									ORDER BY tgl_visit, wkt_visit) 
									
							UNION ALL
									
							(SELECT
										'4' AS trans_order,
										'Radiologi' AS trans_type,
										a.tgl_visit,
										a.wkt_visit,
										a.cm,
										a.no_reg AS no_reg_daftar,
										a.no_trans AS no_trans_rwt,
										b.no_trans,
										d.id AS id_klinik,
										d.nama AS nm_klinik,
										e.id AS id_dokter,
										e.nama AS nm_dokter,
										b.id_tindakan,
										c.nama,
										b.pengali AS jml,
										CAST(b.harga/b.pengali AS SIGNED)  AS tarif,
										CAST(b.harga-b.disc AS SIGNED) AS tagihan_pasien,
										b.disc,
										b.tanggungan_asuransi AS tagihan_penjamin,
										b.id AS id_table_trans
									FROM
										tbt_rawat_jalan a
										INNER JOIN tbt_rad_penjualan b ON (b.no_trans_rwtjln = a.no_trans)
										INNER JOIN tbm_rad_tindakan c ON (b.id_tindakan = c.kode)
										INNER JOIN tbm_poliklinik d ON (d.id = a.id_klinik)
										INNER JOIN tbd_pegawai e ON (e.id = a.dokter)
									WHERE a.st_delete='0' AND a.st_alih = '0' AND b.st_delete='0' AND b.flag='0' AND b.st_medical_checkup='0' AND b.st_travel_clinic='0' AND a.no_reg='".$no_reg."'
									ORDER BY tgl_visit, wkt_visit) 
									
						) tTrans
						ORDER BY no_reg_daftar,trans_order,nm_klinik, id_table_trans, tgl_visit, wkt_visit";
		
		$dataTindakan = SimakConf::queryAction($sql,'S');
		
		$data = array(
			'dataTindakan' => $dataTindakan,
		);
		
		return $data;
	}
	
	public function getDataDiagnosaRajal()
	{
		$no_trans = $this->Request['no_trans'];
		$satelit = $this->Request['satelit'];
		$st_asuransi = $this->Request['st_asuransi'];
		$id_perusahaan_asuransi = $this->Request['id_perusahaan_asuransi'];
		$id_depo = $this->Request['id_depo'];
		
		$tdk_penunjang_list = explode(',',RwtjlnRecord::finder()->findByPk($no_trans)->tdk_penunjang_list);
		$stResepChecked = (in_array('1', $tdk_penunjang_list))?true:false;
		
		$sql = "SELECT 
									tbt_diagnosa.subject,
									tbt_diagnosa.object,
									tbt_diagnosa.planning,
									FORMAT(tbt_diagnosa.berat_badan, 0) AS berat_badan
								FROM 
									tbt_diagnosa 
								WHERE
									tbt_diagnosa.no_trans = '".$no_trans."'
									AND tbt_diagnosa.st_delete='0'
								GROUP BY 
									tbt_diagnosa.id 
								ORDER BY 
									tbt_diagnosa.id ASC";
		$dataDiagnosa = SimakConf::queryAction($sql,'S');
		
		$sql = "SELECT 
									tbt_icd.id, 
									tbt_icd.kode_icd AS id_icd, 
									tbt_icd.kode_icd AS id_icd_old, 
									tbm_icd_global.indonesia AS nama
								FROM 
									tbm_icd_global 
									INNER JOIN tbt_icd ON (tbt_icd.kode_icd = tbm_icd_global.id)
								WHERE
									tbt_icd.no_trans = '".$no_trans."'
									AND tbt_icd.st_delete='0'
								GROUP BY 
									tbt_icd.id 
								ORDER BY 
									tbt_icd.id ASC";
		$dataIcdTen = SimakConf::queryAction($sql,'S');
		
		$sql = "SELECT 
									tbt_diagnosa_tindakan_penunjang.id,
									tbt_diagnosa_tindakan_penunjang.id_penunjang,
									tbt_diagnosa_tindakan_penunjang.id_tindakan,
									tbt_diagnosa_tindakan_penunjang.id_tindakan AS id_tindakan_old,
									tbt_diagnosa_tindakan_penunjang.st_proses,
									IF(tbt_diagnosa_tindakan_penunjang.st_proses='1','SUDAH','BELUM') AS st_proses_txt
								FROM 
									tbt_diagnosa_tindakan_penunjang
								WHERE
									tbt_diagnosa_tindakan_penunjang.no_trans_rujuk = '".$no_trans."'
									AND tbt_diagnosa_tindakan_penunjang.st_proses='0'
									AND tbt_diagnosa_tindakan_penunjang.st_delete='0'
								ORDER BY 
									tbt_diagnosa_tindakan_penunjang.id_penunjang, tbt_diagnosa_tindakan_penunjang.id";
		$dataPenunjang = SimakConf::queryAction($sql,'S');
		
		$sql = "
		SELECT
			tbt_obat_jual_tunda.id,
			tbt_obat_jual_tunda.id_obat AS nama,
			tbt_obat_jual_tunda.no_reg AS no_reg,
			tbt_obat_jual_tunda.id_obat AS id_obat_old,
			tbt_obat_jual_tunda.id_harga AS id_harga,
			tbt_obat_jual_tunda.tujuan AS tujuan,
			tbt_obat_jual_tunda.expired AS expired,
			FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil_old,
			FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil,
			tbt_obat_harga.hrg_netto AS hrg_netto,
			tbt_obat_harga.hrg_ppn AS hrg_ppn,
			tbt_obat_harga.hrg_netto_disc AS hrg_netto_disc,
			tbt_obat_harga.hrg_ppn_disc AS hrg_ppn_disc,
			(t.persentase/100) AS persentase,
			(t.persentase_asuransi/100) AS persentase_asuransi,
			(t.persen_tanggungan/100) AS persen_tanggungan,
			'0' AS disc_penjamin,
			t.hrg_khusus AS hrg_khusus,
			t.nm_satuan AS satuan,
			(t.jml_stok+FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0)) AS jml_stok,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 2)) AS aturan_jml,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 3)) AS aturan_jangka,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 4)) AS aturan_sediaan_jml,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 5)) AS aturan_sediaan,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 6)) AS aturan_waktu,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 7)) AS aturan_periode,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 8)) AS aturan_carapakai
		FROM		
			(
				SELECT 
					tbm_obat.kode AS id, 
					tbm_obat.nama, 
					tbm_satuan_obat.nama AS nm_satuan, SUM(if(tbt_stok_lain.jumlah>0,IF(tbt_stok_lain.jumlah_ambil_pending>0,IF(tbt_stok_lain.jumlah_ambil_pending>tbt_stok_lain.jumlah,0,tbt_stok_lain.jumlah-tbt_stok_lain.jumlah_ambil_pending),tbt_stok_lain.jumlah),0)) AS jml_stok, 
					(SELECT MAX(id) FROM tbt_obat_harga WHERE kode=tbm_obat.kode) AS id_harga, 
					IFNULL((SELECT hrg_jual FROM tbm_obat_hrg_khusus WHERE id_obat = tbm_obat.kode),0) AS hrg_khusus, 
					tMargin.persentase AS persentase, 
					tMargin.persentase_asuransi AS persentase_asuransi, 
					IF('".$st_asuransi."'='1',(SELECT (nilai_tanggungan/100) FROM tbm_provider_detail_obat_cover WHERE id_provider='".$id_perusahaan_asuransi."' AND id_obat=tbm_obat.kode AND st_delete='0'),0) AS persen_tanggungan 
				FROM tbm_obat 
					INNER JOIN tbt_stok_lain ON (tbt_stok_lain.id_obat = tbm_obat.kode) 
					INNER JOIN tbm_obat_kelompok_margin ON (tbm_obat_kelompok_margin.id = tbm_obat.kel_margin) 
					INNER JOIN (SELECT persentase,persentase_asuransi,nama,satelit FROM tbm_obat_kelompok_margin) AS tMargin ON (tMargin.nama = tbm_obat_kelompok_margin.nama AND tMargin.satelit='".$satelit."') 
					LEFT JOIN tbm_satuan_obat ON (tbm_satuan_obat.kode = tbm_obat.satuan) 
				WHERE  
					tbm_obat.st_delete = '0' 
					AND tbt_stok_lain.st_delete = '0' 
					AND tbt_stok_lain.tujuan='".$id_depo."' 
				GROUP BY tbm_obat.kode ORDER BY tbm_obat.nama ASC
			) as t
		INNER JOIN tbt_obat_harga ON (tbt_obat_harga.id = t.id_harga)
		INNER JOIN tbt_obat_jual_tunda ON (tbt_obat_jual_tunda.id_obat = t.id)
		WHERE
			tbt_obat_jual_tunda.no_trans_rawat = '".$no_trans."'
			AND tbt_obat_jual_tunda.tujuan = '".$id_depo."'
			AND tbt_obat_jual_tunda.id_bhp != '999999999'
			AND tbt_obat_jual_tunda.st = '0'
			AND tbt_obat_jual_tunda.st_delete = '0'
			AND tbt_obat_jual_tunda.id_kel_racik = '0'
		GROUP BY
			tbt_obat_jual_tunda.id_obat
		ORDER BY tbt_obat_jual_tunda.id ";
		
		$dataNonRacikan = SimakConf::queryAction($sql,'S');	
	
	
		$sql = "
		SELECT
			tbt_obat_jual_tunda.id,
			tbt_obat_jual_tunda.id_obat AS nama,
			tbt_obat_jual_tunda.no_reg AS no_reg,
			tbt_obat_jual_tunda.id_obat AS id_obat_old,
			tbt_obat_jual_tunda.id_harga AS id_harga,
			tbt_obat_jual_tunda.tujuan AS tujuan,
			tbt_obat_jual_tunda.expired AS expired,
			FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil_old,
			FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0) AS jml_ambil,
			tbt_obat_harga.hrg_netto AS hrg_netto,
			tbt_obat_harga.hrg_ppn AS hrg_ppn,
			tbt_obat_harga.hrg_netto_disc AS hrg_netto_disc,
			tbt_obat_harga.hrg_ppn_disc AS hrg_ppn_disc,
			(t.persentase/100) AS persentase,
			(t.persentase_asuransi/100) AS persentase_asuransi,
			(t.persen_tanggungan/100) AS persen_tanggungan,
			t.hrg_khusus AS hrg_khusus,
			t.nm_satuan AS satuan,
			(t.jml_stok+FORMAT(SUM(tbt_obat_jual_tunda.jumlah),0)) AS jml_stok,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 2)) AS aturan_jml,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 3)) AS aturan_jangka,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 4)) AS aturan_sediaan_jml,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 5)) AS aturan_sediaan,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 6)) AS aturan_waktu,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 7)) AS aturan_periode,
			(SELECT SPLIT_STR(tbt_obat_jual_tunda.aturan_pakai, '-', 8)) AS aturan_carapakai,
			tbt_obat_jual_tunda.id_kel_racik AS id_kel_racik,
			tbt_obat_jual_tunda.jml_bungkus AS jml_bungkus,
			tbt_obat_jual_tunda.id_kemasan AS id_kemasan
		FROM		
			(
				SELECT 
					tbm_obat.kode AS id, 
					tbm_obat.nama, 
					tbm_satuan_obat.nama AS nm_satuan, SUM(if(tbt_stok_lain.jumlah>0,IF(tbt_stok_lain.jumlah_ambil_pending>0,IF(tbt_stok_lain.jumlah_ambil_pending>tbt_stok_lain.jumlah,0,tbt_stok_lain.jumlah-tbt_stok_lain.jumlah_ambil_pending),tbt_stok_lain.jumlah),0)) AS jml_stok, 
					(SELECT MAX(id) FROM tbt_obat_harga WHERE kode=tbm_obat.kode) AS id_harga, 
					IFNULL((SELECT hrg_jual FROM tbm_obat_hrg_khusus WHERE id_obat = tbm_obat.kode),0) AS hrg_khusus, 
					tMargin.persentase AS persentase, 
					tMargin.persentase_asuransi AS persentase_asuransi, 
					IF('".$st_asuransi."'='1',(SELECT (nilai_tanggungan/100) FROM tbm_provider_detail_obat_cover WHERE id_provider='".$id_perusahaan_asuransi."' AND id_obat=tbm_obat.kode AND st_delete='0'),0) AS persen_tanggungan 
				FROM tbm_obat 
					INNER JOIN tbt_stok_lain ON (tbt_stok_lain.id_obat = tbm_obat.kode) 
					INNER JOIN tbm_obat_kelompok_margin ON (tbm_obat_kelompok_margin.id = tbm_obat.kel_margin) 
					INNER JOIN (SELECT persentase,persentase_asuransi,nama,satelit FROM tbm_obat_kelompok_margin) AS tMargin ON (tMargin.nama = tbm_obat_kelompok_margin.nama AND tMargin.satelit='".$satelit."') 
					LEFT JOIN tbm_satuan_obat ON (tbm_satuan_obat.kode = tbm_obat.satuan) 
				WHERE  
					tbm_obat.st_delete = '0' 
					AND tbt_stok_lain.st_delete = '0' 
					AND tbt_stok_lain.tujuan='".$id_depo."' 
				GROUP BY tbm_obat.kode ORDER BY tbm_obat.nama ASC
			) as t
		INNER JOIN tbt_obat_harga ON (tbt_obat_harga.id = t.id_harga)
		INNER JOIN tbt_obat_jual_tunda ON (tbt_obat_jual_tunda.id_obat = t.id)
		WHERE
			tbt_obat_jual_tunda.no_trans_rawat = '".$no_trans."'
			AND tbt_obat_jual_tunda.tujuan = '".$id_depo."'
			AND tbt_obat_jual_tunda.id_bhp != '999999999'
			AND tbt_obat_jual_tunda.st = '0'
			AND tbt_obat_jual_tunda.st_delete = '0'
			AND tbt_obat_jual_tunda.id_kel_racik != '0'
		GROUP BY
			tbt_obat_jual_tunda.id_kel_racik, tbt_obat_jual_tunda.id_obat 
		ORDER BY tbt_obat_jual_tunda.id_kel_racik, tbt_obat_jual_tunda.id ";
		
		$dataRacikan = [];
		$idKelRacik = '';
		$dataRacikanItems = SimakConf::queryAction($sql,'S');	
		$i=0;
		foreach($dataRacikanItems as $row){
			if($idKelRacik!=$row['id_kel_racik']){
				$idKelRacik = $row['id_kel_racik'];
				$dataRacikan[$i]['jml_ambil'] = $row['jml_bungkus'];
				$dataRacikan[$i]['kemasan'] = $row['id_kemasan'];
				$dataRacikan[$i]['aturan_jml'] = $row['aturan_jml'];
				$dataRacikan[$i]['aturan_jangka'] = $row['aturan_jangka'];
				$dataRacikan[$i]['aturan_sediaan_jml'] = $row['aturan_sediaan_jml'];
				$dataRacikan[$i]['aturan_sediaan'] = $row['aturan_sediaan'];
				$dataRacikan[$i]['aturan_waktu'] = $row['aturan_waktu'];
				$dataRacikan[$i]['aturan_periode'] = $row['aturan_periode'];
				$dataRacikan[$i]['aturan_carapakai'] = $row['aturan_carapakai'];
				$dataRacikan[$i]['SubGridData'] = [];
				$i++;
				$j=0;
			}
			
			$item = array(
				'id' => $row['id'],
				'nama' => $row['nama'],
				'jml_stok' => $row['jml_stok'],
				'satuan' => $row['satuan'],
				'jml_ambil' => $row['jml_ambil'],
				'hrg_netto' => $row['hrg_netto'],
				'hrg_ppn' => $row['hrg_ppn'],
				'hrg_netto_disc' => $row['hrg_netto_disc'],
				'hrg_ppn_disc' => $row['hrg_ppn_disc'],
				'hrg_khusus' => $row['hrg_khusus'],
				'persentase' => $row['persentase'],
				'persentase_asuransi' => $row['persentase_asuransi'],
				'persen_tanggungan' => $row['persen_tanggungan'],
				'disc_penjamin' => 0,
				'id_obat_old' => $row['id_obat_old'],
				'jml_ambil_old' => $row['jml_ambil_old'],
				'id_harga' => $row['id_harga'],
				'tujuan' => $row['tujuan'],
				'expired' => $row['expired'],
				'no_reg' => $row['no_reg'],
				'id_kel_racik' => $row['id_kel_racik']
			);
			
			array_push($dataRacikan[$i-1]['SubGridData'], $item);
			$j++;
		}
		
		$data = array(
			'stResepChecked' => $stResepChecked,
			'dataDiagnosa' => $dataDiagnosa,
			'dataIcdTen' => $dataIcdTen,
			'dataPenunjang' => $dataPenunjang,
			'dataNonRacikan' => $dataNonRacikan,
			'dataRacikan' => $dataRacikan,
		);
		
		return $data;
	}
	
	public function getJsonContent()
	{
		if($this->Request['mode']=='ProductImages')
			$data = $this->productImages();
		elseif($this->Request['mode']=='OrganizationImages')
			$data = $this->organizationImages();	
		elseif($this->Request['mode']=='select2')
			$data = $this->select2DataList();
		elseif($this->Request['mode']=='activeRecordTable')
			$data = $this->activeRecordTable();
		elseif($this->Request['mode']=='Roles' || $this->Request['mode']=='Permissions')
			$data = $this->buatMenu(1);
		elseif($this->Request['mode']=='Organization')
			$data = $this->buatMenu(1);
		elseif($this->Request['mode']=='getTransTindakanRajal')
			$data = $this->getTransTindakanRajal();		
		elseif($this->Request['mode']=='getDataTransPasien')
			$data = $this->getDataTransPasien();		
		elseif($this->Request['mode']=='getTransApotik')
			$data = $this->getTransApotik();		
		elseif($this->Request['mode']=='getDataDiagnosaRajal')
			$data = $this->getDataDiagnosaRajal();		
		else	
			$data = $this->buatMenu(1);
			
		return $data;
	}
	
	public function dirToArray($param)
	{ 
		$result = array();
		$cdir = scandir($param['dir']);
		foreach ($cdir as $key => $value)
		{
			if (!in_array($value,$param['dirExclude']))
			{
				if (is_dir($param['dir'] . DIRECTORY_SEPARATOR . $value))
				{
					$check = $this->dirToArray(array(
						'dirParent'=>(isset($param['dirParent']))?$param['dirParent'].'.'.$value:$value,
						'dir'=>$param['dir'] . DIRECTORY_SEPARATOR . $value,
						'dirExclude'=>$param['dirExclude'],
						'fileExt'=>$param['fileExt']
					));
					
					if($check)
						$result[$value] = $check;
				}
				else
				{
					$fileName = preg_replace("/\.[^.]+$/", "", $value);
					if(is_array($param['fileExt']) && count($param['fileExt'])>0)
					{
						if(preg_match('/\.('.implode('|',$param['fileExt']).')*$/i', $value, $matches) && !in_array($value,$param['fileExclude']))
						{
							$res = (isset($param['dirParent']))?$param['dirParent'].'.'.$fileName:$fileName;
							$result[] = $res;
							array_push($this->routeList,array('id'=>$res,'text'=>$res));
						}
					}
					else
					{
						if(!in_array($value,$param['fileExclude']))
						{
							$res = (isset($param['dirParent']))?$param['dirParent'].'.'.$fileName:$fileName;
							$result[] = $res;
							array_push($this->routeList,array('id'=>$res,'text'=>$res));
						}
					}
				}
			}
		}

		return $result;
	}
}

?>
