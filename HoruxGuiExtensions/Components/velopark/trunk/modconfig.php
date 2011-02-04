<?php

class modconfig extends Page {
    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->isPostBack) {
            if(isset($this->Request['id'])) {

                $userId=$this->Application->getUser()->getUserId();
                $this->blockRecord('hr_vp_parking', $this->Request['id'], $userId);

                $this->id->Value = $this->Request['id'];


                $this->accesspoint->DataSource = $this->Accesspoint;
                $this->accesspoint->dataBind();


                $this->setData();
            }
        }
    }

    protected function getAccesspoint() {
        $cmd = $this->db->createCommand( "SELECT id AS Value, name AS Text FROM hr_device WHERE accessPoint=1" );
        $data =  $cmd->query();
        $data = $data->readAll();
        return $data;
    }

    protected function setData() {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_vp_parking WHERE id=:id" );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query) {
            $data = $query->read();
            $this->id->Value = $data['id'];
            $this->name->Text = $data['name'];
            $this->area->Text = $data['area'];
            $this->filling->Text = $data['filling'];
            $this->access_unknown_msg->Text = $data['access_unknown_msg'];
            $this->access_ko_msg->Text = $data['access_ko_msg'];
            $this->access_credit_warning_msg->Text = $data['access_credit_warning_msg'];
            $this->access_warning_msg->Text = $data['access_warning_msg'];
            $this->credit_value->Text = $data['creditValue'];

            $items=$this->accesspoint->getItems();

            $deviceIds = explode(",", $data['device_ids']);

            foreach($items as $item)
            {

                if(in_array($item->Value,$deviceIds) ) {
                    $item->setSelected(true);
                }
                else {
                    $item->setSelected(false);
                }
            }
        }
    }

    public function onApply($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The service was modified successfully'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.velopark.modconfig', $pBack));
            }
            else {
                $pBack = array('koMsg'=>Prado::localize('The service was not modified'), 'id'=>$this->id->Value);
                $this->Response->redirect($this->Service->constructUrl('components.velopark.modconfig', $pBack));
            }
        }
    }

    public function onSave($sender, $param) {
        if($this->Page->IsValid) {
            if($this->saveData()) {
                $pBack = array('okMsg'=>Prado::localize('The service was modified successfully'));
            }
            else
                $pBack = array('koMsg'=>Prado::localize('The service was not modified'));

            $this->blockRecord('hr_vp_parking', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('components.velopark.config',$pBack));
        }
    }

    public function onCancel($sender, $param) {
        $this->blockRecord('hr_vp_parking', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('components.velopark.config'));
    }


    protected function saveData() {
        $cmd = $this->db->createCommand( "UPDATE hr_vp_parking SET
                                            `name` = :name,
                                            `area` = :area,
                                            `filling` = :filling,
                                            `access_unknown_msg`=:access_unknown_msg,
                                            `access_ko_msg`=:access_ko_msg,
                                            `device_ids`=:device_ids,
                                            `access_credit_warning_msg`=:access_credit_warning_msg,
                                            `access_warning_msg`=:access_warning_msg,
                                            `creditValue`:=:creditValue
                                            WHERE id =:id" );

        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":area",$this->area->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":filling",$this->filling->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_STR);
        $cmd->bindValue(":access_unknown_msg",$this->access_unknown_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":access_ko_msg",$this->access_ko_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":access_credit_warning_msg",$this->access_credit_warning_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":access_warning_msg",$this->access_warning_msg->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":creditValue",$this->credit_value->SafeText,PDO::PARAM_STR);

        $indices=$this->accesspoint->SelectedIndices;
        $result=array();
        foreach($indices as $index)
        {
            $item = $this->accesspoint->Items[$index];
            $result[] = $item->Value;
        }

        $cmd->bindValue(":device_ids",implode(',',$result),PDO::PARAM_STR);

        return $cmd->execute();
    }
} 
