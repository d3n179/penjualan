<?php
Prado::using('Application.Portlets.Portlet');
class RightSidePortlet extends Portlet
{
	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{	
			
		}
		
		if($this->Page->IsCallBack)    
		{	
			
		}
	}
	
}

?>
