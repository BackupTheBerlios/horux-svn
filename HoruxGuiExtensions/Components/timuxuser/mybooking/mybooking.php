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
            $FilterStatus = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterStatus', false);
            $FilterFrom = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterFrom', false);
            $FilterUntil = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterUntil', false);

            if($FilterStatus)
                $this->FilterStatus->setSelectedValue($FilterStatus);
            if($FilterFrom)
                $this->from->Text = $FilterFrom;
            if($FilterUntil)
                $this->until->Text = $FilterUntil;

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

        }
    }


    public function getData()
    {
        $status = $this->FilterStatus->getSelectedValue();

        $from = "";
        $until = "";

        $from = $this->dateToSql( $this->from->SafeText );
        $until = $this->dateToSql( $this->until->SafeText );

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


    public function onRefresh($sender, $param)
    {
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterStatus', $this->FilterStatus->getSelectedValue());
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterFrom', $this->from->Text);
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'FilterUntil', $this->until->Text);

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

            $item->ddate->date->Text = $this->dateFromSql($item->DataItem['date']);

            if($item->DataItem['action'] == 255)
                $item->aaction->action->Text = Prado::localize("Sign in");
            if($item->DataItem['action'] == 254)
                $item->aaction->action->Text = Prado::localize("Sign out");

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
                            $item->aaction->action->Text = Prado::localize("Sign in");
                        if($actionReason[1] == "OUT")
                            $item->aaction->action->Text = Prado::localize("Sign out");
                    }
                    else
                    {
                        if($data['signtype'] == "in")
                            $item->aaction->action->Text = Prado::localize("Sign in");
                        if($data['signtype'] == "out")
                            $item->aaction->action->Text = Prado::localize("Sign out");

                    }

                    $item->aactionr->actionr->Text = $data['name'];
                }
            }

            if($item->DataItem['action'] == 500)
            {
                $item->aaction->action->Text = Prado::localize("Sign in");
                $item->aactionr->actionr->Text =Prado::localize("Added manually");
            }

            if($item->DataItem['action'] == 501)
            {
                $item->aaction->action->Text = Prado::localize("Sign out");
                $item->aactionr->actionr->Text = Prado::localize("Added manually");
            }

            if($item->DataItem['action'] == 502)
            {
                $item->aaction->action->Text = Prado::localize("Sign in");
                $item->aactionr->actionr->Text =Prado::localize("Modified manually");
            }

            if($item->DataItem['action'] == 503)
            {
                $item->aaction->action->Text = Prado::localize("Sign out");
                $item->aactionr->actionr->Text = Prado::localize("Modified manually");
            }
        }
    }

}
?>
