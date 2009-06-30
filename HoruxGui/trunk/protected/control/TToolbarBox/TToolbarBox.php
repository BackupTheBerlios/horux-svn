<?php
/**
 * TToolbarBox class file.
 *
 * @author Jean-Luc Gyger <info[at]letux[dot]ch>
 * @link http://www.letux.ch/
 * @copyright Copyright &copy; 2009 Letux Sàrl
 * @license http://www.pradosoft.com/license/
 * @version 0.1
 */

class TToolbarBox extends TTemplateControl
{
	public function setTitle($title)
	{
		$this->setViewState('Title',$title,'');
	}

	public function getTitle()
	{
		return $this->getViewState('Title','');
	}

	public function setCssIcon($icon)
	{
		$this->setViewState('CssIcon',$icon,'');
	}

	public function getCssIcon()
	{
		return $this->getViewState('CssIcon','');
	}

	public function setIconAsset($icon)
	{
		$this->setViewState('IconAsset',$icon,'');
	}

	public function getIconAsset()
	{
		return $this->getViewState('IconAsset','');
	}

	//! button Edit
	public function setEditVisible($flag)
	{
		$this->setViewState('EditVisible',$flag,false);
	}
	public function getEditVisible()
	{
		return $this->getViewState('EditVisible',false);
	}
    
	//! button Add
	public function setAddVisible($flag)
	{
		$this->setViewState('AddVisible',$flag,false);
	}
	public function getAddVisible()
	{
		return $this->getViewState('AddVisible',false);
	}
	public function setAddUrl($url)
	{
		$this->setViewState('AddUrl',$url,'');
	}
	public function getAddUrl()
	{
		return $this->getViewState('AddUrl','');
	}
	//! button Del
	public function setDelVisible($flag)
	{
		$this->setViewState('DelVisible',$flag,false);
	}
	public function getDelVisible()
	{
		return $this->getViewState('DelVisible',false);
	}
	//! button Print
	public function setPrintVisible($flag)
	{
		$this->setViewState('PrintVisible',$flag,false);
	}
	public function getPrintVisible()
	{
		return $this->getViewState('PrintVisible',false);
	}
	public function setPrintUrl($url)
	{
		$this->setViewState('PrintUrl',$url,'');
	}
	public function getPrintUrl()
	{
		return $this->getViewState('PrintUrl','');
	}
	public function setJsClickPrint($url)
	{
		$this->setViewState('JsClickPrint',$url,false);
	}
	public function getJsClickPrint()
	{
		return $this->getViewState('JsClickPrint',false);
	}
	//! button Help
	public function setHelpVisible($flag)
	{
		$this->setViewState('HelpVisible',$flag,false);
	}
	public function getHelpVisible()
	{
		return $this->getViewState('HelpVisible',false);
	}

	//! button Apply
	public function setApplyVisible($flag)
	{
		$this->setViewState('ApplyVisible',$flag,false);
	}
	public function getApplyVisible()
	{
		return $this->getViewState('ApplyVisible',false);
	}
	//! button Refresh
	public function setRefreshVisible($flag)
	{
		$this->setViewState('RefreshVisible',$flag,false);
	}
	public function getRefreshVisible()
	{
		return $this->getViewState('RefreshVisible',false);
	}

	//! button cancel
	public function setCancelVisible($flag)
	{
		$this->setViewState('CancelVisible',$flag,false);
	}
	public function getCancelVisible()
	{
		return $this->getViewState('CancelVisible',false);
	}
	//! button save
	public function setSaveVisible($flag)
	{
		$this->setViewState('SaveVisible',$flag,false);
	}
	public function getSaveVisible()
	{
		return $this->getViewState('SaveVisible',false);
	}
	//! button attribute
	public function setAttributeVisible($flag)
	{
		$this->setViewState('AttributeVisible',$flag,false);
	}
	public function getAttributeVisible()
	{
		return $this->getViewState('AttributeVisible',false);
	}
	//! button unattribute
	public function setUnAttributeVisible($flag)
	{
		$this->setViewState('UnAttributeVisible',$flag,false);
	}
	public function getUnAttributeVisible()
	{
		return $this->getViewState('UnAttributeVisible',false);
	}
	//! button start
	public function setStartVisible($flag)
	{
		$this->setViewState('StartVisible',$flag,false);
	}
	public function getStartVisible()
	{
		return $this->getViewState('StartVisible',false);
	}
	//! button stop
	public function setStopVisible($flag)
	{
		$this->setViewState('StopVisible',$flag,false);
	}
	public function getStopVisible()
	{
		return $this->getViewState('StopVisible',false);
	}
	//! button add access
	public function setAddAccessVisible($flag)
	{
		$this->setViewState('AddAccessVisible',$flag,false);
	}
	public function getAddAccessVisible()
	{
		return $this->getViewState('AddAccessVisible',false);
	}
	//! button unattribute
	public function setUnInstallVisible($flag)
	{
		$this->setViewState('UnInstallVisible',$flag,false);
	}
	public function getUnInstallVisible()
	{
		return $this->getViewState('UnInstallVisible',false);
	}

	//! button default
	public function setDefaultVisible($flag)
	{
		$this->setViewState('DefaultVisible',$flag,false);
	}
	public function getDefaultVisible()
	{
		return $this->getViewState('DefaultVisible',false);
	}
}
?>