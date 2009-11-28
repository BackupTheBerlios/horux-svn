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

        $this->timecodeGrid->DataSource=$this->TimecodeGrid;
        $this->timecodeGrid->dataBind();
    }

    public function getTimecodeGrid()
    {
        $cmd=$this->db->createCommand("SELECT ac.nbre, CONCAT('[',tt.abbreviation,'] - ', tt.name) AS timecode,tt.id AS timecodeId, tt.formatDisplay,ac.id, tt.useMinMax, tt.minHour, tt.maxHour, tt.type  FROM hr_timux_activity_counter AS ac LEFT JOIN hr_user AS u ON u.id=ac.user_id LEFT JOIN hr_timux_timecode AS tt ON tt.id=ac.timecode_id WHERE  u.id=".$this->userId." AND ac.year=0 AND ac.month=0 ORDER BY u.name,u.firstname,tt.abbreviation");

        $data = $cmd->query();
        $data = $data->readAll();
        $defOv = $this->employee->getDefaultOvertimeCounter();
       /* $request = $this->employee->getRequest(date('Y'), date('n'));
        
        for($i=0; $i<count($data);$i++)
        {
            if($data[$i]['timecodeId'] == $defOv)
            {
                $overtime = $this->employee->getMonthOvertime(date('Y'), date('n'));
                $data[$i]['nbre2'] = $overtime + $data[$i]['nbre'] ;
            }
            else
            {
                if(isset($request[$data[$i]['timecodeId']]))
                {

                    if($data[$i]['type'] == 'leave')
                        $data[$i]['nbre2'] = $data[$i]['nbre'] - $request[$data[$i]['timecodeId']]['nbre'] ;
                    if($data[$i]['type'] == 'absence')
                        $data[$i]['nbre2'] = $data[$i]['nbre'] + $request[$data[$i]['timecodeId']]['nbre'] ;
                }
                else
                {
                     $data[$i]['nbre2'] = $data[$i]['nbre'] ;
                }

            }
        }*/

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
