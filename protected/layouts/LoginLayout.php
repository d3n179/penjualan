<?PHP
class LoginLayout extends TTemplateControl
{
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)
		{	
			//$this->Page->Theme = 'admthemes';
			if(!$this->User->IsGuest)
				$this->Response->redirect($this->Service->constructUrl('Home'));
		}
	}
}
?>
