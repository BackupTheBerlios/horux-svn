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

Prado::using('horux.pages.user.sql');

class UserWizzard extends Page
{
    protected $fileSize;
    protected $fileName;
    protected $fileType;
    protected $fileError;
    protected $hasFile;
    protected $koMessage = '';

    public function onInit($param)
    {

        $sql = "SELECT * FROM hr_user_action WHERE type='userWizardTpl'";
        $cmd=$this->db->createCommand($sql);
        $data=$cmd->query();
        $data = $data->readAll();

        $step = 4;
        if(count($data) > 0)
        {

            $this->Step3->StepType = "Step";

            for($i=0; $i<count($data); $i++)
            {
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

    public function onLoad($param)
    {
        parent::onLoad($param);

        $cmd = $this->db->createCommand( "SELECT * FROM hr_config WHERE id=1" );
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            if($data['publicurl'] != "")
            {
                $this->confirmation->setEnabled(true);
                $this->password->setEnabled(true);
                $this->url = $data['publicurl'];

                $cmd = $this->db->createCommand( "SELECT * FROM hr_site WHERE id=1" );
                $query = $cmd->query();
                if($query)
                {
                    $data = $query->read();
                    $this->siteName = $data['name'];
                }
            }
            else
            {
                $this->confirmation->setEnabled(false);
                $this->password->setEnabled(false);
            }
        }

        if(isset($this->Request['serialNumber']) && $this->Request['serialNumber'] != '')
        {
            if($this->Step3->StepType == 'Finish')
            {
                $lastId = $this->savePerson();
                if($lastId)
                {
                    $this->saveGroup($lastId);
                    $this->saveKey($lastId, $this->Request['serialNumber']);
                    $this->addStandalone('add', $lastId);
                    $this->log("Create with the wizard the user: ".$this->name->SafeText." ".$this->firstname->SafeText);
                    $this->Response->redirect($this->Service->constructUrl('user.UserList'));
                }
            }
            else
                $this->Wizard1->setActiveStep($this->Step4);
        }

        if(!$this->IsPostBack)
        {
            $this->UnusedGroup->DataSource=$this->Groups;
            $this->UnusedGroup->dataBind();

            $this->UnusedKey->DataSource=$this->Keys;
            $this->UnusedKey->dataBind();


            $sql = "SELECT * FROM hr_user_action WHERE type='module'";
            $cmd=$this->db->createCommand($sql);
            $data=$cmd->query();
            $data = $data->readAll();

            for($i=0; $i<count($data); $i++)
            {
                try
                {
                    Prado::using('horux.pages.'.$data[$i]['page']);
                    $class = $data[$i]['name'];
                    $sa = new $class();
                    $sa->setData($this->db, $this->getForm());
                }
                catch(Exception $e)
                {
                    //! do noting
                }
            }

        }

        if($this->koMessage != '')
        {
            $this->displayMessage($this->koMessage, false);
        }
    }

    public function getGroups()
    {
        $id = $this->Request['id'];
        $cmd = $this->db->createCommand( SQL::SQL_GET_GROUPS2 );
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function getKeys()
    {
        $id = $this->Request['id'];
        $cmd = $this->db->createCommand( SQL::SQL_GET_UNATTRIBUTED_KEY );
        $data=$cmd->query();
        $connection->Active=false;

        return $data;
    }

    public function wizardCompleted($sender,$param)
    {
        $lastId = $this->savePerson();
        if($lastId)
        {
            $this->saveGroup($lastId);
            $this->saveKey($lastId);

            $sql = "SELECT * FROM hr_user_action WHERE type='module'";
            $cmd=$this->db->createCommand($sql);
            $data=$cmd->query();
            $data = $data->readAll();
            
            for($i=0; $i<count($data); $i++)
            {
                try
                {
                    Prado::using('horux.pages.'.$data[$i]['page']);
                    $class = $data[$i]['name'];
                    $sa = new $class();
                    $sa->saveData($this->db, $this->getForm(), $lastId);
                }
                catch(Exception $e)
                {
                    //! do noting
                }
            }

            $this->addStandalone('add', $lastId);

            $this->log("Create with the wizard the user: ".$this->name->SafeText." ".$this->firstname->SafeText);

            $this->Response->redirect($this->Service->constructUrl('user.UserList'));
        }
    }

    protected function addStandalone($function, $userId)
    {
        $sa = new TStandAlone();
        $sa->addStandalone($function, $userId, 'UserWizzard');
    }

    protected function savePerson()
    {
        if($this->koMessage != "")
        return false;

        $cmd = $this->db->createCommand( SQL::SQL_ADD_PERSON );
        $sex = 'F';
        if($this->sexF->getChecked())
        $sex = 'F';
        if($this->sexM->getChecked())
        $sex = 'M';

        // Global
        $cmd->bindParameter(":sex",$sex,PDO::PARAM_STR);
        $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":firstname",$this->firstname->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":language",$this->language->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindParameter(":picture",$this->pictureName->Value,PDO::PARAM_STR);
        $cmd->bindParameter(":pin_code",$this->pin_code->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":password",sha1($this->password->SafeText),PDO::PARAM_STR);



        //Personal
        $cmd->bindParameter(":street",$this->street->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":zip",$this->zip->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":city",$this->city->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":country",$this->country->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":phone1",$this->phone1->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":email1",$this->email1->SafeText,PDO::PARAM_STR);

        //Private
        $cmd->bindParameter(":firme",$this->firme->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":department",$this->department->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":street_pr",$this->street_pr->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":npa_pr",$this->zip_pr->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":city_pr",$this->city_pr->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":phone2",$this->phone2->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":email2",$this->email2->SafeText,PDO::PARAM_STR);
        $cmd->bindParameter(":country_pr",$this->country_pr->SafeText,PDO::PARAM_STR);

        if(!$cmd->execute()) return false;

        return $this->db->getLastInsertID();
    }

    public function serverValidatePassword($sender, $param)
    {
        if($this->password->Text != $this->confirmation->Text)
        $param->IsValid=false;
    }


    protected function saveGroup($lastId)
    {
        $indices=$this->UnusedGroup->SelectedIndices;

        foreach($indices as $index)
        {
            $cmd = $this->db->createCommand( SQL::SQL_ATTRIBUTE_GROUP );
            $cmd->bindParameter(":id_user",$lastId,PDO::PARAM_STR);

            $item=$this->UnusedGroup->Items[$index];
            $id_group = $item->Value;

            $cmd->bindParameter(":id_group",$id_group,PDO::PARAM_STR);

            $cmd->execute();
        }

    }

    protected function saveKey($lastId, $sn='')
    {

        if($sn != '')
        {
            $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=0" );
            $cmd->bindParameter(":sn",$sn,PDO::PARAM_STR);
            $data=$cmd->query();
            $data = $data->read();
            if($data)
            {
                $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                $cmd->bindParameter(":id_user", $lastId);
                $cmd->bindParameter(":id_key",$data['id']);
                $cmd->execute();

                $cmd=$this->db->createCommand(SQL::SQL_SET_USED_KEY);
                $cmd->bindParameter(":id",$data['id']);
                $flag = 1;
                $cmd->bindParameter(":flag",$flag);
                $cmd->execute();

                $this->addStandalone('add', $data['id']);

                return true;
            }
            else
            {
                $cmd = $this->db->createCommand( "SELECT * FROM hr_keys WHERE serialNumber=:sn AND isUsed=1" );
                $cmd->bindParameter(":sn",$sn,PDO::PARAM_STR);
                $data=$cmd->query();
                $data = $data->read();
                if($data)
                {
                    return false;
                }
                else
                {
                    //! add the new key in the database
                    $cmd=$this->db->createCommand(SQL::SQL_ADD_KEY);
                    $cmd->bindParameter(":serialNumber",$sn);
                    $cmd->execute();
                    //! attribute the new key

                    $lastId2 = $this->db->LastInsertID;

                    $cmd=$this->db->createCommand(SQL::SQL_ATTRIBUTE_KEY);
                    $cmd->bindParameter(":id_user", $lastId);
                    $cmd->bindParameter(":id_key",$lastId2);
                    $cmd->execute();

                    $this->addStandalone('add', $lastId2);

                    return true;

                }

            }
        }
        else
        {
            $indices=$this->UnusedKey->SelectedIndices;
            foreach($indices as $index)
            {
                $cmd = $this->db->createCommand( SQL::SQL_ATTRIBUTE_KEY );
                $cmd->bindParameter(":id_user",$lastId,PDO::PARAM_STR);

                $item=$this->UnusedKey->Items[$index];
                $id_key = $item->Value;

                $cmd->bindParameter(":id_key",$id_key,PDO::PARAM_STR);

                $cmd->execute();

                $cmd = $this->db->createCommand( SQL::SQL_SET_USED_KEY );
                $cmd->bindParameter(":id",$id_key,PDO::PARAM_STR);
                $flag = 1;
                $cmd->bindParameter(":flag",$flag,PDO::PARAM_STR);
                $cmd->execute();
            }
        }
    }

    public function fileUploaded($sender,$param)
    {
        $this->hasFile = $sender->HasFile;
        $this->koMessage  = "";

        if($sender->HasFile)
        {
            if($sender->FileSize <= 100000 &&
                preg_match('/^image\//',$sender->FileType ))
            {
                $fileName = $sender->FileName;

                if(file_exists('./protected/pictures/'.$sender->FileName))
                {
                    $fileName = rand().$sender->FileName;
                }

                $sender->saveAs('./protected/pictures/'.$fileName);
                $this->fileName = $fileName;
                $this->fileType = $sender->FileType;
                $this->fileSize = $sender->FileSize;
                $this->fileError = "";
                $this->pictureName->Value = $fileName;
                $this->checkImage('./protected/pictures/'.$fileName);
            }
            else
            {
                if($sender->FileSize>100000)
                $this->koMessage = Prado::localize('The picture is bigger than 10K bytes');
                if(!preg_match('/^image\//',$sender->FileType ))
                $this->koMessage = Prado::localize('The picture is not a picture (jpg, png, gif)');
            }
        }
    }

    protected function checkImage($file)
    {
        list($width, $height, $type, $attr) = getimagesize($file);

        if($height>150)
        {
            $percent = (float)150.0/(float)$height;
            $new_width = $width * $percent;
            $new_height = $height * $percent;
        }
        else
        return;

        $ext =  image_type_to_extension($type, false);
        $image = null;
        switch($ext)
        {
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

        protected function transparent($orig_type, $orig_img, $new_img)
        {
            // Transparency only available for GIFs & PNGs
            if ( ($orig_type == 'gif') || ($orig_type == 'png') )
            {
                $trnprt_indx = imagecolortransparent($orig_img);

                // If we have a specific transparent color
                if ($trnprt_indx >= 0)
                {

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
                elseif ($orig_type == 'png')
                {

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

        public function activeStepChanged($sender,$param)
        {
            if($sender->getActiveStepIndex() == 2)
                $this->setAccessLink(true);
            else
                $this->setAccessLink(false);

            if($this->koMessage != "")
            {
                $sender->setActiveStepIndex(0);
                $this->displayMessage($this->koMessage, false);
            }
            else
            {
                $this->koMsg->Text = "";
                $this->okMsg->Text = "";
            }
        }

    }

    ?>
