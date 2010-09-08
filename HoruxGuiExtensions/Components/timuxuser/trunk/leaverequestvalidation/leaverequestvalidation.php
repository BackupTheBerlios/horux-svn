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

$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class leaverequestvalidation extends PageList
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
        $cmd = $this->db->createCommand( "SELECT tr.*, CONCAT(u2.name, ' ', u2.firstname ) AS employee, CONCAT(u.name, ' ', u.firstname ) AS modUser, tt.name AS timcodeName, rl.* FROM hr_timux_request AS tr LEFT JOIN hr_user AS u ON u.id=tr.modifyUserId LEFT JOIN hr_user AS u2 ON u2.id=tr.userId  LEFT JOIN hr_timux_timecode AS tt ON tt.id=tr.timecodeId LEFT JOIN hr_timux_request_leave AS rl ON rl.request_id=tr.id LEFT JOIN hr_timux_request_workflow AS rw ON rw.request_id=tr.id WHERE rw.user_id=:id AND tr.state<>'closed' AND tr.state<>'canceled' ORDER BY tr.createDate DESC" );
        $cmd->bindValue(":id",$this->userId,PDO::PARAM_STR);
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
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequestvalidation.leaverequestvalidation',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequestvalidation.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequestvalidation.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequestvalidation.leaverequestvalidation',$pBack));
    }
}
?>
