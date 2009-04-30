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

class componentconfig extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
			$id = $this->Request["id"];

			$cmd = $this->db->createCommand( "SELECT i.name FROM hr_install AS i LEFT JOIN hr_component AS c ON c.id_install=i.id WHERE i.id=:id AND c.parentmenu=0" );
			$cmd->bindParameter(":id",$id,PDO::PARAM_INT);
	
			$cmd = $cmd->query();
			$data = $cmd->read();
			$this->cname->Text = $data["name"];

			$xml = ".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."pages".DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR.$data["name"].DIRECTORY_SEPARATOR."config.xml";

			if(file_exists($xml))
			{
				$this->Repeater->DataSource = $this->setDataFromXml($xml);
        		$this->Repeater->dataBind();
			}
			else
			{
				$this->Apply->setEnabled(false);
				$this->Save->setEnabled(false);

			}
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

	protected function setDataFromDb($config)
	{
		return unserialize($config);
	}

	protected function setDataFromXml($xml)
	{
		$a = array();

		$doc=new TXmlDocument();
		$doc->loadFromFile($xml);

		$parameters = $doc->getElementByTagName('parameters'); 

		if($parameters)
		{
			$parameters = $parameters->getElements();
			foreach($parameters as $parameter)
			{
				$id = $parameter->getAttribute('id');
				$value = $parameter->getAttribute('value');
				$name = Prado::localize($parameter->getAttribute('Name'));
				$rt = Prado::localize($parameter->getAttribute('RightText'));
	
				$a[] = array("id"=>$id, "value"=>$value, "Name"=>$name,"RightText"=>$rt);
			}
		}
		else
		{
			$this->Apply->setEnabled(false);
			$this->Save->setEnabled(false);
		}
		return $a;
	}

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $id = $this->Request["id"];
            $pBack = array('okMsg'=>Prado::localize('The configuration was saved successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('installation.componentconfig', $pBack));
          }
          else
          {
            $pBack = array('koMsg'=>Prado::localize('The configuration was not saved'));
          }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The configuration was saved successfully'));
          }
          else
            $pBack = array('koMsg'=>Prado::localize('The configuration was not saved'));
          $this->Response->redirect($this->Service->constructUrl('installation.components',$pBack));
        }
    }

    protected function saveData()
    {
		$key = $this->Request->getKeys() ;

		$res = array();

		for($i=0; $i<count($key); $i++)
		{
			if(strpos($key[$i], "hidden_") !== FALSE)
			{
				$res[$this->Request[$key[$i]]] = $this->Request[$key[$i+1]];
			}
		}

		$id = $this->Request["id"];

		$cmd = $this->db->createCommand( "SELECT i.name FROM hr_install AS i LEFT JOIN hr_component AS c ON c.id_install=i.id WHERE i.id=:id AND c.parentmenu=0" );
		$cmd->bindParameter(":id",$id,PDO::PARAM_INT);

		$cmd = $cmd->query();
		$data = $cmd->read();
		$this->cname->Text = $data["name"];

		$xml = ".".DIRECTORY_SEPARATOR."protected".DIRECTORY_SEPARATOR."pages".DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR.$data["name"].DIRECTORY_SEPARATOR."config.xml";

		if(file_exists($xml))
		{
			$doc=new TXmlDocument();
			$doc->loadFromFile($xml);
	
			$parameters = $doc->getElementByTagName('parameters'); 
			$parameters = $parameters->getElements();
			foreach($parameters as $parameter)
			{
				$k = $parameter->getAttribute('id');
				$parameter->setAttribute('value', $res[$k]);
			}

			$doc->saveToFile($xml); 
		}

		return true;

    }

}

?>
