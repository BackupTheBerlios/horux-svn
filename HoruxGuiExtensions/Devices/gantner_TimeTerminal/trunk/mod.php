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

Prado::using('horux.pages.hardware.device.gantner_TimeTerminal.sql');

class mod extends Page {

    protected $inputData;

    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {

            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_device', $this->Request['id'], $userId);

            $this->brightness->setDataValueField('value');
            $this->brightness->setDataTextField('text');
            $this->brightness->DataSource=$this->Brightness;
            $this->brightness->dataBind();


            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0) {
                $this->tbb->Save->setEnabled(false);
                $this->tbb->apply->setEnabled(false);
            }

            $this->id->Value = $this->Request['id'];
            $this->setData();

        }
    }


    protected function getBrightness() {
        $v = array();
        for($i=0; $i<=100; $i++) {
            $v[] = array('value'=>$i, 'text'=>$i);
        }

        return $v;
    }

    protected function setData() {
        $cmd = $this->db->createCommand( SQL::SQL_GET_GANTNERTERMINAL );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();

        if($query) {
            $data = $query->read();
            $this->name->Text = $data['name'];
            $this->comment->Text = $data['description'];
            $this->ipOrDhcp->Text = $data['ipOrDhcp'];

            $this->isAutoRestart->setChecked($data['isAutoRestart']);
            $time = explode(":", $data['autoRestart']);
            $this->autoRestartHour->Text = $time[0];
            $this->autoRestartMinute->Text = $time[1];
            $this->displayTimeout->Text = $data['displayTimeout'];
            $this->inputTimeout->Text = $data['inputTimeout'];
            $this->inputTimeout->Text = $data['inputTimeout'];
            $this->brightness->setSelectedValue($data['brightness']);
            $this->udpServer->setChecked($data['udpServer']);
            $this->autoBooking->setChecked($data['autoBooking']);
            $this->checkBooking->Text = $data['checkBooking'];
            $this->isLog->setChecked($data['isLog'] );

            $languages = explode(",", $data['language']);

            if(count($languages)>0) {
                foreach($languages as $l) {
                    if($l != '') {
                        $this->$l->setChecked(true);
                    }
                }
            }

            $this->inputDBEText1->Text = $data['inputDBEText1'];
            $this->inputDBEText2->Text = $data['inputDBEText2'];
            $this->inputDBEText3->Text = $data['inputDBEText3'];
            $this->inputDBEText4->Text = $data['inputDBEText4'];
            $this->inputDBEText5->Text = $data['inputDBEText5'];
            $this->inputDBEText6->Text = $data['inputDBEText6'];
            $this->inputDBEText7->Text = $data['inputDBEText7'];
            $this->inputDBEText8->Text = $data['inputDBEText8'];
            $this->inputDBEText9->Text = $data['inputDBEText9'];
            $this->inputDBEText10->Text = $data['inputDBEText10'];
            $this->inputDBEText11->Text = $data['inputDBEText11'];
            $this->inputDBEText12->Text = $data['inputDBEText12'];
            $this->inputDBEText13->Text = $data['inputDBEText13'];
            $this->inputDBEText14->Text = $data['inputDBEText14'];
            $this->inputDBEText15->Text = $data['inputDBEText15'];
            $this->inputDBEText16->Text = $data['inputDBEText16'];
            $this->inputDBEText17->Text = $data['inputDBEText17'];
            $this->inputDBEText18->Text = $data['inputDBEText18'];
            $this->inputDBEText19->Text = $data['inputDBEText19'];
            $this->inputDBEText20->Text = $data['inputDBEText20'];

            $this->inputDBECheck1->setChecked( $data['inputDBECheck1'] );
            $this->inputDBECheck2->setChecked( $data['inputDBECheck2'] );
            $this->inputDBECheck3->setChecked( $data['inputDBECheck3'] );
            $this->inputDBECheck4->setChecked( $data['inputDBECheck4'] );
            $this->inputDBECheck5->setChecked( $data['inputDBECheck5'] );
            $this->inputDBECheck6->setChecked( $data['inputDBECheck6'] );
            $this->inputDBECheck7->setChecked( $data['inputDBECheck7'] );
            $this->inputDBECheck8->setChecked( $data['inputDBEChecky8'] );
            $this->inputDBECheck9->setChecked( $data['inputDBECheck9'] );
            $this->inputDBECheck10->setChecked( $data['inputDBECheck10'] );
            $this->inputDBECheck11->setChecked( $data['inputDBECheck11'] );
            $this->inputDBECheck12->setChecked( $data['inputDBECheck12'] );
            $this->inputDBECheck13->setChecked( $data['inputDBECheck13'] );
            $this->inputDBECheck14->setChecked( $data['inputDBECheck14'] );
            $this->inputDBECheck15->setChecked( $data['inputDBECheck15'] );
            $this->inputDBECheck16->setChecked( $data['inputDBECheck16'] );
            $this->inputDBECheck17->setChecked( $data['inputDBECheck17'] );
            $this->inputDBECheck18->setChecked( $data['inputDBECheck18'] );
            $this->inputDBECheck19->setChecked( $data['inputDBECheck19'] );
            $this->inputDBECheck20->setChecked( $data['inputDBECheck20'] );

            $this->inputDBEDisplay1->Text =  $data['inputDBEFormat1'] ;
            $this->inputDBEDisplay2->Text =  $data['inputDBEFormat2'] ;
            $this->inputDBEDisplay3->Text =  $data['inputDBEFormat3'] ;
            $this->inputDBEDisplay4->Text =  $data['inputDBEFormat4'] ;
            $this->inputDBEDisplay5->Text =  $data['inputDBEFormat5'] ;
            $this->inputDBEDisplay6->Text =  $data['inputDBEFormat6'] ;
            $this->inputDBEDisplay7->Text =  $data['inputDBEFormat7'] ;
            $this->inputDBEDisplay8->Text =  $data['inputDBEFormat8'] ;
            $this->inputDBEDisplay9->Text =  $data['inputDBEFormat9'] ;
            $this->inputDBEDisplay10->Text =  $data['inputDBEFormat10'] ;
            $this->inputDBEDisplay11->Text =  $data['inputDBEFormat11'] ;
            $this->inputDBEDisplay12->Text =  $data['inputDBEFormat12'] ;
            $this->inputDBEDisplay13->Text =  $data['inputDBEFormat13'] ;
            $this->inputDBEDisplay14->Text =  $data['inputDBEFormat14'] ;
            $this->inputDBEDisplay15->Text =  $data['inputDBEFormat15'] ;
            $this->inputDBEDisplay16->Text =  $data['inputDBEFormat16'] ;
            $this->inputDBEDisplay17->Text =  $data['inputDBEFormat17'] ;
            $this->inputDBEDisplay18->Text =  $data['inputDBEFormat18'] ;
            $this->inputDBEDisplay19->Text =  $data['inputDBEFormat19'] ;
            $this->inputDBEDisplay20->Text =  $data['inputDBEFormat20'] ;

        }

        $cmd = $this->db->createCommand( SQL::SQL_GET_KEY );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query) {
            $data = $query->readAll();

            foreach($data as $d) {
                if($d['type'] == 'fixed') {
                    switch($d['key']) {
                        case 1:
                            $this->inputData['fixed'][1] = $d['params'];

                            $this->leftFixed->Text = $d['text'];
                            $this->leftFixedDlg->setSelectedValue($d['dialog']);

                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->leftFixedDlgLink->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 2:

                            $this->inputData['fixed'][2] = $d['params'];

                            $this->leftMiddleFixed->Text = $d['text'];
                            $this->leftMiddleFixedDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->leftMiddleFixedDlgLink->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 3:
                            $this->inputData['fixed'][3] = $d['params'];


                            $this->rightMiddleFixed->Text = $d['text'];
                            $this->rightMiddleFixedDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->rightMiddleFixedDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 4:
                            $this->inputData['fixed'][4] = $d['params'];

                            $this->rightFixed->Text = $d['text'];
                            $this->rightFixedDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->rightFixedDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                    }
                }
                elseif($d['type'] == 'soft') {
                    switch($d['key']) {
                        case 1:
                            $this->inputData['soft'][1] = $d['params'];

                            $this->leftTopSoft->Text = $d['text'];
                            $this->leftTopSoftDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->leftTopSoftDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 2:
                            $this->inputData['soft'][2] = $d['params'];

                            $this->leftMiddleSoft->Text = $d['text'];
                            $this->leftMiddleSoftDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->leftMiddleSoftDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 3:
                            $this->inputData['soft'][3] = $d['params'];

                            $this->leftBottomSoft->Text = $d['text'];
                            $this->leftBottomSoftDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->leftBottomSoftDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 4:
                            $this->inputData['soft'][4] = $d['params'];

                            $this->rightTopSoft->Text = $d['text'];
                            $this->rightTopSoftDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->rightTopSoftDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 5:
                            $this->inputData['soft'][5] = $d['params'];

                            $this->rightMiddleSoft->Text = $d['text'];
                            $this->rightMiddleSoftDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->rightMiddleSoftDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                        case 6:
                            $this->inputData['soft'][6] = $d['params'];

                            $this->rightBottomSoft->Text = $d['text'];
                            $this->rightBottomSoftDlg->setSelectedValue($d['dialog']);
                            if($d['dialog'] == '<dlg_InputData,150>' || $d['dialog'] == '<dlg_InputData,155>' ) {
                                $this->rightBottomSoftDlg->setDisplay(TDisplayStyle::Dynamic);
                            }
                            break;
                    }

                }
            }
        }


        $this->setViewState('inputData',$this->inputData);
    }

    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $id = $this->id->Value;

                $horuxService = new THoruxService();
                $horuxService->onStopDevice($id);
                $horuxService->onStartDevice($id);

                $sa = new TStandAlone();
                $sa->addStandalone("add", $this->id->Value, 'timuxReinit');


                $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('hardware.device.gantner_TimeTerminal.mod', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The device was not modified'));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The device was modified successfully'));
                $horuxService = new THoruxService();
                $horuxService->onStopDevice($this->id->Value);
                $horuxService->onStartDevice($this->id->Value);

                $sa = new TStandAlone();
                $sa->addStandalone("add", $this->id->Value, 'timuxReinit');


            }
            else
                $pBack = array('koMsg'=>Prado::localize('The device was not modified'));

            $this->blockRecord('hr_device', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList',$pBack));
        }
    }

    public function onCancel($sender, $param) {
        $this->blockRecord('hr_device', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('hardware.HardwareList'));
    }

    protected function saveData() {
        $cmd = $this->db->createCommand( SQL::SQL_MOD_DEVICE );
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":description",$this->comment->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":isLog",$this->isLog->getChecked(),PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->Execute();


        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_GANTNERTERMINAL );
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


        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);

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

        $cmd = $this->db->createCommand( SQL::SQL_REMOVE_KEY );
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $cmd->Execute();


        $inputData = $this->getViewState('inputData');

        $type ='fixed';
        $key = 1;
        if($this->leftFixed->SafeText != '' && $this->leftFixedDlg->getSelectedValue() != '') {
            $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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
            $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
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

        return true;
    }

    public function serverValidateName($sender, $param) {
        $cmd = $this->db->createCommand( SQL::SQL_IS_READER_NAME_EXIST2);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
            $param->IsValid=false;
        else
            $param->IsValid=true;
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
    }
}
