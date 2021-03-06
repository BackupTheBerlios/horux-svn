<?php

Prado::using('horux.pages.components.timuxadmin.pi_barcode');


class timecode extends PageList
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!is_writeable('./tmp'))
        {
            $this->displayMessage(Prado::localize('The directory ./tmp must be writeable to install an extension'), false);
        }

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
    }

    protected function onPrint()
    {
        $param = Prado::getApplication()->getParameters();

        $nbreOfColumn = $param['barcodeprintcolumn'];
        $orderOfColumn = $param['orderprintcolumn'];

        parent::onPrint();
        $this->pdf->AddPage();
        $type = $this->Request['f1'];   //type

        $typeText = '';

        switch($type) {
            case '0':
                break;
            case 'leave' :
                $typeText = " - ".Prado::localize("Leave");
                break;
            case 'absence':
                $typeText = " - ".Prado::localize("Absence");
                break;
            case 'overtime':
                $typeText = " - ".Prado::localize("Overtime");
                break;
            case 'load':
                $typeText = " - ".Prado::localize("Load");
                break;
        }

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of time code').$typeText),0,0,'L');
        $this->pdf->Ln(10);

        $this->pdf->setDefaultFont();


        if($type == '0') {
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode ORDER BY $orderOfColumn");
        }
        else {
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode WHERE type=:type ORDER BY $orderOfColumn");
            $cmd->bindValue(":type", $type);
        }
        $data = $cmd->query();
        $timeCode = $data->readAll();

        while(count($timeCode) % $nbreOfColumn > 0) {
            $timeCode[] = array('name'=>'', 'abbreviation'=>'' );
        }

        $i=0;
        $j=0;
        $x=10;
        $y=38;
        $width = 190 / $nbreOfColumn;

        foreach($timeCode as $tc) {

            $this->pdf->Cell($width,6,utf8_decode($tc['name']),'LTR');

            $objCode = new pi_barcode() ;

            $objCode->setSize(20);
            $objCode->hideCodeType();
            $objCode->setColors('#000000');
            $objCode->setSize(30);

            $param = Prado::getApplication()->getParameters();

            if($tc['abbreviation'] != '') {
                $objCode -> setType($param['barcodetype']) ;
                $objCode -> setCode($tc['abbreviation']) ;

                $objCode -> setFiletype ('PNG');

                $objCode -> writeBarcodeFile('./tmp/bctc'.$i.'.png') ;

                $this->pdf->Image('./tmp/bctc'.$i.'.png',$x,$y);

                $x += 93;
            }
            $i++;
            if($i%$nbreOfColumn == 0) {
                $this->pdf->Ln();

                for($c=0; $c<$nbreOfColumn; $c++)
                    $this->pdf->Cell($width,20,'','LRB');

                $j++;

                if($j%9 == 0) {
                    $this->pdf->AddPage();
                    $x = 15;
                    $y = 38;
                    $this->pdf->SetFont('Arial','',11);
                    $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of time code')),0,0,'L');
                    $this->pdf->Ln(10);

                    $this->pdf->setDefaultFont();
                } else {
                    $this->pdf->Ln();
                    $y += 26;
                    $x=10;
                }
            }

        }      

        $this->pdf->render();

        $i = 0;
    }

    public function getData()
    {
        $type = $this->filterType->getSelectedValue();

        if($type === "0") {
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode ORDER BY id");
        } else {
            $cmd=$this->db->createCommand("SELECT * FROM hr_timux_timecode WHERE type='$type' ORDER BY id");
        }
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }



    public function itemCreated($sender, $param)
    {
        $item=$param->Item;


        if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
        {

            if($item->DataItem['timeworked'] == 1)
                $item->ttimeworked->timeworked->Text = Prado::localize('Yes');
            else
                $item->ttimeworked->timeworked->Text = Prado::localize('No');

            switch($item->DataItem['type'])
            {
                case 'leave':
                    $item->ttype->type->Text = Prado::localize('Leave');
                    break;
                case 'absence':
                    $item->ttype->type->Text = Prado::localize('Absence');
                    break;
                case 'overtime':
                    $item->ttype->type->Text = Prado::localize('Overtime');
                    break;
                case 'load':
                    $item->ttype->type->Text = Prado::localize('Load');
                    break;
            }

            switch($item->DataItem['signtype'])
            {
                case 'none':
                    $item->ssign->sign->Text = Prado::localize("No signing");
                    break;
                case 'in':
                    $item->ssign->sign->Text = Prado::localize("Signing in only");
                    break;
                case 'out':
                    $item->ssign->sign->Text = Prado::localize("Signing out only");
                    break;
                case 'both':
                    $item->ssign->sign->Text = Prado::localize("Signing in/out");
                    break;
            }
        }
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.panel'));
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
                    $sa = new TStandAlone();
                    $sa->addStandalone("sub", $cb->Value, 'timuxAddSubReason');
                    
                    $cmd=$this->db->createCommand("DELETE FROM hr_timux_timecode WHERE id=:id");
                    $cmd->bindValue(":id",$cb->Value);
                    if($cmd->execute())
                    {
                        $nDelete++;
                    }
                    //$this->log("Delete the key: ".$data['serialNumber']);



                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} time code was deleted',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));
    }

    public function onEdit($sender,$param)
    {
        if(count($this->DataGrid->DataKeys) === 0)
        {
            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));

        }

        $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];
        if(is_numeric($id))
        {
            $pBack = array('id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod',$pBack));
        }

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nDelete = 0;
        foreach($cbs as $cb)
        {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
            {
                $pBack = array('id'=>$cb->Value);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod',$pBack));
            }
        }

        $pBack = array('koMsg'=>Prado::localize('Select one item'));
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));
    }

    public function onTypeChanged($sender, $param) {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
    }
}

?>