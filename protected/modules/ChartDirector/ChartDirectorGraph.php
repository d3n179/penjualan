<?php


class ChartDirectorGraph extends TImage {
	
	private $_assets_url;
	private $_graph;
	
	/**
	 * Constructor.
	
	 */
	public function __construct()
	{
		parent::__construct();
		$this->_assets_url = $this->publishAsset("ChartDirectorAssets");
		
	}
	
	public function setGraph($v)
	{
		$this->_graph = $v;
		$this->getApplication()->Session["GRAPH-".$this->getID()] = $v->makeChart2(PNG);
	}
	
	public function getGraph()
	{
		return $this->_graph;
	}
	
	
	public function onPrerender($param)
	{
		$chartURL = $this->_assets_url . "/chartdirectorimage.php?ChartID=" . $this->getID();
		$this->setImageUrl($chartURL);
		parent::onPreRender($param);
	}
}