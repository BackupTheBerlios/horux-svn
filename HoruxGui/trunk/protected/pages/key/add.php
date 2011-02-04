<?php


Prado::using('horux.pages.key.sql');

class add extends Page
{
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

    public function onLoad($param)
    {
        parent::onLoad($param);
        $this->setHoruxSysTray(true);

        // get the cards format...
        $sql = "SELECT cards_format FROM hr_config WHERE id=1";
        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();
        $sn = $this->Request['sn'];

        if($data['cards_format'] != "") {
          $this->cards_format = $format = $data['cards_format'];
        }

        if(isset($this->Request['sn']) && !$this->serialNumber->SafeText)
        {
            $this->serialNumber->Text = $this->Request['sn'];

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
            $this->identificator->Text = $sn;
        }

        $this->person->DataSource = $this->PersonList;
        $this->person->dataBind();


        if($this->person->getItemCount() && $this->person->getSelectedValue() == '')
        {
            $this->person->setSelectedIndex(0);
        }
    }

    protected function getPersonList()
    {
        $cmd = NULL;
        if($this->db->DriverName == 'sqlite')
        {
            $cmd = $this->db->createCommand( SQL::SQL_GET_PERSON_SQLITE );
        }
        else
        {
            $cmd = $this->db->createCommand( SQL::SQL_GET_PERSON );
        }
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['Value'] = 'null';
        $d[0]['Text'] = Prado::localize('---- No attribution ----');
        $data = array_merge($d, $data);
        return $data;
    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($lastId = $this->saveData())
            {
                $id = $lastId;
                $pBack = array('okMsg'=>Prado::localize('The key was added successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('key.mod', $pBack));
            }
            else
            {
                $pBack = array('koMsg'=>Prado::localize('The key was not added'));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The key was added successfully'));
            }
            else
            $pBack = array('koMsg'=>Prado::localize('The key was not added'));
            $this->Response->redirect($this->Service->constructUrl('key.KeyList',$pBack));
        }
    }

    protected function saveData()
    {
        $res1 = $res2 = true;


        $cmd = $this->db->createCommand( SQL::SQL_ADD_KEY );
        $cmd->bindValue(":identificator",$this->identificator->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":serialNumber",$this->serialNumber->SafeText, PDO::PARAM_STR);

        if($this->isBlocked->getChecked())
        {
            $isBlocked = 1;
            $cmd->bindValue(":isBlocked",$isBlocked, PDO::PARAM_STR);
        }
        else
        {
            $isBlocked = 0;
            $cmd->bindValue(":isBlocked",$isBlocked, PDO::PARAM_STR);
        }

        if($this->person->getSelectedValue() != 'null')
        $isUsed = 1;
        else
        $isUsed = 0;

        $cmd->bindValue(":isUsed",$isUsed, PDO::PARAM_STR);


        $res1 = $cmd->execute();
        $lastId = $this->db->LastInsertID;

        $dd = $this->person->getSelectedValue();

        if($this->person->getSelectedValue() != 'null')
        {
            $cmd2=$this->db->createCommand(SQL::SQL_ADD_TAG_ATTRIBUTION);
            $cmd2->bindValue(":id_key",$lastId);
            $cmd2->bindValue(":id_user",$this->person->getSelectedValue());
            $res2 = $cmd2->execute();

            if($isBlocked == 0)
                $this->addStandalone('add',$lastId);
        }

        $this->log("Add the key: ".$this->serialNumber->SafeText);


        return $lastId;
    }

    protected function addStandalone($function, $idkey)
    {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $idkey, 'KeyAdd');
    }


    public function serverValidateSerialNumber($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_SERIALNUMBER_EXIST);
        $cmd->bindValue(":serialNumber",$this->serialNumber->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }

    public function serverValidateIdentificator($sender, $param)
    {
        $cmd = $this->db->createCommand( SQL::SQL_IS_IDENTIFICATOR_EXIST);
        $cmd->bindValue(":identificator",$this->identificator->SafeText,PDO::PARAM_STR);
        $array = $cmd->query()->readAll();

        if(count($array) > 0)
        $param->IsValid=false;
        else
        $param->IsValid=true;
    }

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('key.KeyList'));
    }
}
