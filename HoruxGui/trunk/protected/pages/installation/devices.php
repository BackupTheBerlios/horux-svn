<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2007  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horux is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

class devices extends PageList
{

    public function onLoad($param)
    {
        parent::onLoad($param); 

        if(!$this->IsPostBack)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
        }
        
        if(isset($this->Request['okMsg']))
        {
          $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
          $this->displayMessage($this->Request['koMsg'], false);
        }
    }	

	public function getData()
	{
		$cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='device'");
		$data = $cmd->query();
		$data = $data->readAll();
		
		$template = array();
		
		foreach($data as $d)		
		{
			$doc=new TXmlDocument();
			$doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$d['param'].DIRECTORY_SEPARATOR.'install.xml');
			
			$version = $doc->getElementByTagName('version');  
			$date = $doc->getElementByTagName('creationDate');
			$description = $doc->getElementByTagName('description');
			$author = $doc->getElementByTagName('author');
			$license = $doc->getElementByTagName('license');
			$name = $doc->getElementByTagName('name');
			
			$template[] = array('id' => $d['id'],
								'name' => $name->getValue(),
								'version' => $version->getValue(),
								'date' => $date->getValue(),
								'description' => $description->getValue(),
								'author' => $author->getValue(),
								'license' => $license->getValue()
								);
		}
		
		return $template;
	}

    public function checkboxAllCallback($sender, $param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $isChecked = $sender->getChecked();

        foreach($cbs as $cb)
        {
           $cb->setChecked($isChecked);
        }

    }
}

?>
