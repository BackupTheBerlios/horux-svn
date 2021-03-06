<?php

Prado::using('horux.pages.key.sql');

class KeyList extends PageList
{
    protected function getData()
    {
        if(isset($this->Request['f1']))
        {
            $identificator = $this->Request['f1'];
            $attributed = $this->Request['f2'];
            $status = $this->Request['f3'];
            $serialNumber = '';
        }
        else
        {
            $identificator = $this->FilterIdentificator->SafeText;
            $attributed = $this->FilterUsed->getSelectedValue();
            $status = $this->FilterStatus->getSelectedValue();
            $serialNumber = $this->FilterSerialNumber->SafeText;
        }

        if($attributed == "all")
        $attributed = "";
        elseif($attributed == "used")
        $attributed = " isUsed=1 AND";
        else
        $attributed = " isUsed=0 AND";

        if($status == "all")
        $status = "";
        elseif($status == "block")
        $status = " isBlocked=1 AND";
        else
        $status = " isBlocked=0 AND";


        $sql = "SELECT * FROM  hr_keys WHERE identificator!='??' AND $status $attributed identificator LIKE '%$identificator%' AND serialNumber LIKE '%$serialNumber%'";

        $cmd=$this->db->createCommand($sql);
        $dataKey = $cmd->query();
        $dataKey = $dataKey->readAll();

        for($i=0; $i<count($dataKey); $i++)
        {
            $sql = "SELECT pe.id, pe.name, pe.firstname FROM hr_user AS pe LEFT JOIN hr_keys_attribution AS ta ON ta.id_user=pe.id LEFT JOIN hr_keys AS t ON t.id=ta.id_key WHERE t.id={$dataKey[$i]['id']}";
            $cmd2=$this->db->createCommand($sql);
            $dataAttribution = $cmd2->query();
            $dataAttribution = $dataAttribution->read();
            $dataKey[$i]['person'] = $dataAttribution['name'].' '.$dataAttribution['firstname'];
            $dataKey[$i]['url'] = $this->Service->constructUrl('user.mod', array('id' => $dataAttribution['id']));
        }

        $connection->Active=false;

        return $dataKey;
    }

    public function onLoad($param)
    {
        parent::onLoad($param);

        $this->setHoruxSysTray(true);

        if(!$this->IsPostBack)
        {
            $FilterIdentificator = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterIdentificator'];
            $FilterUsed = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterUsed'];
            $FilterStatus = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'];
            $FilterSerialNumber = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterSerialNumber'];

            if($FilterIdentificator)
            $this->FilterIdentificator->Text = $FilterIdentificator;
            if($FilterSerialNumber)
            $this->FilterSerialNumber->Text = $FilterSerialNumber;
            if($FilterUsed)
            $this->FilterUsed->setSelectedValue($FilterUsed);
            if($FilterStatus)
            $this->FilterStatus->setSelectedValue($FilterStatus);

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

        }

        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterIdentificator'] = $this->FilterIdentificator->SafeText;
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterSerialNumber'] = $this->FilterSerialNumber->SafeText;

        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
        }
    }

    public function filterChange($sender, $param)
    {
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    protected function onPrint()
    {
        parent::onPrint();
        $this->pdf->AddPage();

        $data = $this->getData();

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the keys')),0,0,'L');
        $this->pdf->Ln(10);
        $this->pdf->setDefaultFont();

        $identificator = $this->Request['f1'];
        if($identificator == "%" ) $identificator = "*"; else $identificator = $identificator."*";

        $attributed = $this->Request['f2'];

        switch( $attributed )
        {
            case "all":
                $attributed = utf8_decode(Prado::localize('All'));
                break;
            case "used":
                $attributed = utf8_decode(Prado::localize('Attributed'));
                break;
            case "unsued":
                $attributed = utf8_decode(Prado::localize('Unattributed'));
                break;
        }

        $status = $this->Request['f3'];

        switch( $status )
        {
            case "all":
                $status = utf8_decode(Prado::localize('All'));
                break;
            case "block":
                $status = utf8_decode(Prado::localize('Blocked'));
                break;
            case "unblock":
                $status = utf8_decode(Prado::localize('Unblocked'));
                break;
        }


        $this->pdf->Cell(10,5,utf8_decode(Prado::localize('Filter')),'B',1,'L');
        $this->pdf->Ln(1);

        $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Identificator'))." :",0,0,'L');
        $this->pdf->Cell(0,5,$identificator,0,1,'L');

        $this->pdf->Cell(30, 5,utf8_decode(Prado::localize('Attribution'))." :",0,0,'L');
        $this->pdf->Cell(0,5,$attributed,0,1,'L');

        $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Status'))." :",0,0,'L');
        $this->pdf->Cell(0,5,$status,0,1,'L');

        $this->pdf->Ln(10);


        $header = array(utf8_decode(Prado::localize("Identificator")),
            utf8_decode(Prado::localize("Key Number")),
            utf8_decode(Prado::localize("Blocked")),
            utf8_decode(Prado::localize("Attribution")),
        );

        //Couleurs, �paisseur du trait et police grasse
        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        //En-t�te
        $w=array(45,60,25,50);
        for($i=0;$i<count($header);$i++)
        $this->pdf->Cell($w[$i],7,$header[$i],1,0,'C',1);
        $this->pdf->Ln();
        //Restauration des couleurs et de la police
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');
        //Donn�es
        $fill=false;


        foreach($data as $d)
        {
            $identificator= utf8_decode($d['identificator']);
            $number = utf8_decode($d['serialNumber']);
            $isBlocked= utf8_decode($d['isBlocked'] == 1 ? Prado::Localize("Yes") : Prado::Localize("No"));
            $attribution = utf8_decode($d['person']);

            $this->pdf->Cell($w[0],6,$identificator,'LR',0,'L',$fill);
            $this->pdf->Cell($w[1],6,$number,'LR',0,'L',$fill);
            $this->pdf->Cell($w[2],6,$isBlocked,'LR',0,'L',$fill);
            $this->pdf->Cell($w[3],6,$attribution,'LR',0,'L',$fill);
            $this->pdf->Ln();
            $fill=!$fill;
        }

        $this->pdf->Cell(array_sum($w),0,'','T');

        $this->pdf->render();

    }

    public function setBlocked($sender,$param)
    {
        $id = $sender->Text;
        $cmd=$this->db->createCommand(SQL::SQL_SELECT_SETBLOCK_KEY);
        $cmd->bindValue(":id",$id);

        $func = "";

        if($sender->ImageUrl == "./themes/letux/images/menu/icon-16-checkin.png")
        {
            $flag = 1;
            $sender->ImageUrl = "./themes/letux/images/menu/icon-16-access.png";
            $cmd->bindValue(":flag",$flag);
            $func = 'block';

            $cmd2=$this->db->createCommand(SQL::SQL_GET_KEY);
            $cmd2->bindValue(":id",$id);
            $cmd2 = $cmd2->query();
            $data2 = $cmd2->read();

            $this->log("Unblock the key ".$data2['identificator']);
        }
        else
        {
            $flag = 0;
            $sender->ImageUrl = "./themes/letux/images/menu/icon-16-checkin.png";
            $cmd->bindValue(":flag",$flag);
            $func = 'unblock';

            $cmd2=$this->db->createCommand(SQL::SQL_GET_KEY);
            $cmd2->bindValue(":id",$id);
            $cmd2 = $cmd2->query();
            $data2 = $cmd2->read();

            $this->log("Block the key ".$data2['identificator']);

        }
        $cmd->execute();

        $this->addStandalone($func, $id);

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    protected function addStandalone($function, $idkey)
    {

        $sa = new TStandAlone();
        $sa->addStandalone($function, $idkey, 'KeyList');

    }

    public function selectionChanged($sender,$param)
    {
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterIdentificator'] = $this->FilterIdentificator->SafeText;
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterSerialNumber'] = $this->FilterSerialNumber->SafeText;
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterUsed'] = $this->FilterUsed->getSelectedValue();
        $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'] = $this->FilterStatus->getSelectedValue();


        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    public function selectionChangedUsed($sender,$param)
    {
        $this->selectionChanged($sender,$param);
    }

    public function selectionChangedStatus($sender,$param)
    {
        $this->selectionChanged($sender,$param);
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
                    $this->addStandalone('sub',$cb->Value);

                    $cmd=$this->db->createCommand(SQL::SQL_GET_KEY);
                    $cmd->bindValue(":id",$cb->Value);
                    $cmd = $cmd->query();
                    $data = $cmd->read();

                    $this->log("Delete the key: ".$data['serialNumber']);

                    $cmd=$this->db->createCommand(SQL::SQL_REMOVE_TAG);
                    $cmd->bindValue(":id",$cb->Value);
                    if($cmd->execute())
                    $nDelete++;

                    $cmd=$this->db->createCommand(SQL::SQL_REMOVE_TAG_ATTRIBUTION);
                    $cmd->bindValue(":id",$cb->Value);
                    $cmd->execute();

                }
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg);
        else
        $pBack = array('okMsg'=>Prado::localize('{n} key was deleted',array('n'=>$nDelete)));
        $this->Response->redirect($this->Service->constructUrl('key.KeyList',$pBack));
    }


    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('key.KeyList',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('key.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('key.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('key.KeyList',$pBack));
    }

    public function checkKey($sender, $param)
    {
        $id = $sender->Text;
        $this->Response->redirect($this->Service->constructUrl('tool.CheckKey',array('id'=>$id)));
    }
}
?>
