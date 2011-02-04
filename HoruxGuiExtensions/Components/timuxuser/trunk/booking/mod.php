<?php

class mod extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {          
            $this->employee->DataSource = $this->PersonList;
            $this->employee->dataBind();

            $this->id->Value = $this->Request['id'];
            $this->setData();
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON t.id=tb.tracking_id WHERE t.id=:id");
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query)
        {
            $data = $query->read();

            /*if(!$data['internet'])
            {
                $pBack = array('koMsg'=>Prado::localize('Cannot modified this physical sign'));

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
            }*/

            if($data['closed'] == '1')
            {
                $pBack = array('koMsg'=>Prado::localize('Cannot modified a closed signing'));

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
            }


            $this->id->Value = $data['id'];
            $this->employee->setSelectedValue($data['id_user']);
            $this->date->Text = $this->dateFromSql($data['date']);
            $this->time->Text = $data['roundBooking'];

            if($data['action'] == '254' || $data['action'] == '255')
                $this->sign->setSelectedValue($data['action']);

	    if($data['action'] == '255') {
  
	      $cmd2 = $this->db->createCommand( "SELECT * FROM hr_timux_booking_bde WHERE tracking_id=:id");
	      $cmd2->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
	      $query2 = $cmd2->query();
	    
	      $data2 = $query2->read();

	      if($data2) {

		$cmd2 = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE abbreviation=:abb");
		$cmd2->bindValue(":abb",$data2['BDE1'], PDO::PARAM_STR);
		$query2 = $cmd2->query();
	      
		$data2 = $query2->read();

		
                $this->sign->setSelectedValue("_IN");
		
		$this->timecode->setSelectedValue($data2['id']);
	      }
	    }

            if($data['action'] == '100')
            {
                $ar = explode('_', $data['actionReason']);

                if(count($ar)>1)
                {
                    $this->sign->setSelectedValue("_".$ar[1]);
                }
                else
                {
                    $cmd2=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$ar[0]);

                    $data2 = $cmd2->query();
                    $data2 = $data2->read();

                    if($data2['signtype'] == 'in')
                    {
                        $this->sign->setSelectedValue("_IN");
                    }
                    if($data2['signtype'] == 'out')
                    {
                        $this->sign->setSelectedValue("_OUT");
                    }

                }
            }

            $this->onSignChange(NULL,NULL);

            if($data['action'] == '100')
            {
                $ar = explode('_', $data['actionReason']);

                $this->timecode->setSelectedValue($ar[0]);
            }
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The sign was modified successfully'), 'id'=>$this->id->Value);

                if(isset($this->Request['back']))
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The sign was not modified'), 'id'=>$this->id->Value);

                if(isset($this->Request['back']))
                    $pBack['back'] = $this->Request['back'];

                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.mod', $pBack));
            }
        }
    }

    protected function getPersonList()
    {
        $cmd = NULL;
        if(isset($this->Request['back'])) {

            $cmd = $this->db->createCommand( "SELECT * FROM hr_tracking WHERE id=:id " );
            $cmd->bindValue(":id",$this->Request['id'], PDO::PARAM_INT);
            $data =  $cmd->query();
            $data = $data->read();


            $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??' AND department>0 AND id=:id" );
            $cmd->bindValue(":id",$data['id_user'], PDO::PARAM_INT);

        } else {
            $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT(name, ' ', firstname) AS Text FROM hr_user WHERE name<>'??' AND department>0" );
        }
        $data =  $cmd->query();
        $data = $data->readAll();
        if(!isset($this->Request['back'])) {
            $d[0]['Value'] = 'null';
            $d[0]['Text'] = Prado::localize('---- Choose a employee ----');
            $data = array_merge($d, $data);
        }
        return $data;
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
        {
            $this->timecode->setEnabled(false);
        }
        else
        {
            $this->timecode->setEnabled(true);
            $cmd = NULL;
            if($this->sign->getSelectedValue() == '_IN')
            {
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode WHERE signtype='in' OR signtype='both'" );
            }
            if($this->sign->getSelectedValue() == '_OUT')
            {
                $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode  WHERE signtype='out' OR signtype='both'" );
            }

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
                $pBack = array('okMsg'=>Prado::localize('The sign was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not modified'));

            if(isset($this->Request['back']))
                $this->Response->redirect($this->Service->constructUrl($this->Request['back'],$pBack));
            else
                $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "UPDATE `hr_tracking` SET
                                            `time` = :time,
                                            `date` = :date
                                            WHERE id=:id" );

        $cmd->bindValue(":time",$this->time->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":date",$this->dateToSql( $this->date->SafeText ), PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);

        $res1 = $cmd->execute();

        $cmd = $this->db->createCommand( "UPDATE`hr_timux_booking` SET
                                            `action` = :action,
                                            `actionReason`=:actionReason,
                                            `roundBooking` = :roundBooking,
                                            `internet`=1
                                            WHERE tracking_id=:id" );

        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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

	      $cmd2 = $this->db->createCommand("DELETE FROM `hr_timux_booking_bde` WHERE tracking_id=:tracking_id");
	      $cmd2->bindValue(":tracking_id",$this->id->Value,PDO::PARAM_STR);
	      $cmd2->execute();
      

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
	      
	      $cmd2->bindValue(":tracking_id",$this->id->Value,PDO::PARAM_STR);
	      $cmd2->bindValue(":user_id",$this->employee->getSelectedValue(),PDO::PARAM_STR);
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

        $res2 = $cmd->execute();

        if(Prado::getApplication()->getCache())
            Prado::getApplication()->getCache()->flush();

        return true;
    }


    public function onDelete($sender,$param)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_timux_booking WHERE tracking_id =:id");
        $cmd->bindValue(":id",$this->id->Value);
        $query = $cmd->query();
        $data = $query->read();

        if($data['closed'] == '0')
        {
            $cmd=$this->db->createCommand("DELETE FROM hr_tracking WHERE id =:id");
            $cmd->bindValue(":id",$this->id->Value);
            $cmd->execute();


            $cmd=$this->db->createCommand("DELETE FROM hr_timux_booking WHERE tracking_id =:id");
            $cmd->bindValue(":id",$this->id->Value);
            $cmd->execute();

            $cmd=$this->db->createCommand("DELETE FROM hr_timux_booking_bde WHERE tracking_id =:id");
            $cmd->bindValue(":id",$this->id->Value);
            $cmd->execute();

        }

        if(Prado::getApplication()->getCache())
            Prado::getApplication()->getCache()->flush();

        if(isset($this->Request['back']))
            $this->Response->redirect($this->Service->constructUrl($this->Request['back']));
        else
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking'));
    }

    public function onCancel($sender, $param)
    {
        if(isset($this->Request['back']))
            $this->Response->redirect($this->Service->constructUrl($this->Request['back']));
        else
            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.booking.booking'));
    }
}
