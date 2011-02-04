<?php


Prado::using('horux.pages.hardware.device.gantner_TimeTerminal.sql');

class add extends AddDevicePage {

    public function onLoad($param) {
        $this->deviceName = "gantner_TimeTerminal";

        parent::onLoad($param);

        if(!$this->IsPostBack) {
            $this->brightness->setDataValueField('value');
            $this->brightness->setDataTextField('text');
            $this->brightness->DataSource=$this->Brightness;
            $this->brightness->dataBind();
            $this->brightness->setSelectedValue(50);
        }
    }

    protected function getBrightness() {
        $v = array();
        for($i=0; $i<=100; $i++) {
            $v[] = array('value'=>$i, 'text'=>$i);
        }

        return $v;
    }

    
    public function saveData() {

        parent::saveData();

        if($this->lastId !== false) {

            $cmd = $this->db->createCommand( SQL::SQL_ADD_GANTNERTERMINAL );

            $cmd->bindValue(":id_device",$this->lastId,PDO::PARAM_STR);
            $cmd->bindValue(":ipOrDhcp",$this->ipOrDhcp->SafeText,PDO::PARAM_STR);

            $isAutoRestart = $this->isAutoRestart->getChecked();
            $cmd->bindValue(":isAutoRestart",$isAutoRestart,PDO::PARAM_STR);

            $autoRestart = $this->autoRestartHour->SafeText.":".$this->autoRestartMinute->SafeText.":00";
            $cmd->bindValue(":autoRestart",$autoRestart,PDO::PARAM_STR);

            $cmd->bindValue(":displayTimeout",$this->displayTimeout->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputTimeout",$this->inputTimeout->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":brightness",$this->brightness->getSelectedValue(),PDO::PARAM_STR);

            $udpServer = $this->udpServer->getChecked();
            $cmd->bindValue(":udpServer",$udpServer,PDO::PARAM_STR);

            $cmd->bindValue(":autoBooking",$this->autoBooking->getChecked(),PDO::PARAM_STR);


            $cmd->bindValue(":checkBooking",$this->checkBooking->SafeText,PDO::PARAM_STR);

            $language = array();

            if($this->ar->getChecked())
                $language[] = 'ar';
            if($this->de->getChecked())
                $language[] = 'de';
            if($this->en->getChecked())
                $language[] = 'en';
            if($this->fr->getChecked())
                $language[] =  'fr';
            if($this->it->getChecked())
                $language[] = 'it';
            if($this->fa->getChecked())
                $language[] = 'fa';
            if($this->pl->getChecked())
                $language[] = 'pl';
            if($this->ro->getChecked())
                $language[] = 'ro';
            if($this->es->getChecked())
                $language[] = 'es';
            if($this->cs->getChecked())
                $language[] = 'cs';

            $language = implode(',' ,$language);

            $cmd->bindValue(":language",$language,PDO::PARAM_STR);


            $cmd->bindValue(":inputDBECheck1",$this->inputDBECheck1->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck2",$this->inputDBECheck2->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck3",$this->inputDBECheck3->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck4",$this->inputDBECheck4->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck5",$this->inputDBECheck5->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck6",$this->inputDBECheck6->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck7",$this->inputDBECheck7->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck8",$this->inputDBECheck8->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck9",$this->inputDBECheck9->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck10",$this->inputDBECheck10->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck11",$this->inputDBECheck11->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck12",$this->inputDBECheck12->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck13",$this->inputDBECheck13->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck14",$this->inputDBECheck14->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck15",$this->inputDBECheck15->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck16",$this->inputDBECheck16->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck17",$this->inputDBECheck17->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck18",$this->inputDBECheck18->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck19",$this->inputDBECheck19->getChecked(),PDO::PARAM_STR);
            $cmd->bindValue(":inputDBECheck20",$this->inputDBECheck20->getChecked(),PDO::PARAM_STR);

            $cmd->bindValue(":inputDBEText1",$this->inputDBEText1->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText2",$this->inputDBEText2->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText3",$this->inputDBEText3->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText4",$this->inputDBEText4->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText5",$this->inputDBEText5->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText6",$this->inputDBEText6->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText7",$this->inputDBEText7->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText8",$this->inputDBEText8->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText9",$this->inputDBEText9->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText10",$this->inputDBEText10->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText11",$this->inputDBEText11->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText12",$this->inputDBEText12->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText13",$this->inputDBEText13->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText14",$this->inputDBEText14->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText15",$this->inputDBEText15->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText16",$this->inputDBEText16->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText17",$this->inputDBEText17->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText18",$this->inputDBEText18->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText19",$this->inputDBEText19->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEText20",$this->inputDBEText20->SafeText,PDO::PARAM_STR);

            $cmd->bindValue(":inputDBEFormat1",$this->inputDBEDisplay1->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat2",$this->inputDBEDisplay2->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat3",$this->inputDBEDisplay3->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat4",$this->inputDBEDisplay4->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat5",$this->inputDBEDisplay5->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat6",$this->inputDBEDisplay6->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat7",$this->inputDBEDisplay7->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat8",$this->inputDBEDisplay8->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat9",$this->inputDBEDisplay9->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat10",$this->inputDBEDisplay10->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat11",$this->inputDBEDisplay11->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat12",$this->inputDBEDisplay12->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat13",$this->inputDBEDisplay13->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat14",$this->inputDBEDisplay14->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat15",$this->inputDBEDisplay15->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat16",$this->inputDBEDisplay16->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat17",$this->inputDBEDisplay17->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat18",$this->inputDBEDisplay18->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat19",$this->inputDBEDisplay19->SafeText,PDO::PARAM_STR);
            $cmd->bindValue(":inputDBEFormat20",$this->inputDBEDisplay20->SafeText,PDO::PARAM_STR);

            $cmd->Execute();

            $inputData = $this->getViewState('inputData');


            $type ='fixed';
            $key = 1;
            if($this->leftFixed->SafeText != '' && $this->leftFixedDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->leftFixed->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->leftFixedDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();
            }
            $key = 2;
            if($this->leftMiddleFixed->SafeText != '' && $this->leftMiddleFixedDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->leftMiddleFixed->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->leftMiddleFixedDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();
            }
            $key = 3;
            if($this->rightMiddleFixed->SafeText != '' && $this->rightMiddleFixedDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->rightMiddleFixed->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->rightMiddleFixedDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();

            }
            $key = 4;
            if($this->rightFixed->SafeText != '' && $this->rightFixedDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->rightFixed->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->rightFixedDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();
            }


            $type ='soft';
            $key = 1;
            if($this->leftTopSoft->SafeText != '' && $this->leftTopSoftDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->leftTopSoft->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->leftTopSoftDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();

            }
            $key = 2;
            if($this->leftMiddleSoft->SafeText != '' && $this->leftMiddleSoftDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->leftMiddleSoft->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->leftMiddleSoftDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();

            }
            $key = 3;
            if($this->leftBottomSoft->SafeText != '' && $this->leftBottomSoftDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->leftBottomSoft->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->leftBottomSoftDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();

            }
            $key = 4;
            if($this->rightTopSoft->SafeText != '' && $this->rightTopSoftDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->rightTopSoft->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->rightTopSoftDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();

            }
            $key = 5;
            if($this->rightMiddleSoft->SafeText != '' && $this->rightMiddleSoftDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->rightMiddleSoft->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->rightMiddleSoftDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();

            }
            $key = 6;
            if($this->rightBottomSoft->SafeText != '' && $this->rightBottomSoftDlg->getSelectedValue() != '') {
                $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
                $cmd->bindValue(":id",$this->lastId,PDO::PARAM_STR);
                $cmd->bindValue(":type",$type,PDO::PARAM_STR);
                $cmd->bindValue(":key",$key,PDO::PARAM_STR);
                $cmd->bindValue(":text",$this->rightBottomSoft->SafeText,PDO::PARAM_STR);
                $cmd->bindValue(":dialog",$this->rightBottomSoftDlg->getSelectedValue(),PDO::PARAM_STR);
                if(isset($inputData[$type][$key])) {
                    $cmd->bindValue(":params",$inputData[$type][$key],PDO::PARAM_STR);

                } else {
                    $cmd->bindValue(":params",'',PDO::PARAM_STR);
                }
                $cmd->Execute();

            }

            $horuxService = new THoruxService();
            $horuxService->onStopDevice($id);
            $horuxService->onStartDevice($id);
            $sa = new TStandAlone();
            $sa->addStandalone("add", $this->lastId, 'timuxReinit');


            return true;
        } else {
            return false;
        }
    }



    public function buttonOptionChange($sender, $param) {
        if($sender->getSelectedValue() == '<dlg_InputData,150>' || $sender->getSelectedValue() == '<dlg_InputData,155>') {
            $lw = $sender->getID()."Link";
            $this->$lw->setDisplay(TDisplayStyle::Dynamic);
        } else {
            $lw = $sender->getID()."Link";
            $this->$lw->setDisplay(TDisplayStyle::None);
        }
    }

    public function inputOption_Requested($sender,$param) {

        $inputData = $this->getViewState('inputData');

        $control = $param->CallbackParameter->TControl;

        switch($control) {
            case 'leftFixedDlg':
                $key = 1;
                $type = 'fixed';
                break;
            case 'leftMiddleFixedDlg':
                $key = 2;
                $type = 'fixed';
                break;
            case 'rightMiddleFixedDlg':
                $key = 3;
                $type = 'fixed';
                break;
            case 'rightFixedDlg':
                $key = 4;
                $type = 'fixed';
                break;
            case 'leftTopSoftDlg':
                $key = 1;
                $type = 'soft';
                break;
            case 'leftMiddleSoftDlg':
                $key = 2;
                $type = 'soft';
                break;
            case 'rightTopSoftDlg':
                $key = 3;
                $type = 'soft';
                break;
            case '':
                $key = 4;
                $type = 'soft';
                break;
            case 'rightMiddleSoftDlg':
                $key = 5;
                $type = 'soft';
                break;
            case 'rightBottomSoftDlg':
                $key = 6;
                $type = 'soft';
                break;
        }

        $this->setViewState('currentInputData', array('key'=>$key,'type'=>$type));


        for($i=1;$i<=20;$i++) {
            $cb = 'cb'.$i;
            $this->$cb->setChecked(false);
        }

        if(isset($inputData[$type][$key])) {
            $value = explode(",", $inputData[$type][$key]);

            if(count($value) > 0) {
                foreach($value as $v) {
                   $cb = 'cb'.$v;
                   if($v>=1 && $v <= 20)
                        $this->$cb->setChecked(true);
                }
            }
        }
    }

    public function checkBoxChange($sender, $param) {
        $dlg = $this->getViewState('currentInputData');

        $key = $dlg['key'];
        $type = $dlg['type'];
        $inputData = $this->getViewState('inputData');

        $value = array();
        for($i=1;$i<=20;$i++) {
            $cb = 'cb'.$i;
            if($this->$cb->getChecked()) {
               $value[] = $i;
            }
        }

        $value = implode(",", $value);

        $inputData[$type][$key] = $value;

        $this->setViewState('inputData',$inputData);
    }}
