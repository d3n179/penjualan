<?php
/**
 * CSHtmlArea class file.
 *
 * @author Cláudio César Monteiro dos Santos Júnior <claudiocmsj[at]gmail[dot]com>
 * @version $Revision: 1.1 $Date: 2009-07-23 15:25
 */

/**
 * CSHtmlArea class.
 * 
 * Sorry my poor english.
 * This is a component like THtmlArea, but uses the handy and powerfull
 * FCKeditor instead of TinyMCE. It's very easy to install and use.
 * 
 * REQUIREMENTS:
 * 
 * This component was tested only with PRADO 3.1.1, worked fine and probabily is
 * compatible with next versions of the framework.
 * 
 * INSTALLATION AND USAGE:
 * 
 * Unpack CSHtmlArea.zip and put the contents into the application namespace, in
 * a subfolder of your preference; register it in your application.xml; now you
 * can instantiate the component like a simple TTextBox and have fun! How to do
 * it and what's the features? See the instructions and examples below:
 * 
 * EXAMPLES:
 * 
 * application.xml:
 * <code>
 * ...
 * <using namespace="Application.common.CSHtmlArea.*" />
 * ...
 * </code>
 * 
 * 1) Very simple:
 * 
 * <code>
 * <com:CSHtmlArea ID="Html" />
 * </code>
 * 
 * 2) Simple with dimensions:
 * 
 * <code>
 * <com:CSHtmlArea ID="Html" Width="98%" Height="300px" />
 * </code>
 * 
 * 3) Not so simple with culture and advanced configurations:
 * 
 * <code>
 * <com:CSHtmlArea ID="Html" Culture="pt_BR"
 *   Config.AutoDetectLanguage="false"
 *   Config.ShowBorders="false"
 *   Config.AutoGrowMax="400" />
 * </code>
 * 
 * Note #1: The configurations options are the same available in the core of
 * FCKeditor. Don't worry about inline configurations or external configuration
 * file, just follow the sintax above and BE HAPPY!
 * 
 * 4) Complex properties:
 * 
 * <code>
 * <com:CSHtmlArea ID="Html" Culture="pt_BR"
 *   Config.AutoDetectPasteFromWord="true"
 *   Config.ShowBorders="false"
 *   Config.AutoGrowMax="400"
 *   Config.IndentClasses="['etc1','etc2','etc3']"
 *   >
 *   <prop:Config.CustomStyles>
 *   {
 *     'Red Title'	: { Element : 'h3', Styles : { 'color' : 'Red' } },
 *     'Blue Title' : { Element : 'h3', Styles : { 'color' : 'Blue' } }
 *   }
 *   </prop:Config.CustomStyles>
 * </com:CSHtmlArea>
 * </code>
 * 
 * Note #2: The configurations MUST be in JavaScript sintax, i.e., every
 * configuration MUST be defined as in a JavaScript script file, arrays like JS
 * arrays (note the property 'IndentClasses'), objects like JS objects (note the
 * property 'CustomStyles').
 * 
 * 5) Custom ToolbarSets:
 * 
 * <code>
 * <com:CSHtmlArea ID="HtmlArea"
 *   Height="200px"
 *   Config.AutoGrowMax="300"
 *   Config.ToolbarCanCollapse="true"
 *   ToolbarSet="MyOwn"
 *   >
 *   <prop:Config.ToolbarSets.MyOwn>
 *   [
 *     ['Cut','Copy','Paste','PasteText','PasteWord','-','Print','SpellCheck'],
 *     '/',
 *     ['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript']
 *   ]
 *   </prop:Config.ToolbarSets.Claudio>
 * </com:CSHtmlArea>
 * </code>
 * 
 * Note #3: Note also in the example above the accordance with the JavaScript
 * sintax.
 * 
 * As you may know, CSHtmlArea, through FCKeditor API, enables the file browser
 * and uploads features. Since CSHtmlArea v1.1, it's possible to configure the
 * location of the user files directory. You just need to add an application
 * parameter to the application.xml file. See the example below:
 * 
 * application.xml:
 * 
 * <code>
 * ...
 * <parameters>
 *   <parameter id="CSHtmlAreaUserFilesPath" value="../userfiles" />
 * </parameters>
 * ...
 * </code>
 * 
 * Note #4: The parameter id MUST be "CSHtmlAreaUserFilesPath".
 * 
 * Note #5: The parameter value is the path of a directory that will store the
 * user files. It MUST be writable by the web server process. The path is
 * relative (PAY ATTENTION HERE!) to the application assets directory. Why
 * assets directory? Because this is the appropriate location for public files.
 * OK, in the example above, let's suppose our assets directory is located at
 * "/www/mysite.com/app/assets". Than "../userfiles" translated means
 * "/www/mysite.com/app/userfiles". Never forget this!
 * 
 * Note #6: After update this component in you system, before your tests, you
 * MUST delete all its resources that were generated in the application assets
 * directory.
 * 
 * @author Cláudio César Monteiro dos Santos Júnior <claudiocmsj@gmail.com>
 * @version $Revision: 1.2 $Date: 2010-01-20 14:21
 * @license GNU General Public License - GPLv3
 */
class CSHtmlArea extends TTextBox
{
	/**
	 * @var CSHtmlAreaConfigCollection
	 */
	private $_Config;
	
	/**
	 * @var TAttributeCollection
	 */
	private $_ToolbarSets;
	
	/**
	 * @return CSHtmlAreaConfigCollection
	 */
	public function getConfig () {
		if ($this->_Config === null)
			$this->_Config = new CSHtmlAreaConfigCollection();
		return $this->_Config;
	}

	/**
	 * @return string  Defaults to '100%'.
	 */
	public function getWidth()
	{
		return $this->getViewState('Width','100%');
	}

	/**
	 * @param string
	 */
	public function setWidth($value)
	{
		$this->setViewState('Width',TPropertyValue::ensureString($value),'100%');
	}

	/**
	 * @return string  Defaults to '350px'.
	 */
	public function getHeight()
	{
		return $this->getViewState('Height','350px');
	}

	/**
	 * @param string
	 */
	public function setHeight($value)
	{
		$this->setViewState('Height',TPropertyValue::ensureString($value),'350px');
	}

	/**
	 * @return string  Defaults to 'Default'.
	 */
	public function getToolbarSet()
	{
		return $this->getViewState('ToolbarSet','Default');
	}

	/**
	 * @param string
	 */
	public function setToolbarSet($value)
	{
		$value = TPropertyValue::ensureString($value);
		if ($value != 'Default' && $value != 'Basic')
			$value = strtolower($value);
		$this->setViewState('ToolbarSet',$value,'Default');
	}

	/**
	 * Overrides the parent implementation.
	 * TextMode for THtmlArea control is always 'MultiLine'
	 * @return string the behavior mode of the THtmlArea component.
	 */
	public function getTextMode()
	{
		return 'MultiLine';
	}
	
	/**
	 * Overrides the parent implementation.
	 * TextMode for THtmlArea is always 'MultiLine' and cannot be changed to others.
	 * @param string the text mode
	 */
	public function setTextMode($value)
	{
		throw new TInvalidOperationException("htmlarea_textmode_readonly");
	}
	
	/**
	 * @return boolean whether change of the content should cause postback. Return false if EnableVisualEdit is true.
	 */
	public function getAutoPostBack()
	{
		return $this->getEnableVisualEdit() ? false : parent::getAutoPostBack();
	}
	
	/**
	 * @return boolean whether to show WYSIWYG text editor. Defaults to true.
	 */
	public function getEnableVisualEdit()
	{
		return true;
	}
	
	/**
	 * Sets whether to show WYSIWYG text editor.
	 * @param boolean whether to show WYSIWYG text editor
	 */
	public function setEnableVisualEdit($value)
	{
		throw new TNotSupportedException('Property not supported.');
	}
	
	/**
	 * Gets the current culture.
	 * @return string current culture, e.g. en_AU.
	 */
	public function getCulture()
	{
		return $this->getViewState('Culture', '');
	}
	
	/**
	 * Sets the culture/language for the html area
	 * @param string a culture string, e.g. en_AU.
	 */
	public function setCulture($value)
	{
		$cultures = $this->getConfig()->getAvailableCultures();
		if (!array_key_exists($value,$cultures))
			throw new TInvalidDataValueException('Culture unsupported.');
		$this->getConfig()->AutoDetectLanguage = "false";
		$this->getConfig()->DefaultLanguage = $cultures[$value];
		$this->setViewState('Culture', $value, '');
	}
	
	/**
	 * @return TAttributeCollection
	 */
	public function getToolbarSets() {
		if ($this->_ToolbarSets === null)
			$this->_ToolbarSets =
				$this->getViewState('ToolbarSets',new TAttributeCollection());
		$this->setViewState('ToolbarSets',$this->_ToolbarSets);
		return $this->_ToolbarSets;
	}
	
	/**
	 * @param TAttributeCollection $Value
	 */
	public function setToolbarSets(TAttributeCollection $Value) {
		$this->setViewState('ToolbarSets',$Value);
	}
	
	/**
	 * Adds attribute name-value pairs to renderer.
	 * This method overrides the parent implementation by registering
	 * additional javacript code.
	 *
	 * @param THtmlWriter $writer The writer used for the rendering purpose
	 */
	protected function addAttributesToRender ($writer) {
		if ($this->getEnableVisualEdit() && $this->getEnabled(true)) {
			$writer->addAttribute('id',$this->getClientID());
			$this->registerEditorClientScript($writer);
		}
		$this->loadJavascriptLibrary();
		parent::addAttributesToRender($writer);
	}

	protected function loadJavascriptLibrary() {
		$scripts = $this->getPage()->getClientScript();
		if(!$scripts->isScriptFileRegistered('FCKeditor'))
		$scripts->registerScriptFile('FCKeditor',$this->getFCKeditorScriptUrl());
		if(!$scripts->isScriptFileRegistered('CSHtmlArea'))
		$scripts->registerScriptFile('CSHtmlArea',$this->getScriptUrl());
	}
	
	/**
	 * Registers the editor javascript file and code to initialize the editor.
	 */
	protected function registerEditorClientScript($writer)
	{
		$scripts = $this->getPage()->getClientScript();
		$options = TJavaScript::encode($this->getEditorOptions());
		$script = "new CSHtmlArea($options);";
		$scripts->registerEndScript('CSHtmlArea:'.$this->getClientID(),$script);
	}
	
	/**
	 * @return string The script URL.
	 */
	protected function getScriptUrl () {
		return $this->getScriptDeploymentUrl().'/CSHtmlArea.js';
	}
	
	/**
	 * @static
	 * @return string FCKeditor script URL.
	 */
	protected static function getFCKeditorScriptUrl () {
		return self::getFCKeditorScriptDeploymentUrl().'/fckeditor.js';
	}
	
	/**
	 * Gets the editor script base URL by publishing the tarred source via TTarAssetManager.
	 * 
	 * @static
	 * @return string URL base path to the published editor script
	 */
	protected static function getScriptDeploymentUrl () {
		$tarfile = dirname(__FILE__)."/CSHtmlArea.tar";
		$md5sum = dirname(__FILE__)."/CSHtmlArea.md5";
		if (!file_exists($md5sum)) {
			$fp = @fopen($md5sum,'w');
			if ($fp === false)
				throw new TApplicationException('Filesystem error.');
			fputs($fp,md5_file($tarfile)."  CSHtmlArea.tar\n");
			fclose($fp);
		}
		if ($tarfile === null || $md5sum === null)
			throw new TConfigurationException('htmlarea_tarfile_invalid');
		return Prado::getApplication()->getAssetManager()->publishTarFile($tarfile,$md5sum);
	}
	
	/**
	 * Gets the editor script base URL by publishing the tarred source via TTarAssetManager.
	 * 
	 * @static
	 * @return string URL base path to the published editor script
	 */
	protected static function getFCKeditorScriptDeploymentUrl()
	{
		return self::getScriptDeploymentUrl().'/fckeditor/';
	}
	
	/**
	 * Default editor options gives basic tool bar only.
	 * @return array editor initialization options.
	 */
	protected function getEditorOptions () {
		$options['ID']                       = $this->getClientID();
		$options['BasePath']                 = $this->getBasePath();
		$options['Width']                    = $this->getWidth();
		$options['Height']                   = $this->getHeight();
		$options['ToolbarSet']               = $this->getToolbarSet();
		$options['CustomConfigurationsPath'] = $this->getCustomConfigFileUrl();
		return $options;
	}
	
	/**
	 * Gets the name of the javascript class responsible for performing postback for this control.
	 * This method overrides the parent implementation.
	 * @return string the javascript class name
	 */
	protected function getClientClassName()
	{
		return 'CSHtmlArea';
	}

	/**
	 * @static
	 * @return string  The base path
	 */
	public static function getBasePath () {
		return self::getFCKeditorScriptDeploymentUrl();	
	}
	
	/**
	 * @return string
	 */
	protected function getCustomConfigFileUrl () {
		self::createUserFilesUrlIncludeFile();
		self::createUserFilesAbsolutePathIncludeFile();
		$dir = $this->getScriptDeploymentPath();
		$filename = 'fckconfig__'.
				$this->getPage()->getPagePath().'__'.$this->getClientID().'.js';
		$path = "$dir/$filename";
		if (($fp = @fopen($path,'w')) === false)
			throw new TApplicationException('Filesystem error');
		$availableProps = $this->getConfig()->getAvailableProperties();
		foreach ($this->getConfig() as $key=>$value) {
			foreach ($availableProps as $property=>$type) {
				if (strtolower($key) == strtolower($property)) {
					switch ($type) {
						case 'string':
							$line = "FCKConfig.$property = \"$value\";\r\n";
							break;
						case 'object':
							$line = "FCKConfig.$property = $value\r\n";
							break;
						case 'array':
							if ($property == 'ToolbarSets') {
								$line = '';
								foreach ($value as $key2=>$value2) {
									$line .= "FCKConfig.ToolbarSets['$key2'] = $value2;\r\n";
								}
							}
							else
								$line = "FCKConfig.$property = $value;\r\n";
							break;
						default:
							$line = "FCKConfig.$property = $value;\r\n";
							break;
					}
					if (@fputs($fp,$line) === false)
						throw new TApplicationException('Filesystem error.');
				}
			}
			unset($property,$type);
		}
		unset($availableProps,$key,$value,$line);
		fclose($fp);
		$url = $this->getScriptDeploymentUrl()."/$filename";
		return $url;
	}
	
	/**
	 * This method overrides the parent implementation.
	 */
	public function focus () {
		$this->getConfig()->StartupFocus = true;
	}
	
	/**
	 * @static
	 * @return string
	 */
	protected static function getScriptDeploymentPath () {
		$path = dirname(__FILE__)."/CSHtmlArea.tar";
		$assetMgr = Prado::getApplication()->getAssetManager();
		$path = $assetMgr->getPublishedPath($path);
		return str_replace("\\","/",dirname($path));
	}
	
	/**
	 * @static
	 * @return string
	 */
	protected static function getUserFilesAbsolutePath () {
		$App = Prado::getApplication();
		$AssetMgr = $App->getAssetManager();
		$Params = $App->getParameters();
		$paramName = 'CSHtmlAreaUserFilesPath';
		$userFilesAbsolutePath =
			$Params->contains($paramName)
			? str_replace("\\","/",$AssetMgr->getBasePath().'/'.$Params->itemAt($paramName))
			: self::getScriptDeploymentPath().'/userfiles';
		unset($paramName);
		$path = preg_replace("/^(.+?)[\\/]\$/","\\1",$userFilesAbsolutePath);
		$parts = explode('/',$path);
		$path = $parts[0];
		if (count($parts) > 0) {
			for ($i = 1; $i < count($parts); $i++) {
				$path .= '/'.$parts[$i];
				if (($realpath = @realpath($path)) === false) {
					unset($realpath);
					for ($j = $i+1; $j < count($parts); $j++) {
						$path .= '/'.$parts[$j];
					}
					unset($j);
					break;
				}
				$path = $realpath;
			}
			unset($i,$realpath);
		}
		$userFilesAbsolutePath = str_replace("\\","/",$path);
		unset($parts,$path);
		return $userFilesAbsolutePath;
	}

	/**
	 * @static
	 * @return string The include's path
	 */
	protected static function createUserFilesAbsolutePathIncludeFile () {
		$userFilesAbsolutePath = self::getUserFilesAbsolutePath();
		$include =
			self::getScriptDeploymentPath()."/UserFilesAbsolutePath.inc.php";
		if (($fp = @fopen($include,'w')) === false)
			throw new TApplicationException('Filesystem error.');
		$content = "<?php return '$userFilesAbsolutePath'; ?>";
		fputs($fp,$content);
		unset($content,$userFilesAbsolutePath);
		fclose($fp);
		return $include;
	}
	
	/**
	 * @static
	 * @return string
	 */
	protected static function getUserFilesUrl () {
		$url = $_SERVER['SCRIPT_NAME'];
		$path = str_replace("\\","/",$_SERVER['SCRIPT_FILENAME']);
		$rootpath = str_replace(urldecode($url),'',$path);
		unset($url,$path);
		$userFilesAbsolutePath = self::getUserFilesAbsolutePath();
		$userFilesUrl = str_replace($rootpath,'',$userFilesAbsolutePath);
		unset($rootpath,$userFilesAbsolutePath);
		return $userFilesUrl;
	}
	
	/**
	 * @static
	 * @return string
	 */
	protected static function createUserFilesUrlIncludeFile () {
		$userFilesUrl = self::getUserFilesUrl();
		$include =
			self::getScriptDeploymentPath()."/UserFilesUrl.inc.php";
		if (($fp = @fopen($include,'w')) === false)
			throw new TApplicationException('Filesystem error.');
		$content = "<?php return '$userFilesUrl'; ?>";
		fputs($fp,$content);
		unset($content,$userFilesUrl);
		fclose($fp);
		return $include;
	}
}
?>