<?PHP
class ProfilPerusahaan extends MainConf
{

	public function onPreRenderComplete($param)
	{
		parent::onPreRenderComplete($param);
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
		}
		
	}
	
	public function onPreRender($param)
	{
		parent::onPreRender($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
		}
	}
	
	public function onLoadComplete($param)
	{
		parent::onLoadComplete($param);
		
		if(!$this->Page->IsPostBack && !$this->Page->IsCallBack)  
		{
			$PerusahaanRecord = PerusahaanRecord::finder()->findByPk(1);
			$this->namaperus->Text = $PerusahaanRecord->nama;
			$this->alamat->Text = $PerusahaanRecord->alamat;
			$this->telepon->Text = $PerusahaanRecord->telepon;
			$this->email->Text = $PerusahaanRecord->email;
		}
	}
	
	public function updateProfileClicked()
	{
		$nmPerus = $this->namaperus->Text;
		$alamat = $this->alamat->Text;
		$telepon = $this->telepon->Text;
		$email = $this->email->Text;
		$PerusahaanRecord = PerusahaanRecord::finder()->findByPk(1);
		$PerusahaanRecord->nama = $nmPerus;
		$PerusahaanRecord->alamat = $alamat;
		$PerusahaanRecord->telepon = $telepon;
		$PerusahaanRecord->email = $email;
		$PerusahaanRecord->save();
		
		$this->getPage()->getClientScript()->registerEndScript
					('','
					unloadContent();
					toastr.info("Profil Perusahaan Telah Diupdate !");
					');	
	}
	
}
?>
