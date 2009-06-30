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

class language extends PageList
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
        $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='language'");
        $data = $cmd->query();
        $data = $data->readAll();

        $template = array();

        foreach($data as $d)
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.''.$d['param'].DIRECTORY_SEPARATOR.'install.xml');

            $version = $doc->getElementByTagName('version');
            $date = $doc->getElementByTagName('creationDate');
            $description = $doc->getElementByTagName('description');
            $author = $doc->getElementByTagName('author');
            $license = $doc->getElementByTagName('license');

            $template[] = array('id' => $d['id'],
                                'name' => $d['name'],
                                'default' => $d['default'],
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

    public function onDefault($sender, $param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        $koMsg = '';
        $cbChecked = 0;

        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            $cbChecked++;
        }

        if($cbChecked==0)
        {
            $koMsg = Prado::localize('Select one item');
        }
        elseif($cbChecked==1)
        {
            foreach($cbs as $cb)
            {
                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {

                    $cmd=$this->db->createCommand("UPDATE hr_install SET `default`='0' WHERE type='language'");
                    $cmd->execute();

                    $cmd=$this->db->createCommand("UPDATE hr_install SET `default`='1' WHERE id=:id");
                    $cmd->bindParameter(":id",$cb->Value);
                    $cmd->execute();

                    $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE id=:id AND type='language'");
                    $cmd->bindParameter(":id",$cb->Value);
                    $data = $cmd->query();
                    $data = $data->read();

                    $lang = $data['param'];

                    $this->application->setGlobalState('lang',$lang);
                    $this->getApplication()->getGlobalization()->setCulture($this->application->getGlobalState('lang'));

                }
            }
        }
        else
        {
            $koMsg = Prado::localize('Select only one item');
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg);
        else
        $pBack = array('okMsg'=>Prado::localize('New default language updated'));
        $this->Response->redirect($this->Service->constructUrl('installation.language',$pBack));
    }


}

?>
