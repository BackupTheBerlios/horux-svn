<?php

class template extends PageList
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
        $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='template'");
        $data = $cmd->query();
        $data = $data->readAll();

        $template = array();

        foreach($data as $d)
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$d['name'].DIRECTORY_SEPARATOR.'install.xml');

            $version = $doc->getElementByTagName('version');
            $date = $doc->getElementByTagName('creationDate');
            $description = $doc->getElementByTagName('description');
            $author = $doc->getElementByTagName('author');
            $license = $doc->getElementByTagName('license');
            $name = $doc->getElementByTagName('name');

            $template[] = array('id' => $d['id'],
                                'name' => $name->getValue(),
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

                    $cmd=$this->db->createCommand("UPDATE hr_install SET `default`='0' WHERE type='template'");
                    $cmd->execute();

                    $cmd=$this->db->createCommand("UPDATE hr_install SET `default`='1' WHERE id=:id");
                    $cmd->bindValue(":id",$cb->Value);
                    $cmd->execute();
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
        $pBack = array('okMsg'=>Prado::localize('New default template updated'));
        $this->Response->redirect($this->Service->constructUrl('installation.template',$pBack));
    }


}

?>
