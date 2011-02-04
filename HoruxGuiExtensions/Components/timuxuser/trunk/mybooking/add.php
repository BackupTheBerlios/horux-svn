<?php


$param = Prado::getApplication()->getParameters();
$computation = $param['computation'];

Prado::using('horux.pages.components.timuxuser.'.$computation);

class add extends Page
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

        if(!$this->isPostBack)
        {

            $this->timecode->DataSource = $this->TimeCodeList;
            $this->timecode->dataBind();

            $this->timecode->setEnabled(false);


            if(isset($this->Request['date']))
            {
                $this->date->Text = $this->Request['date'];
            }
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The sign was added successfully'), 'id'=>$id);

                if(isset($this->Request['back']))
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));

                if(isset($this->Request['back']))
                    $pBack = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.add', $pBack));
            }
        }
    }
    
    protected function getTimeCodeList()
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode" );
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = 0;
        $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
        $data = array_merge($d, $data);
        return $data;
    }


    public function onSignChange($sender, $param)
    {
        if($this->sign->getSelectedValue() == 254 || $this->sign->getSelectedValue() == 255 )
            $this->timecode->setEnabled(false);
        else
        {
            $this->timecode->setEnabled(true);
            $cmd = NULL;
            if($this->sign->getSelectedValue() == '_IN')
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode WHERE signtype='in' OR signtype='both'" );
            if($this->sign->getSelectedValue() == '_OUT')
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode  WHERE signtype='out' OR signtype='both'" );

            $data =  $cmd->query();
            $data = $data->readAll();
            $d[0]['Value'] = 0;
            $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
            $data = array_merge($d, $data);
            $this->timecode->DataSource = $data;
            $this->timecode->dataBind();
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The sign was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));

            if(isset($this->Request['back']))
                $this->Response->redirect($this->Service->constructUrl($this->Request['back'],$pBack));
            else
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mybooking',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "INSERT INTO `hr_tracking` (
                                            `id_user` ,
                                            `time`,
                                            `date`,
                                            `is_access`
                                            )
                                            VALUES (
                                            :id_user,
                                            :time,
                                            :date,
                                            '1'
                                            );" );

        $cmd->bindValue(":id_user",$this->userId,PDO::PARAM_STR);
        $cmd->bindValue(":time",$this->time->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":date",$this->dateToSql( $this->date->SafeText ), PDO::PARAM_STR);

        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        $cmd = $this->db->createCommand( "INSERT INTO `hr_timux_booking` (
                                            `tracking_id` ,
                                            `action`,
                                            `roundBooking`,
                                            `actionReason`,
                                            `internet`
                                            )
                                            VALUES (
                                            :tracking_id,
                                            :action,
                                            :roundBooking,
                                            :actionReason,
                                            1
                                            );" );

        $cmd->bindValue(":tracking_id",$lastId,PDO::PARAM_STR);
        $action = $this->sign->getSelectedValue();
        if($this->sign->getSelectedValue() == '_IN' || $this->sign->getSelectedValue() == '_OUT')
        {
            $action = 100;

            $cmd2=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$this->timecode->getSelectedValue());

            $data2 = $cmd2->query();
            $data2 = $data2->read();

	    //$$
	    if($data2['type'] == 'load') {
	      $action = 255;


	      $cmd2 = $this->db->createCommand( "INSERT INTO `hr_timux_booking_bde` (
						    `tracking_id` ,
						    `user_id`,
						    `device_id`,
						    `date`,
						    `time`,
						    `code`,
						    `BDE1`
						    )
						    VALUES (
						    :tracking_id,
						    :user_id,
						    :device_id,
						    :date,
						    :time,
						    155,
						    :bde1
						    );" );
	      
	      $cmd2->bindValue(":tracking_id",$lastId,PDO::PARAM_STR);
	      $cmd2->bindValue(":user_id",$this->userId,PDO::PARAM_STR);
	      $cmd2->bindValue(":device_id",0,PDO::PARAM_STR);
	      $cmd2->bindValue(":date",$this->dateToSql( $this->date->SafeText ),PDO::PARAM_STR);
	      $cmd2->bindValue(":time",$this->time->SafeText,PDO::PARAM_STR);
	      $cmd2->bindValue(":bde1",$data2['abbreviation'],PDO::PARAM_STR);
	      $cmd2->execute();
	    }

            $cmd->bindValue(":action",$action, PDO::PARAM_STR);

	    if($action != 255) {
	      if($data2['signtype'] == 'both')
	      {
		  $actionReason = $this->timecode->getSelectedValue().$this->sign->getSelectedValue();
	      }
	      else
	      {
		  $actionReason = $this->timecode->getSelectedValue();
	      }
	    } else {
		$actionReason = 0;
	    }
            $cmd->bindValue(":actionReason",$actionReason, PDO::PARAM_STR);
        }
        else
        {
            $actionReason = 0;
            $cmd->bindValue(":action",$action, PDO::PARAM_STR);
            $cmd->bindValue(":actionReason",$actionReason, PDO::PARAM_STR);

        }


        $cmd->bindValue(":roundBooking",$this->time->SafeText, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        return $lastId;
    }

    public function isNotClosed($sender,$param)
    {
        $date = explode("-",$this->date->SafeText);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_activity_counter WHERE user_id=:id AND year=:year AND month=:month AND isClosedMonth=1");
        $cmd->bindValue(":id",$this->userId, PDO::PARAM_INT);
        $cmd->bindValue(":year",$date[2], PDO::PARAM_INT);
        $cmd->bindValue(":month",$date[1], PDO::PARAM_INT);
        $query = $cmd->query();
        $query = $query->read();

        if($query)
            $param->IsValid=false;
        
    }

    public function onCancel($sender, $param)
    {
        if(isset($this->Request['back']))
            $this->Response->redirect($this->Service->constructUrl($this->Request['back']));
        else
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.mybooking.mybooking'));
    }
}
