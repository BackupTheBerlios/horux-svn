<?php

Prado::using('horux.pages.hardware.sql');

class HardwareList extends PageList
{

    protected function getData()
    {

        if($this->Filter->getData() == "accessPoint")
        {
            $sql = SQL::SQL_GET_HARDWARE_ACCESSPOINT;
        }
        else if($this->Filter->getData() == "others")
        {
            $sql = SQL::SQL_GET_HARDWARE_OTHERS;
        }
        else
        {
            $sql = SQL::SQL_GET_HARDWARE_ALL;

        }

        $command=$this->db->createCommand($sql);
        $dataReader=$command->query();

        $connection->Active=false;  // connection is established

        return $dataReader;
    }

    public function onLoad($param)
    {
        parent::onLoad($param);
        if(!$this->IsPostBack)
        {
            $this->Filter->setData('*');

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0)
            {
                $this->tbb->delete->setEnabled(false);
            }
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

    protected function onPrint()
    {
        parent::onPrint();
        $this->pdf->AddPage();

        $command=$this->db->createCommand(SQL::SQL_GET_HARDWARE_ALL);
        $dataReader=$command->query();

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the hardware')),0,0,'L');
        $this->pdf->Ln(10);

        $this->pdf->setDefaultFont();

        $header = array(utf8_decode(Prado::localize("Name")),
            utf8_decode(Prado::localize("Access Point")),
            utf8_decode(Prado::localize("Debug")),
            utf8_decode(Prado::localize("Type")),
            utf8_decode(Prado::localize("Access plugin")),
            utf8_decode(Prado::localize("Open time"))
        );

        //Couleurs, �paisseur du trait et police grasse
        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        //En-t�te
        $w=array(30,28,25,48,30,30);
        for($i=0;$i<count($header);$i++)
        $this->pdf->Cell($w[$i],7,$header[$i],1,0,'C',1);
        $this->pdf->Ln();
        //Restauration des couleurs et de la police
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');
        //Donn�es
        $fill=false;

        $this->pdf->SetFont('Arial','',9);

        foreach($dataReader as $d)
        {
            $name= utf8_decode($d['name']);
            $accessPoint = utf8_decode( $d['accessPoint']==1 ? Prado::Localize("Yes") : Prado::Localize("No"));
            $debug = utf8_decode($d['isLog']==1 ? Prado::Localize("Yes") : Prado::Localize("No"));
            $type = utf8_decode($d['type']);
            $accessPlugin = utf8_decode($d['accessPlugin']);

            $command=$this->db->createCommand("SELECT * FROM hr_openTime AS o LEFT JOIN hr_openTime_attribution AS oa ON oa.id_openTime=o.id WHERE oa.id_device=".$d['id']);
            $dataOpentime=$command->query();
            $openTime = array();

            foreach( $dataOpentime as $ot)
            {
                $openTime[] = $ot['name'];
            }

            $openTime = join(", ", $openTime);

            $this->pdf->Cell($w[0],6,$name,'LR',0,'L',$fill);
            $this->pdf->Cell($w[1],6,$accessPoint,'LR',0,'L',$fill);
            $this->pdf->Cell($w[2],6,$debug,'LR',0,'C',$fill);
            $this->pdf->Cell($w[3],6,$type,'LR',0,'C',$fill);
            $this->pdf->Cell($w[4],6,$accessPlugin,'LR',0,'C',$fill);
            $this->pdf->Cell($w[5],6,$openTime,'LR',0,'C',$fill);
            $this->pdf->Ln();
            $fill=!$fill;

        }

        $this->pdf->Cell(array_sum($w),0,'','T');


        $this->pdf->render();
    }

    public function selectionChanged($sender,$param)
    {
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
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
        $cbChecked = 0;
        $koMsg = '';

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
                $id = $cb->Value;

                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {
                    $cmd=$this->db->createCommand("SELECT * FROM hr_device WHERE id=".$id);
                    $data = $cmd->query();
                    $data = $data->read();

                    $type = $data['type'];

                    $cmd=$this->db->createCommand("DELETE FROM hr_device WHERE id=".$id);
                    $cmd->execute();

                    $cmd=$this->db->createCommand("DELETE FROM hr_".$type." WHERE id_device=".$id);
                    $cmd->execute();

                    $cmd=$this->db->createCommand("DELETE FROM hr_standalone_action_service WHERE rd_id=".$id);
                    $cmd->execute();

                    $cmd=$this->db->createCommand("DELETE FROM hr_openTime_attribution WHERE id_device=".$id);
                    $cmd->execute();

                    $cmd=$this->db->createCommand("DELETE FROM hr_user_group_access WHERE id_device=".$id);
                    $cmd->execute();



                    $nDelete++;
                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} interface was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
    }


    public function onEdit($sender,$param)
    {

        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];

        if(is_numeric($id))
        {
            $this->getRightPage($id);
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;

        foreach($cbs as $cb)
        {
            $value = $cb->Value;

            if( (bool)$cb->getChecked() && $value != "0")
            {
                $this->getRightPage($value);
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
    }

    public function getRightPage($id)
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_device WHERE id=".$id);
        $data = $cmd->query();
        $data = $data->read();

        $pBack = array('id'=>$id);

        $this->Response->redirect($this->Service->constructUrl('hardware.device.'.$data['type'].'.mod',$pBack));
    }


    public function setOpenTime($sender, $param)
    {
        $pBack = array( 'id'=>$sender->Text );
        $this->Response->redirect($this->Service->constructUrl('openTime.attribute',$pBack));
    }

    
    public function setActive($sender,$param)
    {
        $id = $sender->Text;
        $cmd=$this->db->createCommand("UPDATE hr_device SET isActive=:flag WHERE id=:id");
        $cmd->bindValue(":id",$id);

        if($sender->ImageUrl == "./themes/letux/images/menu/icon-16-checkin.png")
        {
            $flag = 0;
            $sender->ImageUrl = "./themes/letux/images/menu/icon-16-checkin.png";
            $cmd->bindValue(":flag",$flag);

            $cmd2=$this->db->createCommand("SELECT * FROM hr_device WHERE id=:id");
            $cmd2->bindValue(":id",$id);
            $cmd2 = $cmd2->query();
            $data2 = $cmd2->read();

            $this->log("Disable the device ".$data2['name']);

        }
        else
        {
            $flag = 1;
            $sender->ImageUrl = "./themes/letux/images/menu/icon-16-cross.png";
            $cmd->bindValue(":flag",$flag);

            $cmd2=$this->db->createCommand("SELECT * FROM hr_device WHERE id=:id");
            $cmd2->bindValue(":id",$id);
            $cmd2 = $cmd2->query();
            $data2 = $cmd2->read();

            $this->log("Enable the device ".$data2['name']);
        }
        $cmd->execute();

        $horuxService = new THoruxService();
        $horuxService->onStop();
        $horuxService->onStart();

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }
}

?>
