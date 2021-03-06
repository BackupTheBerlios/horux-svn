<?php

Prado::using('horux.pages.openTime.sql');

class openTimeList extends PageList
{
    protected function getData()
    {
        $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_OPEN_TIME);
        $data=$cmd->query();

        $connection->Active=false;

        return $data;
    }

    public function onLoad($param)
    {
        parent::onLoad($param);

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

        $param = $this->Application->getParameters();
        $superAdmin = $this->Application->getUser()->getSuperAdmin();

        if($param['appMode'] == 'demo' && $superAdmin == 0)
        {
            $this->tbb->delete->setEnabled(false);
        }
    }

    protected function onPrint()
    {
        parent::onPrint();
        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the open time')),0,0,'L');
        $this->pdf->Ln(10);

        $this->pdf->setDefaultFont();

        $openTime = $this->getData();

        $i = 0;

        foreach($openTime as $ot)
        {
            $this->pdf->SetDrawColor(0);
            $this->pdf->SetFont('Arial','B',11);
            $this->pdf->SetLineWidth(0.4);
            $this->pdf->Cell(0,6,utf8_decode($ot['name']),'B',1,'L');
            $this->pdf->setDefaultFont();
            $this->pdf->SetLineWidth(0.1);
            $this->pdf->SetDrawColor(127);
            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Apply to the week-end')),'B',0,'L');
            $this->pdf->Cell(0,6,utf8_decode($ot['week_end'] == "1" ? Prado::localize('Yes') : Prado::localize('No')),'B',1,'L');
            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Apply to the non working day')),'B',0,'L');
            $this->pdf->Cell(0,6,utf8_decode($ot['non_working_day'] == "1" ? Prado::localize('Yes') : Prado::localize('No')),'B',1,'L');
            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Valid from')),'B',0,'L');
            $this->pdf->Cell(0,6,utf8_decode($ot['validity_date'] == "0000-00-00" ? "-" : $this->dateFromSql($ot['validity_date'])),'B',1,'L');
            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Valid until')),'B',0,'L');
            $this->pdf->Cell(0,6,utf8_decode($ot['validity_date_to'] == "0000-00-00" ? "-" : $this->dateFromSql($ot['validity_date_to'])),'B',1,'L');

            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Apply on the devices')),'B',0,'L');
            $cmd=$this->db->createCommand("SELECT * FROM hr_device AS d LEFT JOIN hr_openTime_attribution AS oa ON oa.id_device=d.id WHERE oa.id_openTime=".$ot['id']);
            $devices=$cmd->query();

            $deviceList = array();
            foreach($devices as $device)
            {
                $deviceList[] = $device['name'];
            }
            $deviceList = join(",", $deviceList);
            $this->pdf->Cell(0,6,$deviceList,'B',1,'L');

            $this->pdf->SetLineWidth(0.2);
            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Description')),'B',0,'L');
            $this->pdf->Cell(0,6,utf8_decode($ot['comment']),'B',1,'L');

            $this->pdf->SetLineWidth(0.1);
            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Monday')),'B',0,'L');
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='lundi' AND id_openTime=".$ot['id']);
            $times=$cmd->query();

            foreach($times as $time)
            {
                $from = $time['from'];
                $until = $time['until'];
                $timeStart = str_pad(((int)($from / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($from % 60),2,"0",STR_PAD_LEFT);
                $timeEnd = str_pad(((int)($until / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($until % 60),2,"0",STR_PAD_LEFT);
                $this->pdf->Cell(25,6,$timeStart."-".$timeEnd,'B',0,'L');
            }
            $this->pdf->Cell(0,6,"",'B',1,'L');


            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Tuesday')),'B',0,'L');
            if($ot['monday_default'] == 1)
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='lundi' AND id_openTime=".$ot['id']);
            else
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='mardi' AND id_openTime=".$ot['id']);
            $times=$cmd->query();

            foreach($times as $time)
            {
                $from = $time['from'];
                $until = $time['until'];
                $timeStart = str_pad(((int)($from / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($from % 60),2,"0",STR_PAD_LEFT);
                $timeEnd = str_pad(((int)($until / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($until % 60),2,"0",STR_PAD_LEFT);
                $this->pdf->Cell(25,6,$timeStart."-".$timeEnd,'B',0,'L');
            }
            $this->pdf->Cell(0,6,"",'B',1,'L');


            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Wednesday')),'B',0,'L');
            if($ot['monday_default'] == 1)
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='lundi' AND id_openTime=".$ot['id']);
            else
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='mercredi' AND id_openTime=".$ot['id']);
            $times=$cmd->query();

            foreach($times as $time)
            {
                $from = $time['from'];
                $until = $time['until'];
                $timeStart = str_pad(((int)($from / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($from % 60),2,"0",STR_PAD_LEFT);
                $timeEnd = str_pad(((int)($until / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($until % 60),2,"0",STR_PAD_LEFT);
                $this->pdf->Cell(25,6,$timeStart."-".$timeEnd,'B',0,'L');
            }
            $this->pdf->Cell(0,6,"",'B',1,'L');


            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Thursday')),'B',0,'L');
            if($ot['monday_default'] == 1)
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='lundi' AND id_openTime=".$ot['id']);
            else
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='jeudi' AND id_openTime=".$ot['id']);
            $times=$cmd->query();
            foreach($times as $time)
            {
                $from = $time['from'];
                $until = $time['until'];
                $timeStart = str_pad(((int)($from / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($from % 60),2,"0",STR_PAD_LEFT);
                $timeEnd = str_pad(((int)($until / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($until % 60),2,"0",STR_PAD_LEFT);
                $this->pdf->Cell(25,6,$timeStart."-".$timeEnd,'B',0,'L');
            }
            $this->pdf->Cell(0,6,"",'B',1,'L');

            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Friday')),'B',0,'L');
            if($ot['monday_default'] == 1)
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='lundi' AND id_openTime=".$ot['id']);
            else
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='vendredi' AND id_openTime=".$ot['id']);
            $times=$cmd->query();
            foreach($times as $time)
            {
                $from = $time['from'];
                $until = $time['until'];
                $timeStart = str_pad(((int)($from / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($from % 60),2,"0",STR_PAD_LEFT);
                $timeEnd = str_pad(((int)($until / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($until % 60),2,"0",STR_PAD_LEFT);
                $this->pdf->Cell(25,6,$timeStart."-".$timeEnd,'B',0,'L');
            }
            $this->pdf->Cell(0,6,"",'B',1,'L');

            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Saturday')),'B',0,'L');
            if($ot['monday_default'] == 1)
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='lundi' AND id_openTime=".$ot['id']);
            else
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='samedi' AND id_openTime=".$ot['id']);
            $times=$cmd->query();
            foreach($times as $time)
            {
                $from = $time['from'];
                $until = $time['until'];
                $timeStart = str_pad(((int)($from / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($from % 60),2,"0",STR_PAD_LEFT);
                $timeEnd = str_pad(((int)($until / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($until % 60),2,"0",STR_PAD_LEFT);
                $this->pdf->Cell(25,6,$timeStart."-".$timeEnd,'B',0,'L');
            }
            $this->pdf->Cell(0,6,"",'B',1,'L');

            $this->pdf->Cell(60,6,utf8_decode(Prado::localize('Sunday')),'B',0,'L');
            if($ot['monday_default'] == 1)
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='lundi' AND id_openTime=".$ot['id']);
            else
            $cmd=$this->db->createCommand("SELECT * FROM hr_openTime_time WHERE day='dimanche' AND id_openTime=".$ot['id']);
            $times=$cmd->query();
            foreach($times as $time)
            {
                $from = $time['from'];
                $until = $time['until'];
                $timeStart = str_pad(((int)($from / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($from % 60),2,"0",STR_PAD_LEFT);
                $timeEnd = str_pad(((int)($until / 60)),2,"0",STR_PAD_LEFT).":".str_pad(($until % 60),2,"0",STR_PAD_LEFT);
                $this->pdf->Cell(25,6,$timeStart."-".$timeEnd,'B',0,'L');
            }
            $this->pdf->Cell(0,6,"",'B',1,'L');

            $this->pdf->Ln(10);
            if($i % 2) $this->pdf->AddPage();

            $i++;

        }

        $this->pdf->render();
    }

    public function checkboxAllCallback($sender, $param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $isChecked = $sender->getChecked();

        foreach($cbs as $cb)
        {
            $cb->setChecked($isChecked);
        }

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

                    $cmd=$this->db->createCommand(SQL::SQL_REMOVE_OPEN_TIME);
                    $cmd->bindValue(":id",$cb->Value);
                    if($cmd->execute())
                    $nDelete++;

                    $cmd=$this->db->createCommand(SQL::SQL_REMOVE_OPEN_TIME_TIME);
                    $cmd->bindValue(":id",$cb->Value);
                    $cmd->execute();

                    $cmd=$this->db->createCommand(SQL::SQL_DELETE_OPEN_TIME_ATTRIBUTION_2);
                    $cmd->bindValue(":id",$cb->Value);
                    $cmd->execute();

                }
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg);
        else
        $pBack = array('okMsg'=>Prado::localize('{n} open time was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('openTime.openTimeList',$pBack));
    }


    public function onEdit($sender,$param)
    {

        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('openTime.openTimeList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];

        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('openTime.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('openTime.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('openTime.openTimeList',$pBack));
    }

}
?>
