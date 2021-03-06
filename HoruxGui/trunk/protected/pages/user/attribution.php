<?php


Prado::using('horux.pages.user.sql');

class Attribution extends Page {

    public $cards_format;

    // Input: A decimal number as a String.
    // Output: The equivalent hexadecimal number as a String.
    public function dec2hex($number)
    {
        $hexvalues = array('0','1','2','3','4','5','6','7',
                   '8','9','A','B','C','D','E','F');
        $hexval = '';
         while($number != '0')
         {
            $hexval = $hexvalues[bcmod($number,'16')].$hexval;
            $number = bcdiv($number,'16',0);
        }
        return $hexval;
    }

    // Input: A hexadecimal number as a String.
    // Output: The equivalent decimal number as a String.
    public function hex2dec($number)
    {
        $decvalues = array('0' => '0', '1' => '1', '2' => '2',
                   '3' => '3', '4' => '4', '5' => '5',
                   '6' => '6', '7' => '7', '8' => '8',
                   '9' => '9', 'A' => '10', 'B' => '11',
                   'C' => '12', 'D' => '13', 'E' => '14',
                   'F' => '15');
        $decval = '0';
        $number = strrev($number);
        for($i = 0; $i < strlen($number); $i++)
        {
            $decval = bcadd(bcmul(bcpow('16',$i,0),$decvalues[$number{$i}]), $decval);
        }
        return round($decval);
    }

    public function getData() {
        $id = $this->Request['id'];
        $cmd = $this->db->createCommand( SQL::SQL_GET_KEY );
        $cmd->bindValue(":id",$id,PDO::PARAM_INT);
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function getKey() {
        $cmd = $this->db->createCommand( SQL::SQL_GET_UNATTRIBUTED_KEY );
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function onLoad($param) {
        parent::onLoad($param);

        // get the cards format...
        $sql = "SELECT cards_format FROM hr_config WHERE id=1";
        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();
        $sn = $this->Request['sn'];

        if($data['cards_format'] != "") {
          $this->cards_format = $format = $data['cards_format'];
        }

        if(isset($this->Request['sn'])) {
            // ----- get the sn in the desired format -----
            $strHexSn = $this->dec2hex($sn);
            $data = $strHexSn;
            $dataSize = strlen($format);

            $ret = "";
            if ($format == "")
                $ret = $sn;
            else {
                if (strpos($format, 'X') !== false || strpos($format, 'D') !== false) {
                    for ($i = 0; $i < $dataSize; $i++) {
                        if ($format[$i] != '_') {
                            $ret .= $data[$i*2] . $data[($i*2)+1];
                        }
                    }
                }
                else {
                    for ($i = $dataSize-1; $i > -1; $i--) {
                        if ($format[dataSize-1-$i] != '_')
                            $ret .= $data[$i*2] . $data[($i*2)+1];
                    }
                }

                if (strpos($format, 'D') !== false || strpos($format, 'd') !== false) {
                    $ret = $this->hex2dec($ret);
                }
            }
            $sn = $ret;
            // --------------------------------------------


            $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=0" );
            $cmd->bindValue(":sn",$this->Request['sn'],PDO::PARAM_STR);
            $data=$cmd->query();
            $data = $data->read();
            if($data) {
                $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                $cmd->bindValue(":id_user", $this->Request['id']);
                $cmd->bindValue(":id_key",$data['id']);
                $cmd->execute();

                $cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
                $cmd->bindValue(":id",$data['id']);
                $flag = 1;
                $cmd->bindValue(":flag",$flag);
                $cmd->execute();

                $this->addStandalone('add', $data['id']);

                $this->Response->redirect($this->Service->constructUrl('user.attribution',array('id'=>$this->Request['id'])));

            }
            else {
                $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=1" );
                $cmd->bindValue(":sn",$this->Request['sn'],PDO::PARAM_STR);
                $data=$cmd->query();
                $data = $data->read();
                if($data) {
                    $this->displayMessage(Prado::localize("The key is already attributed"), false);
                }
                else {
                    $cmd = NULL;
                    //! add the new key in the database
                    if($this->db->DriverName == 'sqlite')
                        $cmd=$this->db->createCommand(SQL::SQL_ADD_KEY_SQLITE);
                    else
                        $cmd=$this->db->createCommand(SQL::SQL_ADD_KEY);
                    $cmd->bindValue(":identificator",$sn);
                    $cmd->bindValue(":serialNumber",$this->Request['sn']);
                    $cmd->execute();
                    //! attribute the new key

                    $lastId = $this->db->LastInsertID;

                    $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                    $cmd->bindValue(":id_user", $this->Request['id']);
                    $cmd->bindValue(":id_key",$lastId);
                    $cmd->execute();

                    $this->addStandalone('add', $lastId);

                    $this->Response->redirect($this->Service->constructUrl('user.attribution',array('id'=>$this->Request['id'])));

                }

            }
        }


        $this->setHoruxSysTray(true);

        if(!$this->IsPostBack) {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

            $this->id->Value = $this->Request['id'];

            $this->UnusedKey->DataSource=$this->Key;
            $this->UnusedKey->dataBind();
            if($this->UnusedKey->getItemCount())
                $this->UnusedKey->setSelectedIndex(0);

        }


        if(isset($this->Request['okMsg'])) {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg'])) {
            $this->displayMessage($this->Request['koMsg'], false);
        }
    }

    public function onAttribute($sender,$param) {
        $id_user = $this->id->Value;
        $id_key = $this->UnusedKey->getSelectedValue();

        $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
        $cmd->bindValue(":id_user",$id_user);
        $cmd->bindValue(":id_key",$id_key);
        $cmd->execute();

        $cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
        $cmd->bindValue(":id",$id_key);
        $flag = 1;
        $cmd->bindValue(":flag",$flag);
        $cmd->execute();

        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
        $cmd->bindValue(":id",$id_user);
        $cmd = $cmd->query();
        $data = $cmd->read();

        if(!$data['isBlocked']) {
            $this->addStandalone('add', $id_key);
        }

        $cmd=$this->db->createCommand(SQL::SQL_GET_KEY2);
        $cmd->bindValue(":id",$id_key);
        $cmd = $cmd->query();
        $data2 = $cmd->read();

        $this->log("Attribute the key ".$data2['identificator']." to ".$data['name']." ".$data['firstname']);


        $this->Response->redirect($this->Service->constructUrl('user.attribution',array('id'=>$id_user)));
    }

    public function setBlocked($sender,$param) {
        $id = $sender->Text;
        $cmd=$this->db->createCommand(SQL::SQL_UPDATE_SETBLOCK_KEY);
        $cmd->bindValue(":id",$id);
        $func = "";
        if($sender->ImageUrl == "./themes/letux/images/menu/icon-16-checkin.png") {
            $flag = 1;
            $sender->ImageUrl = "./themes/letux/images/menu/icon-16-access.png";
            $cmd->bindValue(":flag",$flag);
            $func = 'block';

            $cmd2=$this->db->createCommand(SQL::SQL_GET_KEY2);
            $cmd2->bindValue(":id",$id);
            $cmd2 = $cmd2->query();
            $data2 = $cmd2->read();

            $this->log("Block the key ".$data2['identificator']);

        }
        else {
            $flag = 0;
            $sender->ImageUrl = "./themes/letux/images/menu/icon-16-checkin.png";
            $cmd->bindValue(":flag",$flag);
            $func = 'unblock';

            $cmd2=$this->db->createCommand(SQL::SQL_GET_KEY2);
            $cmd2->bindValue(":id",$id);
            $cmd2 = $cmd2->query();
            $data2 = $cmd2->read();

            $this->log("Unblock the key ".$data2['identificator']);

        }
        $cmd->execute();

        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
        $cmd->bindValue(":id",$this->id->Value);
        $cmd = $cmd->query();
        $data = $cmd->read();

        $this->addStandalone($func, $id);

        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);
    }

    protected function addStandalone($function, $idkey) {

        $sa = new TStandAlone();
        $sa->addStandalone($function, $idkey, 'UserAttributionKey');
    }

    public function checkboxAllCallback($sender, $param) {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $isChecked = $sender->getChecked();

        foreach($cbs as $cb) {
            $cb->setChecked($isChecked);
        }

    }

    public function onUnattribute($sender, $param) {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $nUnAttributed = 0;
        $koMsg = '';
        $cbChecked = 0;

        foreach($cbs as $cb) {
            if( (bool)$cb->getChecked() && $cb->Value != "0")
                $cbChecked++;
        }

        if($cbChecked==0) {
            $koMsg = Prado::localize('Select one item');
        }
        else {
            foreach($cbs as $cb) {
                if( (bool)$cb->getChecked() && $cb->Value != "0") {
                    $this->addStandalone('sub', $cb->Value);

                    $cmd=$this->db->createCommand(SQL::SQL_DELETE_KEY_ATTRIBUTION);
                    $cmd->bindValue(":id",$cb->Value);
                    $cmd->execute();

                    $cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
                    $cmd->bindValue(":id",$cb->Value);
                    $flag = 0;
                    $cmd->bindValue(":flag",$flag);


                    if($cmd->execute()) {
                        $nUnAttributed++;
                        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
                        $cmd->bindValue(":id",$this->id->Value);
                        $cmd = $cmd->query();
                        $data = $cmd->read();

                        $cmd=$this->db->createCommand(SQL::SQL_GET_KEY2);
                        $cmd->bindValue(":id",$cb->Value);
                        $cmd = $cmd->query();
                        $data2 = $cmd->read();

                        $this->log("Attribute the key ".$data2['identificator']." to ".$data['name']." ".$data['firstname']);
                    }
                }
            }
        }

        if($koMsg !== '') {
            $pBack = array('id'=>$this->id->Value, 'koMsg'=>$koMsg);
            $this->Response->redirect($this->Service->constructUrl('user.attribution',$pBack));

        }
        else {
            $pBack = array('id'=>$this->Request['id'],'okMsg'=>Prado::localize('{n} key was unattributed',array('n'=>$nUnAttributed)));
            $this->Response->redirect($this->Service->constructUrl('user.attribution',$pBack));
        }
    }

    public function onCancel($sender, $param) {
        $this->Response->redirect($this->Service->constructUrl('user.UserList'));
    }
}

?>
