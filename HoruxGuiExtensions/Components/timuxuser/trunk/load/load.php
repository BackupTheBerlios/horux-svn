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

Prado::using('System.I18N.core.DateFormat');


class load extends PageList {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->IsPostBack) {
            $cmd=$this->db->createCommand("SELECT t.startDate FROM hr_timux_workingtime AS t ORDER BY t.startDate LIMIT 0,1");
            $data = $cmd->query();
            $data = $data->readAll();

            $year = date("Y");
            if(count($data)>0) {
                $year = explode("-",$data[0]['startDate']);
                $year = $year[0];
            }
            $currentYear = date("Y");

            $yearList = array();

            for($i=$year; $i<= $currentYear;$i++ ) {
                $yearList[] = array('Value'=>$i, 'Text'=>$i);
            }


            $this->FilterYear->DataSource=$yearList;
            $this->FilterYear->dataBind();

            if(Prado::getApplication()->getSession()->contains($this->getApplication()->getService()->getRequestedPagePath().'FilterYear')) {
                $FilterYear= $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'];
                $FilterMonth = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'];
            }
            else {
                $FilterYear= date('Y');
                $FilterMonth = date('n');
            }

            $FilterEmployee = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'];
            $FilterDepartment = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'];

            $this->FilterDepartment->DataSource=$this->DepartmentList;
            $this->FilterDepartment->dataBind();

            if($FilterDepartment) {
                $this->FilterDepartment->setSelectedValue($FilterDepartment);
            }
            else {
                $this->FilterDepartment->setSelectedIndex(0);
            }

            $this->FilterEmployee->DataSource=$this->EmployeeList;
            $this->FilterEmployee->dataBind();

            if($FilterEmployee) {
                $this->FilterEmployee->setSelectedValue($FilterEmployee);
            }
            else {
                $this->FilterEmployee->setSelectedValue(0);
            }

            if($FilterYear)
                $this->FilterYear->setSelectedValue($FilterYear);

            if($FilterMonth)
                $this->FilterMonth->setSelectedValue($FilterMonth);


            $this->FilterLoad->DataSource=$this->LoadList;
            $this->FilterLoad->dataBind();

            if($FilterLoad) {
                $this->FilterEmployee->setSelectedValue($FilterLoad);
            }
            else {
                $this->FilterLoad->setSelectedIndex(0);
            }


            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

        }

        if(isset($this->Request['okMsg'])) {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg'])) {
            $this->displayMessage($this->Request['koMsg'], false);
        }
    }

    public function onCancel($sender, $param) {
        $this->Response->redirect($this->Service->constructUrl('components.timuxuser.panel'));
    }

    public function getLoadList() {

        $cmd = $this->db->createCommand( "SELECT id AS Value, name AS Text FROM hr_timux_timecode ORDER BY name");

        $data = $cmd->query();
        $data = $data->readAll();

        $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

        $data = array_merge($dataAll, $data);

        return $data;
    }

    public function getEmployeeList() {
        $department = $this->FilterDepartment->getSelectedValue();

        if($department>0)
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE $id department=$department AND name!='??' ORDER BY name, firstname");
        else
            $cmd = $this->db->createCommand( "SELECT CONCAT(name, ' ', firstname) AS Text, id AS Value FROM hr_user WHERE name!='??' ORDER BY name, firstname");

        $data = $cmd->query();
        $data = $data->readAll();

        $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));
        $data = array_merge($dataAll, $data);

        return $data;

    }

    public function getDepartmentList() {
        $cmd = $this->db->createCommand( "SELECT name AS Text, id AS Value FROM hr_department ORDER BY name");
        $data = $cmd->query();
        $data = $data->readAll();

        $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("--- All ---"));

        $data = array_merge($dataAll, $data);

        return $data;

    }

    public function getData($isPrint=false) {
        $param = Prado::getApplication()->getParameters();
        $computation2 = $param['computation2'];
        if($computation2 != '') {
            Prado::using('horux.pages.components.timuxuser.'.$computation2);
        }

        $userList = $this->getEmployeeList();

        $year = $this->FilterYear->getSelectedValue();
        $month = $this->FilterMonth->getSelectedValue();

        $from = $year.'-'.$month.'-1';
        $until = $year.'-'.$month.'-'.date("t", mktime(0,0,0,$month, 1,$year));
        $date = " t.date>='$from' AND t.date<='$until' AND ";

        $timeCodeList = array();


        $hourMonthTodo = array();
        $hourly = array();
        $hoursByDay = array();

        foreach($userList as $user) {
            if($user['Value']>0) {


                $employee = new employee($user['Value']);
                $hourMonthTodo[$user['Value']] = $employee->getHoursMonthTodo($month, $year);
                $hourly[$user['Value']] = $employee->getHourly($month, $year);
                $hoursByDay[$user['Value']] = $employee->getHoursByDay($month, $year);

                $cmd=$this->db->createCommand("SELECT t.id_user, t.id, t.date, tb.roundBooking AS time, tb.action, tb.actionReason, tb.internet, tbb.BDE1
                                               FROM hr_tracking AS t
                                               LEFT JOIN hr_timux_booking AS tb ON tb.tracking_id=t.id
                                               LEFT JOIN hr_timux_booking_bde AS tbb ON tbb.tracking_id=t.id
                                               WHERE $date t.id_user={$user['Value']} AND tb.action!='NULL'  GROUP BY t.id  ORDER BY  t.date ASC , t.time ASC, tb.action ASC");

                $data = $cmd->query();
                $data = $data->readAll();



                $nextBookingType = 'IN';
                $type = '';
                $timeCode = '';
                foreach($data as $d) {

                    $type = $this->isBookingIn($d) ? 'IN' : 'OUT';

                    if($type == $nextBookingType) {
                        if($type == 'IN') {
                            $bookinIN = $d['time'];
                            $timeCode = $d['BDE1'];
                        } else {
                            $t = bcdiv((strtotime($d['time']) - strtotime($bookinIN)), 3600, 4);
                            $timeCodeList[$timeCode][$user['Text']]['total'] = bcadd($timeCodeList[$timeCode][$user['Text']]['total'], $t,4);
                            $timeCodeList[$timeCode][$user['Text']]['hourDayTodo'] = $hoursByDay[$user['Value']];
                            $timeCodeList[$timeCode][$user['Text']]['hourly'] = $hourly[$user['Value']];
                            $timeCodeList[$timeCode][$user['Text']]['id'] = $user['Value'];
                            $timeCodeList[$timeCode][$user['Text']]['hourMonthTodo'] = $hourMonthTodo[$user['Value']];

                            $timeCodeList[$timeCode]['total'] = bcadd($timeCodeList[$timeCode]['total'],$t,4);
                            $bookinIN = 0;
                            $timeCode = '';
                        }

                        $nextBookingType = $nextBookingType == 'IN' ? 'OUT' : 'IN';

                    } else {

                        $bookinIN = 0;
                        $timeCode = '';
                        $nextBookingType = 'IN';

                    }
                }
            }


        }

        foreach($userList as $user) {
            if($user['Value']>0) {

                $employee = new employee($user['Value']);

                $dtcH = $employee->getDefaultHolidaysCounter();
                $dtcO = $employee->getDefaultOvertimeCounter();

                $h = $employee->getRequest($year, $month, $dtcH);
                if($h['nbre']>0) {
                    if($h['disp'] == 'day') {
                        $timeCodeList[$dtcH]['total'] = bcadd($timeCodeList[$dtcH]['total'], bcmul($h['nbre'],$hoursByDay[$user['Value']],4),4);
                        $timeCodeList[$dtcH][$user['Text']]['total'] = bcadd($timeCodeList[$dtcH][$user['Text']]['total'], bcmul($h['nbre'],$hoursByDay[$user['Value']],4),4);
                        $timeCodeList[$dtcH][$user['Text']]['hourDayTodo'] = $hoursByDay[$user['Value']];
                        $timeCodeList[$dtcH][$user['Text']]['hourly'] = $hourly[$user['Value']];
                        $timeCodeList[$dtcH][$user['Text']]['id'] = $user['Value'];
                        $timeCodeList[$dtcH][$user['Text']]['hourMonthTodo'] = $hourMonthTodo[$user['Value']];

                    } else {
                        $timeCodeList[$dtcH]['total'] += bcadd($timeCodeList[$dtcH]['total'], $h['nbre'],4);
                        $timeCodeList[$dtcH][$user['Text']]['total'] = bcadd($timeCodeList[$dtcH][$user['Text']]['total'], $h['nbre'],4);
                        $timeCodeList[$dtcH][$user['Text']]['hourDayTodo'] = $hoursByDay[$user['Value']];
                        $timeCodeList[$dtcH][$user['Text']]['hourly'] = $hourly[$user['Value']];
                        $timeCodeList[$dtcH][$user['Text']]['id'] = $user['Value'];
                        $timeCodeList[$dtcH][$user['Text']]['hourMonthTodo'] = $hourMonthTodo[$user['Value']];
                    }
                }

                $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE id NOT IN ($dtcH,$dtcO) AND type!='load'");
                $data = $cmd->query();
                $data = $data->readAll();

                foreach($data as $d) {
                    $h = $employee->getRequest($year, $month, $d['id']);
                    if($h['nbre']>0) {
                        if($h['disp'] == 'day') {
                            $timeCodeList[$d['id']]['total'] = bcadd($timeCodeList[$d['id']]['total'], bcmul($h['nbre'],$hoursByDay[$user['Value']],4),4);
                            $timeCodeList[$d['id']][$user['Text']]['total'] += bcadd($timeCodeList[$d['id']][$user['Text']]['total'], bcmul($h['nbre'],$hoursByDay[$user['Value']],4),4);
                            $timeCodeList[$d['id']][$user['Text']]['hourDayTodo'] = $hoursByDay[$user['Value']];
                            $timeCodeList[$d['id']][$user['Text']]['hourly'] = $hourly[$user['Value']];
                            $timeCodeList[$d['id']][$user['Text']]['id'] = $user['Value'];
                            $timeCodeList[$d['id']][$user['Text']]['hourMonthTodo'] = $hourMonthTodo[$user['Value']];
                        } else {
                            $timeCodeList[$d['id']]['total'] += bcadd($timeCodeList[$d['id']]['total'], $h['nbre'],4);
                            $timeCodeList[$d['id']][$user['Text']]['total'] += bcadd($timeCodeList[$d['id']][$user['Text']]['total'] , $h['nbre'],4);
                            $timeCodeList[$d['id']][$user['Text']]['hourDayTodo'] = $hoursByDay[$user['Value']];
                            $timeCodeList[$d['id']][$user['Text']]['hourly'] = $hourly[$user['Value']];
                            $timeCodeList[$d['id']][$user['Text']]['id'] = $user['Value'];
                            $timeCodeList[$d['id']][$user['Text']]['hourMonthTodo'] = $hourMonthTodo[$user['Value']];
                        }
                    }
                }

                for($i=1; $i<date('t', mktime(0,0,0,$month,1,$year));$i++) {
                    $nwd = $employee->getNonWorkingDay($year, $month, $i);
                    if($nwd > 0 && $employee->isWorking($year, $month, $i)) {
                        $timeCodeList[Prado::localize('Non working day')]['total'] = bcadd($timeCodeList[Prado::localize('Non working day')]['total'] , bcmul($nwd,$hoursByDay[$user['Value']],4),4);
                        $timeCodeList[Prado::localize('Non working day')][$user['Text']]['total'] = bcadd($timeCodeList[Prado::localize('Non working day')][$user['Text']]['total'], bcmul($nwd,$hoursByDay[$user['Value']],4),4);
                        $timeCodeList[Prado::localize('Non working day')][$user['Text']]['hourDayTodo'] = $hoursByDay[$user['Value']];
                        $timeCodeList[Prado::localize('Non working day')][$user['Text']]['hourly'] = $hourly[$user['Value']];
                        $timeCodeList[Prado::localize('Non working day')][$user['Text']]['id'] = $user['Value'];
                        $timeCodeList[Prado::localize('Non working day')][$user['Text']]['hourMonthTodo'] = $hourMonthTodo[$user['Value']];
                    }
                }

                $employee = new employee($user['Value']);
                for($i=1; $i<=date("t",mktime(0,0,0,$month,1,$year)); $i++) {
                    $b = $employee->getDayDone($i, $month, $year);
                    $todo = $employee->getDayTodo($i, $month, $year);

                    $overtime = bcsub($b['done'],$todo,4);

                    if($overtime != 0) {
                        $timeCodeList[Prado::localize('Overtime2')][$user['Text']]['total'] = bcadd($timeCodeList[Prado::localize('Overtime2')][$user['Text']]['total'],$overtime,4);
                        $timeCodeList[Prado::localize('Overtime2')][$user['Text']]['hourDayTodo'] = $hoursByDay[$user['Value']];
                        $timeCodeList[Prado::localize('Overtime2')]['total'] = bcadd($timeCodeList[Prado::localize('Overtime2')]['total'], $overtime,4);
                        $timeCodeList[Prado::localize('Overtime2')][$user['Text']]['hourly'] = $hourly[$user['Value']];
                        $timeCodeList[Prado::localize('Overtime2')][$user['Text']]['id'] = $user['Value'];
                        $timeCodeList[Prado::localize('Overtime2')][$user['Text']]['hourMonthTodo'] = $hourMonthTodo[$user['Value']];
                    }
                }
            }
        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_config" );
        $query = $cmd->query();
        $data = $query->read();
        $hoursByDay = bcdiv($data['hoursByWeek'], $data['daysByWeek'], 4);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_site" );
        $query = $cmd->query();
        $data = $query->read();

        $devise = " ".$data['devise'];

        $res = array();

        $extendHourly = false;
        if(class_exists($computation2)) {
            $extendHourly = new $computation2();
        }


        foreach($timeCodeList as $k=>$v) {

            $totalCost = 0;
            if(is_array($v)) {
                foreach($v as $k2=>$u) {

                    if(is_array($u)) {

                        if($extendHourly) {
                            $u['hourly'] = $extendHourly->getHourly($month,$year, $u);
                        }

                        $totalCost = bcadd($totalCost, bcmul($u['hourly'], round($u['total'],2), 2),2);
                    }
                }
            }

            if($k != '') {

                $cmd = $this->db->createCommand( "SELECT * FROM hr_timux_timecode WHERE abbreviation=:abb || id=:abb");
                $cmd->bindValue(":abb", $k);
                $data = $cmd->query();
                $data = $data->read();

                if($data) {
                    $r['timecode'] = '<b>'.$data['name'].'</b>';

                    //how the time code is computed
                    $r['hoursdone'] = '<b>'.round($v['total'],2).'</b>';
                    $r['cost'] = sprintf("<b>%.02f $devise</b>",$totalCost);
                    $r['hourly'] = '';
                    $r['id'] = '';
                    $res[] = $r;

                    if($this->showUser->getChecked()) {
                        foreach($v as $k=>$u) {
                            if($u['hourDayTodo']>0) $hoursByDay2 = $u['hourDayTodo']; else $hoursByDay2 = $hoursByDay;
                            if($k != 'total') {

                                if($extendHourly) {
                                    $u['hourly'] = $extendHourly->getHourly($month,$year, $u);
                                }

                                $r['timecode'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$k;
                                $r['hoursdone'] = round($u['total'],2);
                                $r['cost'] = sprintf("%.02f $devise",bcmul($u['hourly'], round($u['total'],2) , 2));
                                $r['hourly'] = sprintf("%.02f $devise",$u['hourly']);
                                $r['id'] = $u['id'];
                                $res[] = $r;
                            }
                        }
                    }
                } else {
                    $r['timecode'] = '<b>'.$k.'</b>';
                    $r['hoursdone'] = '<b>'.round($v['total'],2).'</b>';
                    $r['cost'] = sprintf("<b>%.02f $devise</b>",$totalCost);
                    $r['hourly'] = '';
                    $r['id'] = '';
                    $res[] = $r;

                    if($this->showUser->getChecked()) {
                        foreach($v as $k=>$u) {
                            if($u['hourDayTodo']>0) $hoursByDay2 = $u['hourDayTodo']; else $hoursByDay2 = $hoursByDay;
                            if($k != 'total') {

                                if($extendHourly) {
                                    $u['hourly'] = $extendHourly->getHourly($month,$year, $u);
                                }

                                $r['timecode'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$k;
                                $r['hoursdone'] = round($u['total'],2);
                                $r['cost'] = sprintf("%.02f $devise",bcmul($u['hourly'], round($u['total'],2) , 2));
                                $r['hourly'] = sprintf("%.02f $devise",$u['hourly']);
                                $r['id'] = $u['id'];
                                $res[] = $r;
                            }
                        }
                    }
                }


            } else {
                $r['timecode'] = '<b>'.Prado::localize('Unkown time code').'</b>';
                $r['hoursdone'] = '<b>'.round($v['total'],2).'</b>';
                $r['cost'] = sprintf("<b>%.02f $devise</b>",$totalCost);
                $r['hourly'] = '';
                $r['id'] = '';
                $res[] = $r;

                if($this->showUser->getChecked()) {
                    foreach($v as $k=>$u) {
                        if($u['hourDayTodo']>0) $hoursByDay2 = $u['hourDayTodo']; else $hoursByDay2 = $hoursByDay;
                        if($k != 'total') {

                            if($extendHourly) {
                                $u['hourly'] = $extendHourly->getHourly($month,$year, $u);
                            }

                            $r['timecode'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$k;
                            $r['hoursdone'] = round($u['total'],2);
                            $r['cost'] = sprintf("%.02f $devise",bcmul($u['hourly'], round($u['total'],2) , 2));
                            $r['hourly'] = sprintf("%.02f $devise",$u['hourly']);
                            $r['id'] = $u['id'];
                            $res[] = $r;
                        }
                    }
                }
            }


        }


        return $res;
    }

    protected function isBookingIn($booking) {
        //255 => in
        //254 => out

        if($booking['action'] == 255)
            return true;
        if($booking['action'] == 254)
            return false;

        if($booking['action'] == 100 && substr($booking['actionReason'],-2,2) == 'IN')
            return true;

        // the last is in every case an out booking
        return false;
    }


    public function itemCreated($sender, $param) {
        $item=$param->Item;

        if($item->ItemType==='EditItem') {

        }

        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' ) {
            //$item->edit->EditButton->Attributes->onclick='alert(\'Cannot modifiy the hourly for this line\'); return false;';
        }
    }

    public function selectionChangedYear($sender, $param) {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedMonth($sender, $param) {
        $this->onRefresh($sender, $param);
    }

    public function selectionChangedDepartment($sender, $param) {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterDepartment'] = $this->FilterDepartment->getSelectedValue();

        $this->FilterEmployee->DataSource=$this->EmployeeList;
        $this->FilterEmployee->dataBind();

        if(count($this->EmployeeList)>0) {
            $this->FilterEmployee->setSelectedIndex(0);
        }

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);

    }

    public function selectionChangedEmployee($sender, $param) {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterEmployee'] = $this->FilterEmployee->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);

    }

    public function onRefresh($sender, $param) {

        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterYear'] = $this->FilterYear->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterMonth'] = $this->FilterMonth->getSelectedValue();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }


    public function editItem($sender,$param) {
        $this->DataGrid->EditItemIndex=$param->Item->ItemIndex;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
    }

    public function cancelItem($sender,$param) {
        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
    }


    public function saveItem($sender,$param) {
        $item=$param->Item;

        $year = $this->FilterYear->getSelectedValue();
        $month = $this->FilterMonth->getSelectedValue();

        $cmd=$this->db->createCommand("SELECT COUNT(*) AS n FROM hr_timux_hourly WHERE user_id=:id AND month=:month AND year=:year");
        $cmd->bindValue(":id",$this->DataGrid->DataKeys[$item->ItemIndex]);
        $cmd->bindValue(":month",$month);
        $cmd->bindValue(":year",$year);
        $data = $cmd->query();
        $data = $data->read();
        if($data['n'] > 0) {
            $cmd=$this->db->createCommand("UPDATE hr_timux_hourly SET hourly=:hourly WHERE user_id=:id AND month=:month AND year=:year");
            $cmd->bindValue(":id",$this->DataGrid->DataKeys[$item->ItemIndex]);
            $cmd->bindValue(":month",$month);
            $cmd->bindValue(":year",$year);
            $cmd->bindValue(":hourly",$item->hourly->TextBox->Text);
            $cmd->execute();
        } else {
            $cmd=$this->db->createCommand("INSERT hr_timux_hourly SET hourly=:hourly, user_id=:id, month=:month, year=:year");
            $cmd->bindValue(":id",$this->DataGrid->DataKeys[$item->ItemIndex]);
            $cmd->bindValue(":month",$month);
            $cmd->bindValue(":year",$year);
            $cmd->bindValue(":hourly",$item->hourly->TextBox->Text);
            $cmd->execute();

        }

        $this->DataGrid->EditItemIndex=-1;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
    }

}

?>