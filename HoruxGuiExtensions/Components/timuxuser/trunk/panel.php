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

class panel extends Page
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
            $this->timecode->DataSource = $this->getTimeCodeList('out');
            $this->timecode->dataBind();
            $this->timecodeIn->DataSource = $this->getTimeCodeList('in');
            $this->timecodeIn->dataBind();

            $this->timecodeGrid->DataSource=$this->TimecodeGrid;
            $this->timecodeGrid->dataBind();

            $this->presenceGrid->DataSource=$this->PresenceGrid;
            $this->presenceGrid->dataBind();

        }
    }

    public function getPresenceGrid()
    {
        $result = array();

        $cmd=$this->db->createCommand("SELECT id, CONCAT(name, ' ', firstname) AS fullname FROM hr_user WHERE id>1 ORDER BY fullname");

        $data = $cmd->query();
        $data = $data->readAll();

        $absence = array();
        $presence = array();

        foreach($data as $d) {

            $cmd=$this->db->createCommand("SELECT * FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON t.id=tb.tracking_id WHERE t.id_user=:userId ORDER BY t.date DESC, t.time DESC LIMIT 0,1");
            $cmd->bindValue(":userId", $d['id']);
            $data = $cmd->query();
            $data = $data->read();

            if($data) {
                if($data['action'] == 255 || $data['action'] == 155 || substr($data['actionReason'],-3,3) === '_IN' ) {
                    $presence[]  = "<span style='width:10px;height:10px; background-color:#60ff21'>&nbsp;&nbsp;&nbsp;&nbsp;</span> ".$d['fullname'];
                } else {
                    $absence[] = "<span style='width:10px;height:10px; background-color:#ff2a2a'>&nbsp;&nbsp;&nbsp;&nbsp;</span> ".$d['fullname'];
                }
            } else {
                $absence[] = "<span style='width:10px;height:10px; background-color:#ff2a2a'>&nbsp;&nbsp;&nbsp;&nbsp;</span> ".$d['fullname'];
            }
        }

        if(count($absence) > count($presence)) {

           for($i=0; $i<count($presence); $i++) {
               $result[] = array('present' => $presence[$i], 'absent' => $absence[$i]);
           }

           for($i; $i<count($absence); $i++) {
               $result[] = array('present' => '', 'absent' => $absence[$i]);
           }

        } else {

           for($i=0; $i<count($absence); $i++) {
               $result[] = array('present' => $presence[$i], 'absent' => $absence[$i]);
           }

           for($i; $i<count($presence); $i++) {
               $result[] = array('present' => $presence[$i], 'absent' =>'');
           }

        }


        return $result;
    }

    public function getTimecodeGrid()
    {
        $defOv = $this->employee->getDefaultOvertimeCounter();
        $defH = $this->employee->getDefaultHolidaysCounter();
        
        $cmd=$this->db->createCommand("SELECT ac.nbre, CONCAT('[',tt.abbreviation,'] - ', tt.name) AS timecode,tt.id AS timecodeId, tt.formatDisplay, tt.useMinMax, tt.minHour, tt.maxHour, tt.type, ac.year, ac.month  FROM hr_timux_activity_counter AS ac LEFT JOIN hr_user AS u ON u.id=ac.user_id LEFT JOIN hr_timux_timecode AS tt ON tt.id=ac.timecode_id WHERE u.id=".$this->userId." AND ac.year=0 AND ac.month=0 AND (tt.type='leave' OR tt.type='overtime') AND tt.id IN ($defOv, $defH) ORDER BY u.name,u.firstname,tt.abbreviation");

        $data = $cmd->query();
        $data = $data->readAll();

        
        for($i=0; $i<count($data);$i++)
        {
            if($data[$i]['timecodeId'] == $defOv)
            {
                $overTimeLastMonth = $this->employee->getOvertimeLastMonth(date('n'), date('Y'));

                $overTimeMonth = 0;
                for($day=1; $day<=date('j');$day++) {
                    $todo = $this->employee->getDayTodo($day,date('n'), date('Y'));
                    $done = $this->employee->getDayDone($day,date('n'), date('Y'));
                    $overTimeMonth = bcadd($overTimeMonth, bcsub($done['done'], $todo ,4),4 );
                }

                $overtime = bcadd($overTimeLastMonth,$overTimeMonth,4);

                $data[$i]['nbre'] = $overTimeLastMonth ;
                $data[$i]['nbre2'] = $overtime ;
            }
            elseif ($data[$i]['timecodeId'] == $defH) {

                if(date('n') == 1)
                    $hLastMonth = $this->employee->geHolidaystMonth(date('Y')-1, 12);
                else
                    $hLastMonth = $this->employee->geHolidaystMonth(date('Y'), date('n')-1);

                $hcurrentMonth = $this->employee->geHolidaystMonth(date('Y'), date('n'));


                $data[$i]['nbre'] = $hLastMonth;
                
                $data[$i]['nbre2'] = $hcurrentMonth;
            } else {

                $request = $this->employee->getRequest(date('Y'), date('n'),$data[$i]['timecodeId']);

                if($data[$i]['type'] == 'leave')
                    $data[$i]['nbre2'] = $data[$i]['nbre'] - $request['nbre'] ;
                if($data[$i]['type'] == 'absence')
                    $data[$i]['nbre2'] = $data[$i]['nbre'] + $request['nbre'] ;
            }
        }

        return $data;
        
    }

    public function itemCreated($sender, $param)
    {
        $item=$param->Item;

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {
            if( $item->DataItem['formatDisplay'] == 'hour' )
            {
                if($item->DataItem['nbre'] > 0)
                {
                    $item->nnbre->nbre->Text = sprintf("+%.2f ",$item->DataItem['nbre']).Prado::localize('hours');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']>$item->DataItem['maxHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }

                }

                if($item->DataItem['nbre'] < 0)
                {
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('hours');

                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']<$item->DataItem['minHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }
                    else
                        $item->nnbre->nbre->ForeColor = "red";
                }
                if($item->DataItem['nbre'] == 0)
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('hours');

            }

            if( $item->DataItem['formatDisplay'] == 'day' )
            {
                if($item->DataItem['nbre'] > 0)
                {
                    $item->nnbre->nbre->Text = sprintf("+%.2f ",$item->DataItem['nbre']).Prado::localize('days');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']>$item->DataItem['maxHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }

                }

                if($item->DataItem['nbre'] < 0)
                {
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('days');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre']<$item->DataItem['minHour'])
                            $item->nnbre->nbre->ForeColor = "red";
                    }
                    else
                        $item->nnbre->nbre->ForeColor = "red";
                }

                if($item->DataItem['nbre'] == 0)
                    $item->nnbre->nbre->Text = sprintf("%.2f ",$item->DataItem['nbre']).Prado::localize('days');
            }


            ////

            if( $item->DataItem['formatDisplay'] == 'hour' )
            {
                if($item->DataItem['nbre2'] > 0)
                {
                    $item->nnbre2->nbre2->Text = sprintf("+%.2f ",$item->DataItem['nbre2']).Prado::localize('hours');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre2']>$item->DataItem['maxHour'])
                            $item->nnbre2->nbre2->ForeColor = "red";
                    }

                }

                if($item->DataItem['nbre2'] < 0)
                {
                    $item->nnbre2->nbre2->Text = sprintf("%.2f ",$item->DataItem['nbre2']).Prado::localize('hours');

                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre2']<$item->DataItem['minHour'])
                            $item->nnbre2->nbre2->ForeColor = "red";
                    }
                    else
                        $item->nnbre2->nbre2->ForeColor = "red";
                }
                if($item->DataItem['nbre2'] == 0)
                    $item->nnbre2->nbre2->Text = sprintf("%.2f ",$item->DataItem['nbre2']).Prado::localize('hours');

            }

            if( $item->DataItem['formatDisplay'] == 'day' )
            {
                if($item->DataItem['nbre2'] > 0)
                {
                    $item->nnbre2->nbre2->Text = sprintf("+%.2f ",$item->DataItem['nbre2']).Prado::localize('days');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre2']>$item->DataItem['maxHour'])
                            $item->nnbre2->nbre2->ForeColor = "red";
                    }

                }

                if($item->DataItem['nbre2'] < 0)
                {
                    $item->nnbre2->nbre2->Text = sprintf("%.2f ",$item->DataItem['nbre2']).Prado::localize('days');
                    if($item->DataItem['useMinMax'] )
                    {
                        if($item->DataItem['nbre2']<$item->DataItem['minHour'])
                            $item->nnbre2->nbre2->ForeColor = "red";
                    }
                    else
                        $item->nnbre2->nbre2->ForeColor = "red";
                }

                if($item->DataItem['nbre2'] == 0)
                    $item->nnbre2->nbre2->Text = sprintf("%.2f ",$item->DataItem['nbre2']).Prado::localize('days');
            }

        }
    }


    public function isAccess($page)
    {
		$app = $this->getApplication();
      	$db = $app->getModule('horuxDb')->DbConnection;
      	$db->Active=true;

		$usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();
		$groupId = $app->getUser()->getGroupID() == null ? 0 : $app->getUser()->getGroupID();

		$sql = 	'SELECT `allowed`, `shortcut` FROM hr_gui_permissions WHERE ' .
				'(`page`=\''.$page.'\' OR `page` IS NULL) ' .
				"AND (" .
					"(`selector`='user_id' AND `value`=".$usedId.") " .
					"OR (`selector`='group_id' AND `value`=".$groupId.") " .
				")" .
			'ORDER BY `page` DESC';

		$cmd = $db->createCommand($sql);
		$res = $cmd->query();
		$res = $res->readAll();
		// If there were no results
		if (!$res)
			return false;
		else
			// Traverse results
			foreach ($res as $allowed)
			{
				// If we get deny here
				if (! $allowed)
					return false;
			}

		return true;
    }

    protected function getTimeCodeList($mode = 'out')
    {
        $cmd = NULL;
        $cmd = $this->db->createCommand( "SELECT id AS Value, CONCAT('[',abbreviation, '] ', name) AS Text FROM hr_timux_timecode  WHERE signtype='".$mode."' OR signtype='both'" );
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = 0;
        $d[0]['Text'] = Prado::localize('---- Choose a timecode ----');
        $data = array_merge($d, $data);
        return $data;
    }


    public function signIn($sender,$param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData(255))
            {
                $pBack = array('okMsg'=>Prado::localize('The sign was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel',$pBack));
        }
    }

    public function signOut($sender,$param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData(254))
            {
                $pBack = array('okMsg'=>Prado::localize('The sign was added successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The sign was not added'));

            $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel',$pBack));
        }
    }

    protected function saveData($action)
    {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $sign = $action;

        $cmd = $this->db->createCommand( "INSERT INTO `hr_tracking` (
                                            `id_user` ,
                                            `time`,
                                            `date`,
                                            `is_access`,
                                            `extData`
                                            )
                                            VALUES (
                                            :id_user,
                                            :time,
                                            :date,
                                            '1',
                                            'hr_timux_booking'
                                            );" );

        $cmd->bindValue(":id_user",$this->userId,PDO::PARAM_STR);
        $cmd->bindValue(":time",$time, PDO::PARAM_STR);
        $cmd->bindValue(":date",$date, PDO::PARAM_STR);

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

        if(($action == 254 && $this->timecode->getSelectedValue()) || ($action == 255 && $this->timecodeIn->getSelectedValue()))
        {
            $oldAction = $action;
            if ($action == 254)
              $timecodeValue = $this->timecode->getSelectedValue();
            else
              $timecodeValue = $this->timecodeIn->getSelectedValue();

            $action = 100;

            $cmd2=$this->db->createCommand("SELECT *  FROM hr_timux_timecode WHERE id=".$timecodeValue);

            $data2 = $cmd2->query();
            $data2 = $data2->read();

	          //$$
	          if($data2['type'] == 'load') {
	            $action = 255;
              $sign = "_IN";


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
	            $cmd2->bindValue(":date",$date,PDO::PARAM_STR);
	            $cmd2->bindValue(":time",$time,PDO::PARAM_STR);
	            $cmd2->bindValue(":bde1",$data2['abbreviation'],PDO::PARAM_STR);
	            $cmd2->execute();
	          }
            else
              $sign = "_OUT";

            if ($oldAction == 255)
              $sign = "_IN";

                  $cmd->bindValue(":action",$action, PDO::PARAM_STR);

	          if($action != 255) {
	            if($data2['signtype'] == 'both')
	            {
		            $actionReason = $timecodeValue.$sign;
	            }
	            else
	            {
		            $actionReason = $timecodeValue;
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


        $cmd->bindValue(":roundBooking",$time, PDO::PARAM_STR);

        $res1 = $cmd->execute();

        $sa = new TStandAlone();
        $sa->addStandalone('add', $this->userId, 'UserListMod');

        return $lastId;
    }

}

?>
