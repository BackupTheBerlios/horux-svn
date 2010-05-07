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


class Add extends Page
{
	protected $fileSize;	
	protected $fileName = '';
	protected $fileType;
	protected $fileError;
	protected $hasFile;
    protected $url;
    protected $siteName;

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

        if(!$this->isPostBack)
        {
			$this->picture->setImageUrl('./pictures/unknown.jpg');
            $this->language->DataSource = $this->LanguageList;
            $this->language->dataBind();

            $this->language->setSelectedValue($this->getLanguageDefault());

            $this->department->DataSource = $this->DepartmentList;
            $this->department->dataBind();
            $this->department->setSelectedValue(0);


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

    protected function getLanguageDefault()
    {
       $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='language' AND `default`=1");
       $data =  $cmd->query();
       $data = $data->read();
       return $data['param'];
    }

    protected function getLanguageList()
    {
       $cmd = $this->db->createCommand( "SELECT * FROM hr_install WHERE type='language' ORDER BY name");
       $data =  $cmd->query();
       return $data->readAll();
    }

    public function serverValidatePassword($sender, $param)
    {
        if($this->password->Text != $this->confirmation->Text)
        $param->IsValid=false;
    }

	public function onApply($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if(($id = $this->saveData()) !== false)
          {
            $pBack = array('okMsg'=>Prado::localize('The user was added successfully'), 'id'=>$id);
            $this->Response->redirect($this->Service->constructUrl('user.mod', $pBack));
          }
          else
          {
         	if($this->fileError != "")
				$pBack = array('koMsg'=>$this->fileError);          	
            else
            	$pBack = array('koMsg'=>Prado::localize('The user was not added'));
          	$this->Response->redirect($this->Service->constructUrl('user.add',$pBack));        	          	
          }
        }		
	}

	public function onSave($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The user was added successfully'));
          }
          else
          {
          	if($this->fileError != "")
				$pBack = array('koMsg'=>$this->fileError);          	
            else
            	$pBack = array('koMsg'=>Prado::localize('The user was not saved'));
          }
          $this->Response->redirect($this->Service->constructUrl('user.UserList',$pBack));
        }		
	}

	public function saveData()
	{
  	  if($this->fileError != "")
  	  {
		return false;
  	  }		
		
	
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
      $cmd->bindParameter(":picture",$this->fileName,PDO::PARAM_STR);
      $cmd->bindParameter(":pin_code",$this->pin_code->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":password",sha1($this->password->SafeText),PDO::PARAM_STR);
      $cmd->bindParameter(":validity_date",$this->dateToSql($this->validity_date->SafeText),PDO::PARAM_STR);


      $f1 = $this->masterAuthorization->getChecked() ? 1 : 0;
      $cmd->bindParameter(":masterAuthorization",$f1,PDO::PARAM_STR);
      
      
      //Personal
      $cmd->bindParameter(":avs",$this->avs->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":street",$this->street->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":zip",$this->zip->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":city",$this->city->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":country",$this->country->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":phone1",$this->phone1->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":email1",$this->email1->SafeText,PDO::PARAM_STR);
      
      //Private
      $cmd->bindParameter(":firme",$this->firme->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":department",$this->department->getSelectedValue(),PDO::PARAM_STR);
      $cmd->bindParameter(":street_pr",$this->street_pr->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":npa_pr",$this->zip_pr->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":city_pr",$this->city_pr->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":phone2",$this->phone2->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":email2",$this->email2->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":country_pr",$this->country_pr->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":fax",$this->fax->SafeText,PDO::PARAM_STR);

      if(!$cmd->execute()) return false;

      $id = $this->db->getLastInsertID();

      if( ($this->email1->SafeText != '' || $this->email2->SafeText != '')  && $this->password->SafeText != '')
      {
        $mailer = new TMailer();

        $email = $this->email1->SafeText == '' ? $this->email2->SafeText : $this->email1->SafeText;

        $mailer->sendUser($email,$this->name->SafeText, $this->firstname->SafeText, $this->password->SafeText, $this->url, $this->siteName);
      }

      $this->log("Add the user: ".$this->name->SafeText." ".$this->firstname->SafeText);

      $this->addStandalone('add',$id);

      return $id;
	} 

    protected function addStandalone($function, $userId)
    {

        $sa = new TStandAlone();
        $sa->addStandalone($function, $userId, 'UserListAdd');

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

    public function onCancel($sender, $param)
    {
        $this->Response->redirect($this->Service->constructUrl('user.UserList'));
    }
	 
}
