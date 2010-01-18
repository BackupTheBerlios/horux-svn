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

Prado::using('horux.pages.components.timuxuser.employee');

class leave extends PageList
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

        if(!$this->IsPostBack)
        {

            $cmd=$this->db->createCommand("SELECT t.createDate FROM hr_timux_request AS t ORDER BY t.createDate LIMIT 0,1");
            $data = $cmd->query();
            $data = $data->readAll();

            $year = date("Y");
            if(count($data)>0)
            {
                $year = explode("-",$data[0]['createDate']);
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


            $FilterEmployee = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee', false);
            $FilterState = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterState', false);
            $FilterYear= $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterYear', date('Y'));
            $FilterMonth = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterMonth', date('n'));
            $FilterTimecode = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterTimecode', false);


            if($FilterEmployee)
                $this->FilterEmployee->Text = $FilterEmployee;

            $this->FilterTimecode->DataSource = $this->TimeCodeList;
            $this->FilterTimecode->dataBind();


            if($FilterState)
                $this->FilterState->setSelectedValue($FilterState);
            else
                $this->FilterState->setSelectedValue('all');

            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);

            if($FilterMonth)
                $this->FilterMonth->setSelectedValue($FilterMonth);

            if($FilterTimecode)
                $this->FilterTimecode->setSelectedValue($FilterTimecode);
            else
                $this->FilterTimecode->setSelectedValue('all');

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

    protected function getTimeCodeList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode" );
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = 'all';
        $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
        $data = array_merge($d, $data);
        return $data;
    }

    public function getData()
    {
        $employee = $this->FilterEmployee->SafeText;

        if($employee != '')
        {
            $employee = " (u2.name LIKE '%$employee%' OR u2.firstname LIKE '%$employee%' OR CONCAT(u2.name, ' ', u2.firstname) LIKE '%$employee%' OR CONCAT(u2.firstname, ' ', u2.name) LIKE '%$employee%') AND ";
        }

        $state = $this->FilterState->getSelectedValue();
        if($state != 'all')
        {
            $state = ' tr.state=\''.$state.'\' AND ';
        }
        else
            $state = " tr.state<>'draft' AND ";

        $timecode = $this->FilterTimecode->getSelectedValue();
        if($timecode != 'all')
        {
            $timecode = ' tt.id='.$timecode.' AND ';
        }
        else
            $timecode = '';

        $from =  $this->FilterYear->getSelectedValue()."-".$this->FilterMonth->getSelectedValue()."-1";
        $day = date("t", mktime(0,0,0,(int)$this->FilterMonth->getSelectedValue(),1,(int)$this->FilterYear->getSelectedValue()));
        $until = $this->FilterYear->getSelectedValue()."-".$this->FilterMonth->getSelectedValue()."-".$day;


        $date = "";

        if($from == "" && $until == "")
        {
            //take the current month

            $date = getdate();
            $from = $date['year'].'-'.$date['mon'].'-1';
            $until = $date['year'].'-'.$date['mon'].'-'.date("t");

            $this->from->Text = "1-".$date['mon']."-".$date['year'];
            $this->until->Text = date("t")."-".$date['mon']."-".$date['year'];

            $date = " tr.createDate>='$from' AND tr.createDate<='$until' AND ";
        }
        else
        {
            if($from != "" && $until != "")
            {
                $date = " tr.createDate>='$from' AND tr.createDate<='$until' AND ";
            }
            if($from != "" && $until == "")
            {
                $date = " tr.createDate>='$from' AND ";
            }
            if($from == "" && $until != "")
            {
                $date = " tr.createDate<='$until' AND ";
            }

        }


        $cmd = $this->db->createCommand( "SELECT tr.*, CONCAT(u2.name, ' ', u2.firstname ) AS employee, CONCAT(u.name, ' ', u.firstname ) AS modUser, tt.name AS timcodeName, rl.* FROM hr_timux_request AS tr LEFT JOIN hr_user AS u ON u.id=tr.modifyUserId LEFT JOIN hr_user AS u2 ON u2.id=tr.userId  LEFT JOIN hr_timux_timecode AS tt ON tt.id=tr.timecodeId LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id WHERE $employee $state $timecode $date 1=1 ORDER BY tr.createDate DESC" );
        $cmd->bindParameter(":id",$this->userId,PDO::PARAM_STR);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->readAll();
            return $data;
        }

        return array();
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }



    public function selectionChangedState($sender, $param)
    {
        $this->onRefresh($sender, $param);
        
    }

    public function selectionChangedEmployee($sender, $param)
    {
        $this->onRefresh($sender, $param);

    }

    public function selectionChangedTimeCode($sender, $param)
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
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee', $this->FilterEmployee->SafeText);
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterState', $this->FilterState->getSelectedValue());
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterYear', $this->FilterYear->getSelectedValue());
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterMonth', $this->FilterMonth->getSelectedValue());
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterTimecode', $this->FilterTimecode->getSelectedValue());

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {
            switch($item->DataItem['state'])
            {

                case 'draft':
                    $item->sstate->state->Text = Prado::localize("Draft");
                    break;
                case 'sended':
                    $item->sstate->state->Text = Prado::localize("Sended");
                    break;
                case 'validating':
                    $item->sstate->state->Text = Prado::localize("Validating");
                    break;
                case 'validate':
                    $item->sstate->state->Text = Prado::localize("Validated");
                    break;
                case 'canceled':
                    $item->sstate->state->Text = Prado::localize("Canceled");
                    break;
                case 'closed':
                    $item->sstate->state->Text = Prado::localize("Closed");
                    break;
                case 'refused':
                    $item->sstate->state->Text = Prado::localize("Refused");
                    break;
            }

            $item->ffdate->fdate->Text = $this->dateFromSql($item->DataItem['datefrom']);

            if($item->DataItem['dateto'] == '0000-00-00')
                $item->ttdate->tdate->Text = $item->ffdate->fdate->Text;
            else
                $item->ttdate->tdate->Text = $this->dateFromSql($item->DataItem['dateto']);


            $item->ccdate->cdate->Text = $this->dateFromSql($item->DataItem['createDate']);

            if($item->DataItem['modifyDate'] != '0000-00-00')
                $item->mmdate->mdate->Text = $this->dateFromSql($item->DataItem['modifyDate']);
        }
    }


    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave',$pBack));
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

                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_request WHERE id =:id");
                    $cmd->bindParameter(":id",$cb->Value);
                    if($cmd->execute())
                    {
                        $nDelete++;
                    }
                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_request_leave WHERE request_id =:id");
                    $cmd->bindParameter(":id",$cb->Value);
                    $cmd->execute();
                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_request_workflow WHERE request_id =:id");
                    $cmd->bindParameter(":id",$cb->Value);



                    //$this->log("Delete the key: ".$data['serialNumber']);

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} leave was deleted',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leave.leave',$pBack));
    }
}
?>
