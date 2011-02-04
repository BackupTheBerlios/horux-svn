<?php

Prado::using('horux.pages.login.sql');

class Login extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(isset($this->Request['enterkey']) && $this->Request['enterkey']==1 )
        {
            $authManager=$this->Application->getModule('Auth');
            if($authManager->login(strtolower($this->username->SafeText),$this->password->SafeText))
            {
                $this->log($this->username->SafeText." is logged in");

                $userID = Prado::getApplication()->getUser()->getUserID();

                $cmd=$this->db->createCommand("SELECT defaultPage FROM hr_superuser_group AS sg LEFT JOIN  hr_superusers AS s ON s.group_id=sg.id WHERE s.id=$userID");
                $data = $cmd->query();
                $dataUser = $data->read();
                $defaultPage = $dataUser['defaultPage'];

                if($defaultPage == '')
                    $this->Response->redirect($this->Service->constructUrl('controlPanel.ControlPanel',array('lang'=>$this->lang->getSelectedValue())));
                else
                    $this->Response->redirect($this->Service->constructUrl($defaultPage,array('lang'=>$this->lang->getSelectedValue())));

                //$this->Response->redirect($this->Service->constructUrl('controlPanel.ControlPanel',array('lang'=>$this->lang->getSelectedValue())));
            }
        }

        $this->getClientScript()->registerStyleSheetFile('loginCss','./themes/letux/css/login.css');

        $this->username->focus ();

        if(!$this->IsPostBack)
        {
            $this->lang->DataTextField='name';
            $this->lang->DataValueField='param';
            $this->lang->DataSource=$this->Data;
            $this->lang->dataBind();

            $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='language' AND `default`=1");
            $data = $cmd->query();
            $data = $data->read();

            $this->lang->setSelectedValue($data['param']);

        }

    }

    public function getData()
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='language'");
        $data = $cmd->query();
        $data = $data->readAll();

        $l[] = array('param' => 'default', 'name'=>Prado::localize('default'));
        foreach($data as $d)
        {
            $l[] = array('param' => $d['param'], 'name'=>$d['name']);
        }
        return $l;
    }

    public function onLogin($sender, $param)
    {
        $authManager=$this->Application->getModule('Auth');
        if(!$authManager->login(strtolower($this->username->SafeText),$this->password->SafeText))
            $param->IsValid=false;
        else
        {
            $this->log($this->username->SafeText." is logged in");

            $userID = Prado::getApplication()->getUser()->getUserID();

            $cmd=$this->db->createCommand("SELECT defaultPage FROM hr_superuser_group AS sg LEFT JOIN  hr_superusers AS s ON s.group_id=sg.id WHERE s.id=$userID");
            $data = $cmd->query();
            $dataUser = $data->read();
            $defaultPage = $dataUser['defaultPage'];


            if($defaultPage == '')
                $this->Response->redirect($this->Service->constructUrl('controlPanel.ControlPanel',array('lang'=>$this->lang->getSelectedValue())));
            else
                $this->Response->redirect($this->Service->constructUrl($defaultPage,array('lang'=>$this->lang->getSelectedValue())));
        }
    }
}
