<?php


Prado::using('horux.pages.components.timuxadmin.pi_barcode');

class add extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(isset($this->Request['barcode'])) {
            $objCode = new pi_barcode() ;

            $objCode->setSize(50);
            $objCode->hideCodeType();
            $objCode->setColors('#000000');
            $objCode->setSize(80);

            $param = Prado::getApplication()->getParameters();

            $objCode -> setType($param['barcodetype']) ;
            $objCode -> setCode($this->Request['code']) ;

            $objCode -> setFiletype ('PNG');
            $objCode -> showBarcodeImage();

            exit;
        }
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The time code was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The time code was not added'));
                $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.mod', $pBack));
            }
        }
    }

    protected function genBarCode($sender, $param) {
        $this->barcode->ImageUrl = 'index.php?page='.$this->getApplication()->getService()->getRequestedPagePath().'&barcode=1&code='.$sender->SafeText;
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The time code was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The time code was not added'));
            $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode',$pBack));
        }
    }

    protected function saveData()
    {

        $cmd = $this->db->createCommand( "INSERT `hr_timux_timecode` SET
                                            `name`=:name ,
                                            `abbreviation`=:abbreviation,
                                            `type`=:type,
                                            `useMinMax`=:useMinMax,
                                            `minHour`=:minHour,
                                            `maxHour`=:maxHour,
                                            `defaultHoliday` =:defaultHoliday,
                                            `defaultOvertime` =:defaultOvertime,
                                            `formatDisplay` =:formatDisplay,
                                            `signtype`=:signtype,
                                            `timeworked`=:timeworked,
                                            `deviceDisplay`=:deviceDisplay,
                                            `color`=:color,
                                            `inputDBE`=:inputDBE
                                            ;" );
        
        $cmd->bindValue(":type",$this->type->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":name",$this->name->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":deviceDisplay",$this->deviceDisplay->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":abbreviation",$this->abbreviation->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":color",$this->color->SafeText, PDO::PARAM_STR);

        if($this->type->getSelectedValue() == 'load') {
            $cmd->bindValue(":inputDBE",$this->inputBDE->getSelectedValue(), PDO::PARAM_STR);
        } else {
            $cmd->bindValue(":inputDBE",0, PDO::PARAM_STR);
        }

        $useMinMax = false;
        if($this->useMinMax->getChecked())
            $useMinMax=  true;
        else
        {
           $this->minHour->Text = 0;
           $this->maxHour->Text = 0;
        }

        $timeworked = false;
        if($this->timeworked->getChecked())
            $timeworked = true;
            
        $cmd->bindValue(":timeworked",$timeworked, PDO::PARAM_STR);

        $cmd->bindValue(":useMinMax",$useMinMax, PDO::PARAM_STR);
        $cmd->bindValue(":minHour",$this->minHour->SafeText, PDO::PARAM_STR);
        $cmd->bindValue(":maxHour",$this->maxHour->SafeText, PDO::PARAM_STR);

        $checkO = 0;
        if($this->defaultOvertime->getChecked())
            $checkO = 1;

        $cmd->bindValue(":defaultOvertime",$checkO, PDO::PARAM_STR);

        $checkH = 0;
        if($this->defaultHoliday->getChecked())
            $checkH = 1;
        $cmd->bindValue(":defaultHoliday",$checkH, PDO::PARAM_STR);


        $format = "hour";

        if($this->formatHour->getChecked())
            $format = 1;

        if($this->formatDay->getChecked())
            $format = 2;

        $cmd->bindValue(":formatDisplay",$format, PDO::PARAM_STR);

        if($checkO == 1)
        {
            $cmdU = $this->db->createCommand( "UPDATE `hr_timux_timecode` SET
                                                `defaultOvertime` =0
                                                ;" );
            $cmdU->execute();

        }

        if($checkH == 1)
        {
            $cmdU = $this->db->createCommand( "UPDATE `hr_timux_timecode` SET
                                                `defaultHoliday` =0
                                                ;" );
            $cmdU->execute();

        }

        $cmd->bindValue(":signtype",$this->signtype->getSelectedValue(), PDO::PARAM_STR);


        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        if($this->signtype->getSelectedValue() != 'none')
        {
            $sa = new TStandAlone();
            $sa->addStandalone("add", $lastId, 'timuxAddSubReason');
        }

        return $lastId;
    }


    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('components.timuxadmin.timecode.timecode'));
    }

    public function onTypeChanged($sender, $param)
    {
        $this->inputBDE->setDisplay('None');
        if($this->type->getSelectedValue() == 'overtime')
        {
            $this->useMinMax->setEnabled(true);
            $this->minHour->setEnabled(true);
            $this->maxHour->setEnabled(true);
        }
        else
        {
            $this->useMinMax->setEnabled(false);
            $this->minHour->setEnabled(false);
            $this->maxHour->setEnabled(false);

            $this->useMinMax->setChecked(false);
            $this->minHour->Text = "";
            $this->maxHour->Text = "";

            if($this->type->getSelectedValue() == 'load') {
                $this->inputBDE->setDisplay('Dynamic');
            }
        }
    }

    public function defaultChange($sender, $param)
    {
        if($sender == $this->defaultHoliday)
        {
            if($this->defaultHoliday->getChecked())
                $this->defaultOvertime->setChecked(false);
        }

        if($sender == $this->defaultOvertime)
        {
            if($this->defaultOvertime->getChecked())
                $this->defaultHoliday->setChecked(false);
        }
    }
}
