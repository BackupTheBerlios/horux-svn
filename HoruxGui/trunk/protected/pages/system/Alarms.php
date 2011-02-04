<?php

Prado::using('horux.pages.system.sql');

class Alarms extends PageList
{
    protected $alarmMessage = array();

    protected function getObjectAlarms($data, $indexStart, $indexStop)
    {
        if(count($data)<$indexStop) $indexStop = count($data);

        for($i=$indexStart; $i<$indexStop; $i++)
        {
            $dateAndTime = explode(" ", $data[$i]['datetime_']);
            $data[$i]['datetime_'] = date("d-m-Y", strtotime($dateAndTime[0])).' '.$dateAndTime[1];

            $text =  $this->alarmMessage[$data[$i]['type']];

            $data[$i]['description'] = $text;

            if($data[$i]['type'] >= 1001 && $data[$i]['type'] <= 1099 || $data[$i]['type']== 1102)
            {
                $object_type = Prado::localize("Device");
                $sql = "SELECT * FROM hr_device WHERE id=".$data[$i]['id_object'];
                $command=$this->db->createCommand($sql);
                $dataObj=$command->query();
                $dataObj = $dataObj->read();
                $object = $dataObj['name'];
            }

            if($data[$i]['type'] >= 1100 && $data[$i]['type'] <= 1199 && $data[$i]['type'] != 1102)
            {
                $object_type =  Prado::localize("User");
                $sql = "SELECT * FROM hr_user WHERE id=".$data[$i]['id_object'];
                $command=$this->db->createCommand($sql);
                $dataObj=$command->query();
                $dataObj = $dataObj->read();
                $object = $dataObj['name']." ".$dataObj['firstname'];
            }

            $data[$i]['object'] = '<i>'.$object_type.'</i>:'.$object;

        }


        $connection->Active=false;  // connection is established

        return $data;
    }



    protected function getData()
    {
        $this->alarmMessage[1001] = Prado::localize("1001");
        $this->alarmMessage[1002] = Prado::localize("1002");
        $this->alarmMessage[1003] = Prado::localize("1003");
        $this->alarmMessage[1004] = Prado::localize("1004");
        $this->alarmMessage[1005] = Prado::localize("1005");
        $this->alarmMessage[1006] = Prado::localize("1006");
        $this->alarmMessage[1007] = Prado::localize("1007");
        $this->alarmMessage[1008] = Prado::localize("1008");
        $this->alarmMessage[1009] = Prado::localize("1009");
        $this->alarmMessage[1010] = Prado::localize("1010");
        $this->alarmMessage[1011] = Prado::localize("1011");
        $this->alarmMessage[1012] = Prado::localize("1012");
        $this->alarmMessage[1013] = Prado::localize("1013");
        $this->alarmMessage[1014] = Prado::localize("1014");
        $this->alarmMessage[1015] = Prado::localize("1015");
        $this->alarmMessage[1016] = Prado::localize("1016");
        $this->alarmMessage[1017] = Prado::localize("1017");

        $this->alarmMessage[1100] = Prado::localize("1100");
        $this->alarmMessage[1101] = Prado::localize("1101");
        $this->alarmMessage[1102] = Prado::localize("1102");

        $this->alarmMessage[1200] = Prado::localize("1200");

        $from = "";
        $until = "";

        $from = $this->dateToSql( $this->from->SafeText );
        $until = $this->dateToSql( $this->until->SafeText );


        if($from == "" && $until == "")
        {
            $cmd=$this->db->createCommand(SQL::SQL_GET_ALARMS);
        }
        else
        {
            if($from != "" && $until != "")
            {
                $cmd=$this->db->createCommand(SQL::SQL_GET_ALARMS_BY_DATE);
                $cmd->bindValue(":from",$from,PDO::PARAM_STR);
                $cmd->bindValue(":until",$until,PDO::PARAM_STR);
            }
            if($from != "" && $until == "")
            {
                $cmd=$this->db->createCommand(SQL::SQL_GET_ALARMS_BY_DATE_FROM);
                $cmd->bindValue(":from",$from,PDO::PARAM_STR);
            }
            if($from == "" && $until != "")
            {
                $cmd=$this->db->createCommand(SQL::SQL_GET_ALARMS_BY_DATE_UNTIL);
                $cmd->bindValue(":until",$until,PDO::PARAM_STR);
            }

        }
        $indexStart = $this->DataGrid->CurrentPageIndex*15;
        $indexStop = $indexStart+15;
        $data = $cmd->query();
        $data = $data->readAll();


        return $this->getObjectAlarms( $data, $indexStart, $indexStop );
    }

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();


        }
    }

    public function onRefresh($sender, $param)
    {
        //$this->DataGrid->CurrentPageIndex = 0;

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }
}
?>
