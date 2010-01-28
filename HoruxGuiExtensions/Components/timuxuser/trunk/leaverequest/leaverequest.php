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

class leaverequest extends PageList
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

        $this->tbb->setTitle( Prado::localize("Leave request")." - ".$this->employee->getFullName() );

        if(!$this->IsPostBack)
        {
            if(Prado::getApplication()->getSession()->contains($this->getApplication()->getService()->getRequestedPagePath().'FilterState'))
            {
                $FilterState = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterState'];
            }
            else
            {
                $FilterState = 'all';
            }

            $this->FilterState->setSelectedValue($FilterState);

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
        $state = $this->FilterState->getSelectedValue();

        return $this->employee->getEmployeeLeaveRequest($state);
    }

    public function selectionChangedState($sender, $param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterState'] = $this->FilterState->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
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

                    $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request WHERE id =:id");
                    $cmd->bindParameter(":id",$cb->Value);
                    $query = $cmd->query();
                    $data = $query->read();

                    if($data['state'] == 'draft')
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
                    }
                    else
                    {
                        if($data['state'] != 'refused')
                        {
                            $cmd=$this->db->createCommand("UPDATE hr_timux_request SET state='canceled' WHERE id =:id");
                            $cmd->bindParameter(":id",$cb->Value);
                            if($cmd->execute())
                            {
                                $nDelete++;

                                $cmd=$this->db->createCommand("SELECT * FROM hr_timux_request_workflow WHERE request_id=:id");
                                $cmd->bindParameter(":id",$cb->Value);
                                $query = $cmd->query();
                                $data = $query->readAll();

                                $mailer = new TMailer();
                                foreach($data as $d)
                                {
                                    $user_id = $d['user_id'];

                                    $cmd=$this->db->createCommand("SELECT u.email1, u.email2, su.email AS email3 FROM hr_user AS u LEFT JOIN hr_superusers AS su ON su.user_id=u.id WHERE u.id=:id");
                                    $cmd->bindParameter(":id",$user_id);
                                    $query = $cmd->query();
                                    $data2 = $query->read();

                                    if($data2['email1'] != '' || $data2['email2'] != '' || $data2['email3'] != '')
                                    {
                                        if($data2['email2'] != '')
                                        {
                                            $mailer->addRecipient($data2['email2']);
                                        }
                                        elseif($data2['email3'] != '')
                                        {
                                            $mailer->addRecipient($data2['email3']);
                                        }
                                        elseif($data2['email1'] != '')
                                        {
                                            $mailer->addRecipient($data2['email1']);
                                        }
                                        
                                    }
                                }
                                $mailer->setObject(Prado::localize("Leave request canceled"));

                                $body = Prado::localize("The leave request from {name} was canceled<br/><br/>Timux", array('name'=>$this->employee->getFullName()));
                                $mailer->setBody($body);
                                $mailer->sendHtmlMail();


                                $cmd=$this->db->createCommand("DELETE FROM hr_timux_request_workflow WHERE request_id =:id");
                                $cmd->bindParameter(":id",$cb->Value);
                                $cmd->execute();
 

                            }
                        }
                    }
                    
                    //$this->log("Delete the key: ".$data['serialNumber']);

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} leave request was deleted/canceled',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest',$pBack));
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
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.leaverequest.leaverequest',$pBack));
    }
}
?>
