<?php
class PdfLayout extends TTemplateControl
{
	public function onInit($param)
	{
		parent::onInit($param);
	}
		
	public function onPreRender($param)
	{
		parent::onPreRender($param);
	}
		
	public function onLoad($param)
	{
		parent::onLoad($param);	
		$this->getResponse()->setContentType("application/pdf");
	}
	
}
?>
