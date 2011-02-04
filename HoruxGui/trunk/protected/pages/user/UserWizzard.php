<?php


Prado::using('horux.pages.user.sql');

class UserWizzard extends Page {
    public $cards_format;
    protected $fileSize;
    protected $fileName = '';
    protected $fileType;
    protected $fileError;
    protected $hasFile;
    protected $koMessage = '';
    protected $picturepath = "";

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

    public function onInit($param) {

        $sql = "SELECT * FROM hr_user_action WHERE type='userWizardTpl'";
        $cmd=$this->db->createCommand($sql);
        $data=$cmd->query();
        $data = $data->readAll();

        $step = 4;
        if(count($data) > 0) {

            $this->Step3->StepType = "Step";

            for($i=0; $i<count($data); $i++) {
                $wizardStep = new TWizardStep();
                $wizardStep->setTitle(Prado::localize('Step').' '.$step.': '.Prado::localize ($data[$i]['name'],array(),$data[$i]['catalog']) );
                if($i+1 == count($data))
                    $wizardStep->setStepType("Finish");
                else
                    $wizardStep->setStepType("Step");

                $wizardStep->setID('Step' . $step);
                $steps = $this->Wizard1->getWizardSteps();

                $tpl = $this->Service->TemplateManager->getTemplateByFileName ($data[$i]['page']);

                $tpl->instantiateIn($wizardStep);

                $steps->insertAt($steps->getCount()-1, $wizardStep);
                $step++;
            }
        }
        else
            $this->Step3->StepType = 'Finish';

        parent::onInit($param);
    }

    public function onLoad($param) {
        parent::onLoad($param);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_config WHERE id=1" );
        $query = $cmd->query();
        if($query) {

            $data = $query->read();

            if($data['picturepath'] != "") {
                if(!is_writeable('pictures'.DIRECTORY_SEPARATOR.$data['picturepath']))
                    $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save your picture', array('p'=>DIRECTORY_SEPARATOR.$data['picturepath'])), false);
                else
                    $this->picturepath = 'pictures'.DIRECTORY_SEPARATOR.$data['picturepath'].DIRECTORY_SEPARATOR;
            }
            else {
                if(!is_writeable('.'.DIRECTORY_SEPARATOR.'pictures'))
                    $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save your picture', array('p'=>"")), false);
                else
                    $this->picturepath = 'pictures'.DIRECTORY_SEPARATOR;
            }

            if($data['publicurl'] != "") {
                $this->confirmation->setEnabled(true);
                $this->password->setEnabled(true);
                $this->url = $data['publicurl'];

                $cmd = $this->db->createCommand( "SELECT * FROM hr_site WHERE id=1" );
                $query = $cmd->query();
                if($query) {
                    $data = $query->read();
                    $this->siteName = $data['name'];
                }
            }
            else {
                $this->confirmation->setEnabled(false);
                $this->password->setEnabled(false);
            }
        }

        // get the cards format...
        $sql = "SELECT cards_format FROM hr_config WHERE id=1";
        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();
        $sn = $this->Request['serialNumber'];

        if($data['cards_format'] != "") {
          $this->cards_format = $format = $data['cards_format'];
        }

        if(isset($this->Request['serialNumber']) && $this->Request['serialNumber'] != '') {
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

            if(isset($this->Request['serialNumber'])) {
                $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=0" );
                $cmd->bindValue(":sn",$this->Request['serialNumber'],PDO::PARAM_STR);
                $data=$cmd->query();
                $data = $data->read();
                
                if($data) {
                    $this->UnusedKey->setSelectedValue($data['id']);
                }
                else {
                    $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=1" );
                    $cmd->bindValue(":sn",$this->Request['serialNumber'],PDO::PARAM_STR);
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
                        $cmd->bindValue(":serialNumber",$this->Request['serialNumber']);
                        $cmd->execute();

                        $cmd = $this->db->createCommand( SQL::SQL_GET_UNATTRIBUTED_KEY );
                        $data=$cmd->query();
                        $connection->Active=false;

                        $this->UnusedKey->DataSource=$data;
                        $this->UnusedKey->dataBind();


                        $this->UnusedKey->setSelectedValue($this->db->LastInsertID);

                    }

                }
            }
            $this->Wizard1->setActiveStep($this->Step3);
        }

        if(!$this->IsPostBack) {
            $this->picture->setImageUrl('./pictures/unknown.jpg');

            $this->language->DataSource = $this->LanguageList;
            $this->language->dataBind();

            $this->language->setSelectedValue($this->getLanguageDefault());

            $this->department->DataSource = $this->DepartmentList;
            $this->department->dataBind();
            $this->department->setSelectedValue(0);

            $this->UnusedGroup->DataSource=$this->Groups;
            $this->UnusedGroup->dataBind();

            $this->UnusedKey->DataSource=$this->Keys;
            $this->UnusedKey->dataBind();


            $sql = "SELECT * FROM hr_user_action WHERE type='module'";
            $cmd=$this->db->createCommand($sql);
            $data=$cmd->query();
            $data = $data->readAll();

            for($i=0; $i<count($data); $i++) {
                try {
                    Prado::using('horux.pages.'.$data[$i]['page']);
                    $class = $data[$i]['name'];
                    $sa = new $class();
                    $sa->setData($this->db, $this->getForm());
                }
                catch(Exception $e) {
                    //! do noting
                }
            }

        }

        if($this->koMessage != '') {
            $this->displayMessage($this->koMessage, false);
        }
    }

    protected function getDepartmentList() {
        $cmd = $this->db->createCommand( "SELECT name, id AS value FROM hr_department ORDER BY name");
        $data =  $cmd->query();
        $data = $data->readAll();
        $d[0]['value'] = '0';
        $d[0]['name'] = Prado::localize('---- No department ----');
        $data = array_merge($d, $data);
        return $data;
    }

    protected function getLanguageDefault() {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='language' AND `default`=1");
        $data =  $cmd->query();
        $data = $data->read();
        return $data['param'];
    }

    protected function getLanguageList() {
        $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='language' ORDER BY name");
        $data =  $cmd->query();
        return $data->readAll();
    }

    public function getGroups() {
        $id = $this->Request['id'];
        $cmd = $this->db->createCommand( SQL::SQL_GET_GROUPS2 );
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function getKeys() {
        $id = $this->Request['id'];
        $cmd = $this->db->createCommand( SQL::SQL_GET_UNATTRIBUTED_KEY );
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function wizardCompleted($sender,$param) {
        $lastId = $this->savePerson();
        if($lastId) {
            $this->saveGroup($lastId);
            $this->saveKey($lastId);

            $sql = "SELECT * FROM hr_user_action WHERE type='module'";
            $cmd=$this->db->createCommand($sql);
            $data=$cmd->query();
            $data = $data->readAll();

            for($i=0; $i<count($data); $i++) {
                try {
                    Prado::using('horux.pages.'.$data[$i]['page']);
                    $class = $data[$i]['name'];
                    $sa = new $class();
                    $sa->saveData($this->db, $this->getForm(), $lastId);
                }
                catch(Exception $e) {
                    //! do noting
                }
            }

            $this->addStandalone('add', $lastId);

            $this->log("Create with the wizard the user: ".$this->name->SafeText." ".$this->firstname->SafeText);

            $this->Response->redirect($this->Service->constructUrl('user.UserList'));
        }
    }

    protected function addStandalone($function, $userId) {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $userId, 'UserWizzard');
    }

    protected function addStandaloneKey($function, $keyId) {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $keyId, 'UserAttributionKey');
    }

    protected function savePerson() {
        if($this->koMessage != "")
            return false;

        $cmd = $this->db->createCommand( SQL::SQL_ADD_PERSON );
        $sex = 'F';
        if($this->sexF->getChecked())
            $sex = 'F';
        if($this->sexM->getChecked())
            $sex = 'M';

        // Global
        $cmd->bindValue(":sex",$sex,PDO::PARAM_STR);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":firstname",$this->firstname->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":language",$this->language->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":picture",$this->pictureName->Value,PDO::PARAM_STR);
        $cmd->bindValue(":pin_code",$this->pin_code->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":password",sha1($this->password->SafeText),PDO::PARAM_STR);
        $cmd->bindValue(":validity_date",$this->dateToSql($this->validity_date->SafeText),PDO::PARAM_STR);
        $cmd->bindValue(":birthday",$this->dateToSql($this->birthday->SafeText),PDO::PARAM_STR);

        $f1 = $this->masterAuthorization->getChecked() ? 1 : 0;
        $cmd->bindValue(":masterAuthorization",$f1,PDO::PARAM_STR);


        //Personal
        $cmd->bindValue(":avs",$this->avs->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":street",$this->street->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":zip",$this->zip->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":city",$this->city->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":country",$this->country->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":phone1",$this->phone1->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":email1",$this->email1->SafeText,PDO::PARAM_STR);

        //Private
        $cmd->bindValue(":firme",$this->firme->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":department",$this->department->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":street_pr",$this->street_pr->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":npa_pr",$this->zip_pr->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":city_pr",$this->city_pr->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":phone2",$this->phone2->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":email2",$this->email2->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":country_pr",$this->country_pr->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":fax",$this->fax->SafeText,PDO::PARAM_STR);

        if(!$cmd->execute()) return false;

        return $this->db->getLastInsertID();
    }

    public function serverValidatePassword($sender, $param) {
        if($this->password->Text != $this->confirmation->Text)
            $param->IsValid=false;
    }


    protected function saveGroup($lastId) {
        $indices=$this->UnusedGroup->SelectedIndices;

        foreach($indices as $index) {
            $cmd = $this->db->createCommand( SQL::SQL_ATTRIBUTE_GROUP );
            $cmd->bindValue(":id_user",$lastId,PDO::PARAM_STR);

            $item=$this->UnusedGroup->Items[$index];
            $id_group = $item->Value;

            $cmd->bindValue(":id_group",$id_group,PDO::PARAM_STR);

            $cmd->execute();
        }

    }

    protected function saveKey($lastId, $sn='') {

        if($sn != '') {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=0" );
            $cmd->bindValue(":sn",$sn,PDO::PARAM_STR);
            $data=$cmd->query();
            $data = $data->read();
            if($data) {
                $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                $cmd->bindValue(":id_user", $lastId);
                $cmd->bindValue(":id_key",$data['id']);
                $cmd->execute();

                $cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
                $cmd->bindValue(":id",$data['id']);
                $flag = 1;
                $cmd->bindValue(":flag",$flag);
                $cmd->execute();

                $this->addStandaloneKey('add', $data['id']);

                return true;
            }
            else {
                $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=1" );
                $cmd->bindValue(":sn",$sn,PDO::PARAM_STR);
                $data=$cmd->query();
                $data = $data->read();
                if($data) {
                    return false;
                }
                else {
                    //! add the new key in the database
                    $cmd=$this->db->createCommand(SQL::SQL_ADD_KEY);
                    $cmd->bindValue(":serialNumber",$sn);
                    $cmd->execute();
                    //! attribute the new key

                    $lastId2 = $this->db->LastInsertID;

                    $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                    $cmd->bindValue(":id_user", $lastId);
                    $cmd->bindValue(":id_key",$lastId2);
                    $cmd->execute();

                    $this->addStandaloneKey('add', $lastId2);

                    return true;

                }

            }
        }
        else {
            $indices=$this->UnusedKey->SelectedIndices;
            foreach($indices as $index) {
                $cmd = $this->db->createCommand( SQL::SQL_ATTRIBUTE_KEY );
                $cmd->bindValue(":id_user",$lastId,PDO::PARAM_STR);

                $item=$this->UnusedKey->Items[$index];
                $id_key = $item->Value;

                $cmd->bindValue(":id_key",$id_key,PDO::PARAM_STR);

                $cmd->execute();

                $cmd = $this->db->createCommand( SQL::SQL_SET_USED_KEY );
                $cmd->bindValue(":id",$id_key,PDO::PARAM_STR);
                $flag = 1;
                $cmd->bindValue(":flag",$flag,PDO::PARAM_STR);
                $cmd->execute();

                $this->addStandaloneKey('add', $id_key);
            }
        }
    }

    public function fileUploaded($sender,$param) {
        $this->hasFile = $sender->HasFile;
        $this->koMessage  = "";

        if($sender->HasFile) {
            if($sender->FileSize <= 100000 &&
                    preg_match('/^image\//',$sender->FileType )) {
                $fileName = $sender->FileName;

                if(file_exists($this->picturepath.$sender->FileName)) {
                    $fileName = rand().$sender->FileName;
                }
                $sender->saveAs($this->picturepath.$fileName);
                $this->fileName = $fileName;
                $this->fileType = $sender->FileType;
                $this->fileSize = $sender->FileSize;
                $this->fileError = "";
                $this->pictureName->Value = $fileName;
                $this->checkImage($this->picturepath.$fileName);
            }
            else {
                if($sender->FileSize>100000)
                    $this->koMessage = Prado::localize('The picture is bigger than 10K bytes');
                if(!preg_match('/^image\//',$sender->FileType ))
                    $this->koMessage = Prado::localize('The picture is not a picture (jpg, png, gif)');
            }
        }
    }

    protected function checkImage($file) {
        list($width, $height, $type, $attr) = getimagesize($file);

        if($height>150) {
            $percent = (float)150.0/(float)$height;
            $new_width = $width * $percent;
            $new_height = $height * $percent;
        }
        else
            return;

        $ext =  image_type_to_extension($type, false);
        $image = null;
        switch($ext) {
            case "jpg":
            case "jpeg":
                $image = imagecreatefromjpeg($file);
                $image_p = imagecreatetruecolor($new_width, $new_height);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagejpeg($image_p,$file,100);
                break;
            case "png":
                $image = imagecreatefrompng($file);
                $colorTransparent = imagecolortransparent($image);

                $image_p = imagecreatetruecolor($new_width, $new_height);

                $this->transparent("png", $image, $image_p);

                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagepng($image_p,$file,0);
                break;
            case "gif":
                $image = imagecreatefromgif($file);
                $image_p = imagecreate($new_width, $new_height);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                imagegif($image_p,$file);
                break;
            default:
                break;
        }
    }

    protected function transparent($orig_type, $orig_img, $new_img) {
        // Transparency only available for GIFs & PNGs
        if ( ($orig_type == 'gif') || ($orig_type == 'png') ) {
            $trnprt_indx = imagecolortransparent($orig_img);

            // If we have a specific transparent color
            if ($trnprt_indx >= 0) {

                // Get the original image's transparent color's RGB values
                $trnprt_color    = imagecolorsforindex($orig_img, $trnprt_indx);

                // Allocate the same color in the new image resource
                $trnprt_indx    = imagecolorallocate($new_img, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

                // Completely fill the background of the new image with allocated color.
                imagefill($new_img, 0, 0, $trnprt_indx);

                // Set the background color for new image to transparent
                imagecolortransparent($new_img, $trnprt_indx);

                // Always make a transparent background color for PNGs that don't have one allocated already
            }
            elseif ($orig_type == 'png') {

                // Turn off transparency blending (temporarily)
                imagealphablending($new_img, false);

                // Create a new transparent color for image
                $color = imagecolorallocatealpha($new_img, 0, 0, 0, 127);

                // Completely fill the background of the new image with allocated color.
                imagefill($new_img, 0, 0, $color);

                // Restore transparency blending
                imagesavealpha($new_img, true);
            }
        }
    }

    public function activeStepChanged($sender,$param) {
        if($sender->getActiveStepIndex() == 2)
            $this->setHoruxSysTray(true);
        else
            $this->setHoruxSysTray(false);

        if($this->koMessage != "") {
            $sender->setActiveStepIndex(0);
            $this->displayMessage($this->koMessage, false);
        }
        else {
            $this->koMsg->Text = "";
            $this->okMsg->Text = "";
        }
    }

}

?>
