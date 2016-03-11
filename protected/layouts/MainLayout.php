<?PHP
class MainLayout extends TTemplateControl
{
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)
		{	
			if($this->User->IsGuest)
				$this->Response->redirect($this->Service->constructUrl('Login'));
			
		}
	}
}
?>
