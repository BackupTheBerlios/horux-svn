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

class Mod extends Page
{
    protected $fileSize;
    protected $fileName = '';
    protected $fileType;
    protected $fileError;
    protected $hasFile;

    protected $picturepath = "";

    public function onLoad($param)
    {
        parent::onLoad($param);

        $sql = "SELECT picturepath FROM hr_config WHERE id=1";
        $cmd=$this->db->createCommand($sql);
        $data = $cmd->query();
        $data = $data->read();

        if($data['picturepath'] != "")
        {
            if(!is_writeable('pictures'.DIRECTORY_SEPARATOR.$data['picturepath']))
                $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save your picture', array('p'=>DIRECTORY_SEPARATOR.$data['picturepath'])), false);
            else
                $this->picturepath = 'pictures'.DIRECTORY_SEPARATOR.$data['picturepath'].DIRECTORY_SEPARATOR;
        }
        else
        {
            if(!is_writeable('.'.DIRECTORY_SEPARATOR.'pictures'))
                $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save your picture', array('p'=>"")), false);
            else
                $this->picturepath = 'pictures'.DIRECTORY_SEPARATOR;
        }

        $cmd = $this->db->createCommand( "SELECT * FROM hr_config WHERE id=1" );
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            if($data['publicurl'] != "")
            {
                $this->confirmation->setEnabled(true);
                $this->password->setEnabled(true);
            }
            else
            {
                $this->confirmation->setEnabled(false);
                $this->password->setEnabled(false);
            }
        }

        if(!$this->isPostBack)
        {
            $userId=$this->Application->getUser()->getUserId();
            $this->blockRecord('hr_user', $this->Request['id'], $userId);

            $this->id->Value = $this->Request['id'];
            $this->setData();

            $this->language->DataSource = $this->LanguageList;
            $this->language->dataBind();

            $this->department->DataSource = $this->DepartmentList;
            $this->department->dataBind();
        }
    }

    protected function getDepartmentList()
    {
       $cmd = $this->db->createCommand( "SELECT name, id AS value FROM hr_department ORDER BY name");
       $data =  $cmd->query();
       $data = $data->readAll();
       $d[0]['value'] = '0';
       $d[0]['name'] = Prado::localize('---- No department ----');
       $data = array_merge($d, $data);
       return $data;
    }

    protected function getLanguageList()
    {
       $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='language' ORDER BY name");
       $data =  $cmd->query();
       return $data->readAll();
    }

    public function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_PERSON );
        $cmd->bindValue(":id",$this->id->Value, PDO::PARAM_INT);
        $query = $cmd->query();
        if($query)
        {
            $data = $query->read();
            // Global
            if($data['sex'] == 'F')
            {
                $this->sexF->setChecked(true);
                $this->sexM->setChecked(false);
            }
            if($data['sex'] == 'M')
            {
                $this->sexF->setChecked(false);
                $this->sexM->setChecked(true);
            }

            $this->name->Text = $data['name'];
            $this->firstname->Text = $data['firstname'];
            $this->language->setSelectedValue($data['language']);
            $this->pictureName->Value = $data['picture'];
            if($data['picture'] != "")
                $this->picture->setImageUrl($this->picturepath.$data['picture']);
            else
                $this->picture->setImageUrl('./pictures/unknown.jpg');

            $this->pin_code->Text = $data['pin_code'];
            $this->currentPswd->Value = $data['password'];

            $this->masterAuthorization->setChecked($data['masterAuthorization']);

            if($data['validity_date'] != '0000-00-00')
                $this->validity_date->Text = $this->dateFromSql($data['validity_date']);
            else
                $this->validity_date->Text = '';

            //Personal
            $this->avs->Text = $data['avs'];
            $this->street->Text = $data['street'];
            $this->zip->Text = $data['zip'];
            $this->city->Text = $data['city'];
            $this->country->Text = $data['country'];
            $this->phone1->Text = $data['phone1'];
            $this->email1->Text = $data['email1'];


            //Private
            $this->firme->Text = $data['firme'];
            $this->department->setSelectedValue($data['department']);
            $this->street_pr->Text = $data['street_pr'];
            $this->zip_pr->Text = $data['npa_pr'];
            $this->city_pr->Text = $data['city_pr'];
            $this->phone2->Text = $data['phone2'];
            $this->email2->Text = $data['email2'];
            $this->country_pr->Text = $data['country_pr'];
            $this->fax->Text =  $data['fax'];

        }

    }

    public function onApply($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $id = $this->id->Value;
                $pBack = array('okMsg'=>Prado::localize('The user was modified successfully'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('user.mod', $pBack));
            }
            else
            {
                $id = $this->id->Value;
                if($this->fileError != "")
                $pBack = array('koMsg'=>$this->fileError, 'id'=>$id);
                else
                $pBack = array('koMsg'=>Prado::localize('The user was not modified'), 'id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('user.mod',$pBack));
            }
        }
    }

    public function onSave($sender, $param)
    {
        if($this->Page->IsValid)
        {
            if($this->saveData())
            {
                $pBack = array('okMsg'=>Prado::localize('The user was modified successfully'));
            }
            else
            {
                if($this->fileError != "")
                $pBack = array('koMsg'=>$this->fileError);
                else
                $pBack = array('koMsg'=>Prado::localize('The user was not modified'));
            }
            $this->blockRecord('hr_user', $this->id->Value, 0);
            $this->Response->redirect($this->Service->constructUrl('user.UserList',$pBack));
        }
    }

    public function serverValidatePassword($sender, $param)
    {
        if($this->password->Text != $this->confirmation->Text)
        $param->IsValid=false;
    }

    public function onCancel($sender, $param)
    {
        $this->blockRecord('hr_user', $this->id->Value, 0);
        $this->Response->redirect($this->Service->constructUrl('user.UserList'));
    }

    public function saveData()
    {
        if($this->fileError != "")
        {
            return false;
        }


        $cmd = $this->db->createCommand( SQL::SQL_UPDATE_PERSON );
        $sex = 'F';
        if($this->sexF->getChecked())
        $sex = 'F';
        if($this->sexM->getChecked())
        $sex = 'M';

        $cmd->bindValue(":id",$this->id->Value,PDO::PARAM_STR);


        // Global
        $cmd->bindValue(":sex",$sex,PDO::PARAM_STR);
        $cmd->bindValue(":name",$this->name->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":firstname",$this->firstname->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":language",$this->language->getSelectedValue(),PDO::PARAM_STR);
        $cmd->bindValue(":pin_code",$this->pin_code->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":password",$this->password->SafeText,PDO::PARAM_STR);
        $cmd->bindValue(":validity_date",$this->dateToSql($this->validity_date->SafeText),PDO::PARAM_STR);


        $f1 = $this->masterAuthorization->getChecked() ? 1 : 0;
        $cmd->bindValue(":masterAuthorization",$f1,PDO::PARAM_STR);


        if($this->password->SafeText == "")
        {
            $cmd->bindValue(":password",$this->currentPswd->Value,PDO::PARAM_STR);
        }
        else
        {
            $cmd->bindValue(":password",sha1( $this->password->SafeText),PDO::PARAM_STR);
        }


        if($this->delPicture->getChecked())
        {
            $this->fileName = "";
            if(is_file($this->picturepath.$this->pictureName->Value) )
                unlink($this->picturepath.$this->pictureName->Value);
            $cmd->bindValue(":picture",$this->fileName,PDO::PARAM_STR);

        }
        else
        {
            if($this->hasFile)
            {
                if(is_file($this->picturepath.$this->pictureName->Value) &&
                    $this->pictureName->Value != $this->fileName)
                unlink($this->picturepath.$this->pictureName->Value);
                $cmd->bindValue(":picture",$this->fileName,PDO::PARAM_STR);
            }
            else
            {
                $cmd->bindValue(":picture",$this->pictureName->Value,PDO::PARAM_STR);
            }
        }
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

        $this->addStandalone('add',$this->id->Value);

        $this->log("Modify the user: ".$this->name->SafeText." ".$this->firstname->SafeText);

        return true;
    }

    protected function addStandalone($function, $userId)
    {

        $sa = new TStandAlone();
        $sa->addStandalone($function, $userId, 'UserListMod');

    }

    public function fileUploaded($sender,$param)
    {
        $this->hasFile = $sender->HasFile;

        if($sender->HasFile)
        {

            if($sender->FileSize <= 100000 &&
                preg_match('/^image\//',$sender->FileType ))
            {
                $fileName = $sender->FileName;

                if(file_exists($this->picturepath.$sender->FileName))
                {
                    $fileName = rand().$sender->FileName;
                }

                $sender->saveAs($this->picturepath.$fileName);
                $this->fileName = $fileName;
                $this->fileType = $sender->FileType;
                $this->fileSize = $sender->FileSize;
                $this->fileError = "";

                $this->checkImage($this->picturepath.$fileName);
            }
            else
            {
                if($sender->FileSize>100000)
                $this->fileError = Prado::localize('The picture is bigger than 10K bytes');
                if(!preg_match('/^image\//',$sender->FileType ))
                $this->fileError = Prado::localize('The picture is not a picture (jpg, png, gif)');
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

        protected function onPrint()
        {
            parent::onPrint();
            $cmd = $this->db->createCommand( SQL::SQL_GET_PERSON );
            $cmd->bindValue(":id",$this->Request['id'], PDO::PARAM_INT);
            $query = $cmd->query();
            $data = $query->read();

            $this->pdf->AddPage();

            $this->pdf->Ln(10);

            $this->pdf->SetFont('Arial','B',11);
            $this->pdf->Cell(80 ,0,utf8_decode(Prado::localize('Global')));
            $this->pdf->Ln(6);


            $this->pdf->setDefaultFont();
            if($data['sex'] == 'F')
                $this->pdf->Cell(80 ,0,Prado::localize('Mrs.'));
            else
                $this->pdf->Cell(80 ,0,Prado::localize('Mr.'));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Name')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['name']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Firstname')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['firstname']));
            $this->pdf->Ln(10);

            $this->pdf->SetFont('Arial','B',11);
            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Personal information')));
            $this->pdf->Ln(6);
            $this->pdf->setDefaultFont();

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('AVS')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['avs']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Street')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['street']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('ZIP')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['zip']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('City')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['city']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Country')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['country']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Phone')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['phone1']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Email')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['email1']));
            $this->pdf->Ln(10);

            $this->pdf->SetFont('Arial','B',11);
            $this->pdf->Cell(80 ,0,utf8_decode(Prado::localize('Professional information')));
            $this->pdf->Ln(6);
            $this->pdf->setDefaultFont();

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Firme')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['firme']));
            $this->pdf->Ln(6);

            $cmd = $this->db->createCommand( "SELECT * FROM hr_department WHERE id=:id" );
            $cmd->bindValue(":id",$data['department'], PDO::PARAM_INT);
            $query = $cmd->query();
            $data2 = $query->read();


            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Department')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data2['name']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Street')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['street_pr']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('ZIP')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['npa_pr']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('City')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['city_pr']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Country')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['country_pr']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Phone')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['phone2']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Fax')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['fax']));
            $this->pdf->Ln(6);

            $this->pdf->Cell(40 ,0,utf8_decode(Prado::localize('Email')). ' :');
            $this->pdf->Cell(80 ,0,utf8_decode($data['email2']));
            $this->pdf->Ln(6);

            $this->pdf->render();
        }
    }
