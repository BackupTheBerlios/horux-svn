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
            $this->timecodeGrid->DataSource=$this->TimecodeGrid;
            $this->timecodeGrid->dataBind();
        }
    }


    public function getTimecodeGrid()
    {
        $cmd=$this->db->createCommand("SELECT ac.nbre, CONCAT('[',tt.abbreviation,'] - ', tt.name) AS timecode,tt.id AS timecodeId, tt.formatDisplay, tt.useMinMax, tt.minHour, tt.maxHour, tt.type, ac.year, ac.month  FROM hr_timux_activity_counter AS ac LEFT JOIN hr_user AS u ON u.id=ac.user_id LEFT JOIN hr_timux_timecode AS tt ON tt.id=ac.timecode_id WHERE  u.id=".$this->userId." AND ac.year=0 AND ac.month=0 AND (tt.type='leave' OR tt.type='overtime') ORDER BY u.name,u.firstname,tt.abbreviation");

        $data = $cmd->query();
        $data = $data->readAll();
        

        $defOv = $this->employee->getDefaultOvertimeCounter();
        $defH = $this->employee->getDefaultHolidaysCounter();
        
        for($i=0; $i<count($data);$i++)
        {
            if($data[$i]['timecodeId'] == $defOv)
            {
                $overTimeLastMonth = $this->employee->getOvertimeLastMonth(date('n'), date('Y'));

                $overTimeMonth = 0;
                for($day=1; $day<date('j');$day++) {
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

}

?>
