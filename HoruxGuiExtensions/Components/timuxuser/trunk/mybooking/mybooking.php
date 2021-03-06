<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class mybooking extends PageList
{
    protected $userId = 0;
    protected $employee = null;

    public function onLoad($param)
    {
        parent::onLoad($param);


        $app = $this->getApplication();
        $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();

        $cmd=$this->db->createCommand("SELECT user_id FROM hr_superusers WHERE id=$usedId");
        $data = $cmd->query();
        $dataUser = $data->read();
        $this->userId = $dataUser['user_id'];

        $this->employee = new employee($this->userId );


        $this->tbb->setTitle( Prado::localize("My sign in/out")." - ".$this->employee->getFullName() );


        if(!$this->IsPostBack)
        {
            $cmd=$this->db->createCommand("SELECT t.date FROM hr_tracking AS t WHERE t.id_user=".$this->userId." ORDER BY t.date LIMIT 0,1");
            $data = $cmd->query();
            $data = $data->readAll();

            $year = date("Y");
            if(count($data)>0)
            {
                $year = explode("-",$data[0]['date']);
                $year = $year[0];
            }
            $currentYear = date("Y");

            $yearList = array();
            for($i=$year; $i<= $currentYear;$i++ )
            {
                $yearList[] = array('Value'=>$i, 'Text'=>$i);
            }

            $this->FilterYear->DataSource=$yearList;
            $this->FilterYear->dataBind();

            if(Prado::getApplication()->getSession()->contains($this->getApplication()->getService()->getRequestedPagePath().'FilterYear'))
            {
                $FilterYear= $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'];
                $FilterMonth = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'];
            }
            else
            {
                $FilterYear= date('Y');
                $FilterMonth = date('n');
            }

            $FilterStatus = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'];

            if($FilterStatus)
                $this->FilterStatus->setSelectedValue($FilterStatus);
                
            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);

            if($FilterMonth)
                $this->FilterMonth->setSelectedValue($FilterMonth);

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
        $status = $this->FilterStatus->getSelectedValue();

        $from =  $this->FilterYear->getSelectedValue()."-".$this->FilterMonth->getSelectedValue()."-1";
        $day = date("t", mktime(0,0,0,(int)$this->FilterMonth->getSelectedValue(),1,(int)$this->FilterYear->getSelectedValue()));
        $until = $this->FilterYear->getSelectedValue()."-".$this->FilterMonth->getSelectedValue()."-".$day;

        return $this->employee->getBookings($status, $from, $until);

    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }


    public function selectionChangedStatus($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedYear($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedMonth($sender, $param)
    {
        $this->onRefresh($sender, $param);
    }
    
    public function onRefresh($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'] = $this->FilterStatus->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'] = $this->FilterYear->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'] = $this->FilterMonth->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

            $signInText =  Prado::localize("Sign in");
            $signOutText =  Prado::localize("Sign out");

            if($item->DataItem['internet'])
            {
                $signInText = "* ".$signInText;
                $signOutText = "* ".$signOutText;
                $item->aaction->action->ForeColor = "green";
            }

            $item->ddate->date->Text = $this->dateFromSql($item->DataItem['date']);

            if($item->DataItem['action'] == 255) {
                $item->aaction->action->Text = $signInText;

                $cmd=$this->db->createCommand("SELECT * FROM hr_timux_booking_bde WHERE tracking_id=:trackingId");
                $cmd->bindValue("trackingId", $item->DataItem['id']);

                $data = $cmd->query();
                $data = $data->read();

                if($data) {
                    $item->aactionr->actionr->Text = $data['BDE1'];
                }

            }
            if($item->DataItem['action'] == 254)
                $item->aaction->action->Text = $signOutText;

            if($item->DataItem['action'] == 100)
            {
                $actionReason = explode("_",$item->DataItem['actionReason']);

                if($actionReason[0] > 0)
                {
                    $cmd=$this->db->createCommand("SELECT name, signtype FROM hr_timux_timecode WHERE id=".$actionReason[0]);
                    $data = $cmd->query();
                    $data = $data->read();

                    if(isset($actionReason[1]))
                    {
                        if($actionReason[1] == "IN")
                            $item->aaction->action->Text = $signInText;
                        if($actionReason[1] == "OUT")
                            $item->aaction->action->Text = $signOutText;
                    }
                    else
                    {
                        if($data['signtype'] == "in")
                            $item->aaction->action->Text = $signInText;
                        if($data['signtype'] == "out")
                            $item->aaction->action->Text = $signOutText;

                    }


                    $item->aactionr->actionr->Text = $data['name'];
                }
            }


        }
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

    public function onDelete($sender,$param)
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
        else
        {
            foreach($cbs as $cb)
            {
                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {
                    $cmd=$this->db->createCommand("SELECT * FROM hr_timux_booking WHERE tracking_id =:id");
                    $cmd->bindValue(":id",$cb->Value);
                    $query = $cmd->query();
                    $data = $query->read();

                    if($data['internet'] && $data['closed'] == '0' )
                    {

                        $cmd=$this->db->createCommand("DELETE FROM hr_timux_booking WHERE tracking_id =:id");
                        $cmd->bindValue(":id",$cb->Value);
                        if($cmd->execute())
                        {
                            $nDelete++;
                        }

                        $cmd=$this->db->createCommand("DELETE FROM hr_timux_booking_bde WHERE tracking_id =:id");
                        $cmd->bindValue(":id",$cb->Value);
                        $cmd->execute();

                        
                        $cmd=$this->db->createCommand("DELETE FROM hr_tracking WHERE id =:id");
                        $cmd->bindValue(":id",$cb->Value);
                        $cmd->execute();
                    }
                    //$this->log("Delete the key: ".$data['serialNumber']);

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} booking was deleted',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mybooking',$pBack));
    }

    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mybooking',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
    }
}
?>
