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
	protected $fileName;	
	protected $fileType;
	protected $fileError;
	protected $hasFile;

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
          $userId=$this->Application->getUser()->getUserId();
    	  $this->blockRecord('hr_user', $this->Request['id'], $userId);	

          $this->id->Value = $this->Request['id'];
          $this->setData();
        }
    }	

	public function setData()
	{
        $cmd = $this->db->createCommand( SQL::SQL_GET_PERSON );
        $cmd->bindParameter(":id",$this->id->Value, PDO::PARAM_INT);
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
	      	$this->picture->setImageUrl('./pictures/'.$data['picture']);
		  else
		    $this->picture->setImageUrl('./pictures/unknown.jpg');

	      $this->pin_code->Text = $data['pin_code'];

			
	      //Personal
	      $this->street->Text = $data['street'];
	      $this->zip->Text = $data['zip'];
	      $this->city->Text = $data['city'];
	      $this->country->Text = $data['country'];
	      $this->phone1->Text = $data['phone1'];
	      $this->email1->Text = $data['email1'];
	      
	      
	      //Private
	      $this->firme->Text = $data['firme'];
	      $this->department->Text = $data['department'];
	      $this->street_pr->Text = $data['street_pr'];
	      $this->zip_pr->Text = $data['npa_pr'];
	      $this->city_pr->Text = $data['city_pr'];
	      $this->phone2->Text = $data['phone2'];
	      $this->email2->Text = $data['email2'];
	      $this->country_pr->Text = $data['country_pr'];
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

      $cmd->bindParameter(":id",$this->id->Value,PDO::PARAM_STR);

      	
      // Global
      $cmd->bindParameter(":sex",$sex,PDO::PARAM_STR);
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":firstname",$this->firstname->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":language",$this->language->getSelectedValue(),PDO::PARAM_STR);
      $cmd->bindParameter(":pin_code",$this->pin_code->SafeText,PDO::PARAM_STR);
       
      if($this->delPicture->getChecked())
      {
      	$this->fileName = "";
      	if(is_file('./pictures/'.$this->pictureName->Value) )      
      		unlink('./pictures/'.$this->pictureName->Value);
      	$cmd->bindParameter(":picture",$this->fileName,PDO::PARAM_STR);
      	
      }
	  else
	  {
	      if($this->hasFile)
	      {
	      	if(is_file('./pictures/'.$this->pictureName->Value) &&
	      	   $this->pictureName->Value != $this->fileName)      
	      		unlink('./pictures/'.$this->pictureName->Value);
	      	$cmd->bindParameter(":picture",$this->fileName,PDO::PARAM_STR);
	      }
	      else
	      {
	      	$cmd->bindParameter(":picture",$this->pictureName->Value,PDO::PARAM_STR);
	      }
	  }            
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

      $this->log("Modify the user: ".$this->name->SafeText." ".$this->firstname->SafeText);

      return true;
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
	
			if(file_exists('./pictures/'.$sender->FileName))
			{
				$fileName = rand().$sender->FileName;
			}	
		
			$sender->saveAs('./pictures/'.$fileName);
			$this->fileName = $fileName;
			$this->fileType = $sender->FileType;
			$this->fileSize = $sender->FileSize;
			$this->fileError = "";
			
			$this->checkImage('./pictures/'.$fileName);
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
}