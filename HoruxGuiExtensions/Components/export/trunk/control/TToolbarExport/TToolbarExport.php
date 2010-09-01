<?php
/**
 * TToolbarExport class file.
 *
 * @author Thierry Forchelet <info[at]letux[dot]ch>
 * @link http://www.letux.ch/
 * @copyright Copyright &copy; 2009 Letux Sàrl
 * @license http://www.pradosoft.com/license/
 * @version 0.1
 */

class TToolbarExport extends TToolbarBox
{
	//! button add conf
	public function setAddConfVisible($flag)
	{
		$this->setViewState('AddConfVisible',$flag,false);
	}
	public function getAddConfVisible()
	{
		return $this->getViewState('AddConfVisible',false);
	}
	public function setAddConfUrl($url)
	{
		$this->setViewState('AddUrl',$url,'');
	}
	public function getAddConfUrl()
	{
		return $this->getViewState('AddUrl','');
	}

	//! button del conf
	public function setDelConfVisible($flag)
	{
		$this->setViewState('DelConfVisible',$flag,false);
	}
	public function getDelConfVisible()
	{
		return $this->getViewState('DelConfVisible',false);
	}

	//! button import
	public function setImportVisible($flag)
	{
		$this->setViewState('ImportVisible',$flag,false);
	}
	public function getImportVisible()
	{
		return $this->getViewState('ImportVisible',false);
	}
}
?>
