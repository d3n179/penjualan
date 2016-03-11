<?php
/**
 * CSHtmlAreaConfigCollection class file.
 * 
 * @author Cláudio César Monteiro dos Santos Júnior <claudiocmsj[at]gmail[dot]com>
 * @version $Revision: 1.0 $Date: 2009-07-15 11:27
 */

/**
 * Includes CultureInfo class.
 */
Prado::using('System.I18N.core.CultureInfo');

/**
 * CSHtmlAreaConfigCollection class.
 *
 * @author Cláudio César Monteiro dos Santos Júnior <claudiocmsj[at]gmail[dot]com>
 * @version $Revision: 1.0 $Date: 2009-07-15 11:27
 */
class CSHtmlAreaConfigCollection extends TAttributeCollection {
	/**
	 * @see TMap::__construct()
	 */
	public function __construct($data = null, $readOnly = false) {
		parent::__construct($data,$readOnly);
		$this->add('ToolbarSets',new TAttributeCollection());
	}
	
	/**
	 * This method overrides the parent implementation.
	 * 
	 * @param mixed $key
	 * @param mixed $value
	 */
	public function add ($key, $value) {
		foreach (self::getAvailableProperties() as $k=>$v) {
			if (strtolower($key) == strtolower($k))
				return parent::add($key,$value);
		}
		throw new TNotSupportedException('Property not supported.');
	}
	
	/**
	 * @static
	 * @return array
	 */
	public static function getAvailableProperties () {
		return
			array(
			'CustomConfigurationsPath'     => 'string',
			'EditorAreaCSS'                => 'string',
			'EditorAreaStyles'             => 'string',
			'ToolbarComboPreviewCSS'       => 'string',
			'DocType'                      => 'string',
			'BaseHref'                     => 'string',
			'FullPage'                     => 'boolean',
			'StartupShowBlocks'            => 'boolean',
			'Debug'                        => 'boolean',
			'AllowQueryStringDebug'        => 'boolean',
			'SkinPath'                     => 'string',
			'SkinEditorCSS'                => 'string',
			'SkinDialogCSS'                => 'string',
			'PreloadImages'                => 'array',
			'PluginsPath'                  => 'string',
			'AutoGrowMax'                  => 'integer',
			'AutoDetectLanguage'           => 'boolean',
			'DefaultLanguage'              => 'string',
			'ContentLangDirection'         => 'string',
			'ProcessHTMLEntities'          => 'boolean',
			'IncludeLatinEntities'         => 'boolean',
			'IncludeGreekEntities'         => 'boolean',
			'ProcessNumericEntities'       => 'boolean',
			'AdditionalNumericEntities'    => 'string',
			'FillEmptyBlocks'              => 'boolean',
			'FormatSource'                 => 'boolean',
			'FormatOutput'                 => 'boolean',
			'FormatIndentator'             => 'string',
			'EMailProtection'              => 'string',
			'EMailProtectionFunction'      => 'string',
			'StartupFocus'                 => 'boolean',
			'ForcePasteAsPlainText'        => 'boolean',
			'AutoDetectPasteFromWord'      => 'boolean',
			'ShowDropDialog'               => 'boolean',
			'ForceSimpleAmpersand'         => 'boolean',
			'TabSpaces'                    => 'integer',
			'ShowBorders'                  => 'boolean',
			'SourcePopup'                  => 'boolean',
			'ToolbarStartExpanded'         => 'boolean',
			'ToolbarCanCollapse'           => 'boolean',
			'IgnoreEmptyParagraphValue'    => 'boolean',
			'FloatingPanelsZIndex'         => 'integer',
			'HtmlEncodeOutput'             => 'boolean',
			'TemplateReplaceAll'           => 'boolean',
			'TemplateReplaceCheckbox'      => 'boolean',
			'ToolbarLocation'              => 'string',
			'ToolbarSets'                  => 'array',
			'EnterMode'                    => 'string',
			'ShiftEnterMode'               => 'string',
			'Keystrokes'                   => 'array',
			'ContextMenu'                  => 'array',
			'BrowserContextMenuOnCtrl'     => 'boolean',
			'BrowserContextMenu'           => 'boolean',
			'EnableMoreFontColors'         => 'boolean',
			'FontColors'                   => 'string',
			'FontFormats'                  => 'string',
			'FontNames'                    => 'string',
			'FontSizes'                    => 'string',
			'StylesXmlPath'                => 'string',
			'TemplatesXmlPath'             => 'string',
			'SpellChecker'                 => 'string',
			'IeSpellDownloadUrl'           => 'string',
			'SpellerPagesServerScript'     => 'string',
			'FirefoxSpellChecker'          => 'boolean',
			'MaxUndoLevels'                => 'integer',
			'DisableObjectResizing'        => 'boolean',
			'DisableFFTableHandles'        => 'boolean',
			'LinkDlgHideTarget'            => 'boolean',
			'LinkDlgHideAdvanced'          => 'boolean',
			'ImageDlgHideLink'             => 'boolean',
			'ImageDlgHideAdvanced'         => 'boolean',
			'FlashDlgHideAdvanced'         => 'boolean',
			'ProtectedTags'                => 'string',
			'BodyId'                       => 'string',
			'BodyClass'                    => 'string',
			'DefaultStyleLabel'            => 'string',
			'DefaultFontFormatLabel'       => 'string',
			'DefaultFontLabel'             => 'string',
			'DefaultFontSizeLabel'         => 'string',
			'DefaultLinkTarget'            => 'string',
			'CleanWordKeepsStructure'      => 'boolean',
			'RemoveFormatTags'             => 'string',
			'RemoveAttributes'             => 'string',
			'CustomStyles'                 => 'object',
			'CoreStyles'                   => 'object',
			'IndentLength'                 => 'integer',
			'IndentUnit'                   => 'string',
			'IndentClasses'                => 'array',
			'JustifyClasses'               => 'array',
			'LinkBrowser'                  => 'boolean',
			'LinkBrowserURL'               => 'string',
			'LinkBrowserWindowWidth'       => 'float',
			'LinkBrowserWindowHeight'      => 'float',
			'ImageBrowser'                 => 'boolean',
			'ImageBrowserURL'              => 'string',
			'ImageBrowserWindowWidth'      => 'float',
			'ImageBrowserWindowHeight'     => 'float',
			'FlashBrowser'                 => 'boolean',
			'FlashBrowserURL'              => 'string',
			'FlashBrowserWindowWidth'      => 'float',
			'FlashBrowserWindowHeight'     => 'float',
			'LinkUpload'                   => 'boolean',
			'LinkUploadURL'                => 'string',
			'LinkUploadAllowedExtensions'  => 'string',
			'LinkUploadDeniedExtensions'   => 'string',
			'ImageUpload'                  => 'boolean',
			'ImageUploadURL'               => 'string',
			'ImageUploadAllowedExtensions' => 'string',
			'ImageUploadDeniedExtensions'  => 'string',
			'FlashUpload'                  => 'boolean',
			'FlashUploadURL'               => 'string',
			'FlashUploadAllowedExtensions' => 'string',
			'FlashUploadDeniedExtensions'  => 'string',
			'SmileyPath'                   => 'string',
			'SmileyImages'                 => 'array',
			'SmileyColumns'                => 'integer',
			'SmileyWindowWidth'            => 'integer',
			'SmileyWindowHeight'           => 'integer',
			'BackgroundBlockerColor'       => 'string',
			'BackgroundBlockerOpacity'     => 'float',
			'MsWebBrowserControlCompat'    => 'boolean',
			'PreventSubmitHandler'         => 'boolean'
		);
	}
	
	/**
	 * @static
	 * @return array The FCKeditor's available languages.
	 */
	public static function getFCKeditorsLanguages () {
		return
			array('af','ar','eu','bn','bg','ca','zh-cn','zh','hr','cs','da','nl','eo',
			      'fo','fi','fr','gl','de','gr','hi','hu','it','jp','ko','lt','no',
			      'fa','pl','pt-br','pt','ro','ru','sr','sr-latn','sk','sl','es','sv',
			      'th','tr','uk','vi');
	}
	
	/**
	 * @static
	 * @return array
	 */
	public static function getAvailableCultures () {
		//FCKeditor's available languages
		$fck = self::getFCKeditorsLanguages();
		//Prado's available languages
		$prado = CultureInfo::getCultures();
		//Gets the intersection through comparison
		$intersection = array();
		foreach ($prado as $p) {
			foreach ($fck as $f) {
				if (eregi("^".sql_regcase($p)."\$",str_replace('-','_',$f)))
					$intersection[$p] = $f;
			}
		}
		unset($fck,$prado);
		//Include languages that were not included, but are available.
		$intersection['ja'] = 'jp';
		$intersection['ja_JP'] = 'jp';
		$intersection['ja_JP_TRADITIONAL'] = 'jp';
		return $intersection;
	}
}
?>