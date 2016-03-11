<?php
/**
* using see example code followup:
* use inline
* <code>
<com:Application.Controls.LightWindow.TLightWindow 
    Href="#<%=$this->LightWindowContent->getClientID()%>"
    Title="show lightwindow"
    Height="350"
    Width="600"
    Top="300"/>
<com:TPanel ID="LightWindowContent">
<div>
    <com:TActiveTextBox ID="txtInlineContent"/>
        content inline
    <com:TActiveButton OnCallback="Page.btnCallback" Text="CallBack">
        <prop:ClientSide
        OnLoading=""
        OnComplete="myLightWindow.deactivate();" />
    </com:TActiveButton>
</div>
</com:TPanel>
* </code>
* 
* <code>
* use gallery
<com:Application.Controls.LightWindow.TLightWindow     
    Caption="Whatever it is, it is still a pretty cool picture. Whatever it is, it is still a pretty cool picture. Whatever it is, it is still a pretty cool picture. Whatever it is, it is still a pretty cool picture."
    Author="Unknown"
    Title="What is this Plant?"
    Rel=""
    Href="gallery/image-5.jpg"/>
* </code>
* 
*/
class TLightWindow extends TTemplateControl {
    protected $params;            // The params for the window
    protected $href;            // The hyperlink for the window
    protected $title;             // Title of window
    protected $author;             // Author of window
    protected $caption;         // The Caption for the window
    protected $rel;             // Set the rel tag
    protected $top;             // Top position of the window
    protected $left;             // Left position of the window
    protected $type;             // The type of the window you want to change it too
    protected $showImages;         // How many images to show
    protected $height;             // The height of the window
    protected $width;             // The width of the window
    protected $loadingAnimation;     // Make the Loading Cover skip the animation
    protected $iframeEmbed;         // Emebed the Media into an iframe instead of a div
    protected $form;             // The name of the form
    
    public function setHref($value){
        $this->href = $value;
    }
    
    public function getHref(){
        return $this->href;
    }
    
    public function setTitle($value){
        $this->title = $value;
    }
    
    public function getTitle(){
        return $this->title;
    }
    
    public function setAuthor($value){
        $this->author = $value;
    }
    
    public function getAuthor(){
        return $this->author;
    }
    
    public function setCaption($value){
        $this->caption = $value;
    }
    
    public function getCaption(){
        return $this->caption;
    }
    
    public function setRel($value){
        $this->rel = $value;
    }
    
    public function getRel(){
        return $this->rel;
    }
    
    public function setTop($value){
        $this->top = $value;
    }
    
    public function getTop(){
        return $this->top;
    }
    
    public function setLeft($value){
        $this->left = $value;
    }
    
    public function getLeft(){
        return $this->left;
    }
    
    public function setType($value){
        $this->type = $value;
    }
    
    public function getType(){
        return $this->type;
    }
    
    public function setShowImages($value){
        $this->showImages = $value;
    }
    
    public function getShowImages(){
        return $this->showImages;
    }
    
    public function setHeight($value){
        $this->height = $value;
    }
    
    public function getHeight(){
        return $this->height;
    }
    
    public function setWidth($value){
        $this->width = $value;
    }
    
    public function getWidth(){
        return $this->width;
    }
    
    public function setLoadingAnimation($value){
        $this->loadingAnimation = $value;
    }
    
    public function getLoadingAnimation(){
        return $this->loadingAnimation;
    }
    
    public function setIframeEmbed($value){
        $this->iframeEmbed = $value;
    }
    
    public function getIframeEmbed(){
        return $this->iframeEmbed;
    }
    
    public function setForm($value){
        $this->form = $value;
    }
    
    public function getForm(){
        return $this->form;
    }
    
    public function addParam($value){
        $this->params[] = $value;
    }
    
    public function getParams(){
        if (is_array($this->params))
            return implode(',', $this->params);
        return '';
    }
    
    public function onInit($param){
        parent::onInit($param);
        if ($this->Height) $this->addParam('lightwindow_height='.$this->Height);
        if ($this->Width) $this->addParam('lightwindow_width='.$this->Width);
        if ($this->Top) $this->addParam('lightwindow_top='.$this->Top);
        if ($this->Left) $this->addParam('lightwindow_left='.$this->Left);
        if ($this->ShowImages) $this->addParam('lightwindow_show_images='.$this->ShowImages);
        if ($this->Form) $this->addParam('lightwindow_form='.$this->Form);
        if ($this->LoadingAnimation) $this->addParam('lightwindow_loading_animation='.$this->LoadingAnimation);
        if ($this->IframeEmbed) $this->addParam('lightwindow_iframe_embed='.$this->IframeEmbed);      
        if ($this->Type) $this->addParam('lightwindow_type='.$this->Type);
    }
    
    public function onLoad($param){
        parent::onLoad($param);
        
    }
    
    public function onPreRender($param){
        $scripts = $this->getPage()->getClientScript();        
        if(!$scripts->isScriptFileRegistered('lightwindow.js'))
            $scripts->registerScriptFile('lightwindow.js', $this->getScriptUrl());
        if(!$scripts->isStyleSheetFileRegistered('lightwindow.css'))
            $scripts->registerStyleSheetFile('lightwindow.css', $this->getStyleSheetUrl());
        $this->applyJavascriptFixes();        
    }
    
    /**
     * @return string LightWindow script URL.
     */
    protected function getScriptUrl()
    {
        return $this->getScriptDeploymentPath().'/LightWindow/javascript/lightwindow.js';
    }
    
    protected function getStyleSheetUrl()
    {
        return $this->getScriptDeploymentPath().'/LightWindow/css/lightwindow.css';
    }
    
    /**
     * Gets the editor script base URL by publishing the tarred source via TTarAssetManager.
     * @return string URL base path to the published editor script
     */
    protected function getScriptDeploymentPath()
    {
        $tarfile = Prado::getPathOfNamespace('Application.modules.LightWindow.LightWindow', '.tar');
        $md5sum = Prado::getPathOfNamespace('Application.modules.LightWindow.LightWindow', '.md5');
        if($tarfile===null || $md5sum===null)
            throw new TConfigurationException('LightWindow tarfile invalid');
        $url = $this->getApplication()->getAssetManager()->publishTarFile($tarfile, $md5sum);
        return $url;
    }
    
    protected function applyJavascriptFixes()
    {
        $scripts = $this->getPage()->getClientScript();
        $path = $this->getScriptDeploymentPath();
        $js = <<<EOD
/*-----------------------------------------------------------------------------------------------*/

Event.observe(window, 'load', lightwindowInit, false);

//
//    Set up all of our links
//
var myLightWindow = null;
function lightwindowInit() {
    myLightWindow = new lightwindow({
        overlay : {
                opacity : 0.7,
                image : '$path/LightWindow/images/black.png',
                presetImage : '$path/LightWindow/images/black-70.png'
            },
        skin: {
                main :     '<div id="lightwindow_container" >'+
                            '<div id="lightwindow_title_bar" >'+
                                '<div id="lightwindow_title_bar_inner" >'+
                                    '<span id="lightwindow_title_bar_title"></span>'+
                                    '<a id="lightwindow_title_bar_close_link" >close</a>'+
                                '</div>'+
                            '</div>'+
                            '<div id="lightwindow_stage" >'+
                                '<div id="lightwindow_contents" >'+
                                '</div>'+
                                '<div id="lightwindow_navigation" >'+
                                    '<a href="#" id="lightwindow_previous" >'+
                                        '<span id="lightwindow_previous_title"></span>'+
                                    '</a>'+
                                    '<a href="#" id="lightwindow_next" >'+
                                        '<span id="lightwindow_next_title"></span>'+
                                    '</a>'+
                                    '<iframe name="lightwindow_navigation_shim" id="lightwindow_navigation_shim" src="javascript:false;" frameBorder="0" scrolling="no"></iframe>'+
                                '</div>'+                                
                                '<div id="lightwindow_galleries">'+
                                    '<div id="lightwindow_galleries_tab_container" >'+
                                        '<a href="#" id="lightwindow_galleries_tab" >'+
                                            '<span id="lightwindow_galleries_tab_span" class="up" >Galleries</span>'+
                                        '</a>'+
                                    '</div>'+
                                    '<div id="lightwindow_galleries_list" >'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                            '<div id="lightwindow_data_slide" >'+
                                '<div id="lightwindow_data_slide_inner" >'+
                                    '<div id="lightwindow_data_details" >'+
                                        '<div id="lightwindow_data_gallery_container" >'+
                                            '<span id="lightwindow_data_gallery_current"></span>'+
                                            ' of '+
                                            '<span id="lightwindow_data_gallery_total"></span>'+
                                        '</div>'+
                                        '<div id="lightwindow_data_author_container" >'+
                                            'by <span id="lightwindow_data_author"></span>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div id="lightwindow_data_caption" >'+
                                    '</div>'+
                                '</div>'+
                            '</div>'+
                        '</div>',    
                loading :     '<div id="lightwindow_loading" >'+
                                '<img src="$path/LightWindow/images/ajax-loading.gif" alt="loading" />'+
                                '<span>Loading or <a href="javascript: myLightWindow.deactivate();">Cancel</a></span>'+
                                '<iframe name="lightwindow_loading_shim" id="lightwindow_loading_shim" src="javascript:false;" frameBorder="0" scrolling="no"></iframe>'+
                            '</div>',
                iframe :     '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'+
                            '<html xmlns="http://www.w3.org/1999/xhtml">'+
                                '<body>'+
                                    '{body_replace}'+
                                '</body>'+
                            '</html>',
                gallery : {
                    top :        '<div class="lightwindow_galleries_list">'+
                                    '<h1>{gallery_title_replace}</h1>'+
                                    '<ul>',
                    middle :             '<li>'+
                                            '{gallery_link_replace}'+
                                        '</li>',
                    bottom :         '</ul>'+
                                '</div>'
                }                
        }
    });
}
EOD;
        if(!$scripts->isEndScriptRegistered('LightWindow:fix'))
            $scripts->registerEndScript('LightWindow:fix', $js);
    }
}

class TLightWindowType extends TEnumerable{
    const INLINE = 'inline';
    const MEDIA = 'media';
    const IMAGE = 'image';
    const EXTERNAL = 'external';
    const FORM = 'form';
}
?>