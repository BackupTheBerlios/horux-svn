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

Prado::using('horux.pages.site.sql');

class Site extends Page
{
	protected $fileSize;	
	protected $fileName;	
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
            if(!is_writeable('.'.DIRECTORY_SEPARATOR.'pictures'.DIRECTORY_SEPARATOR.$data['picturepath']))
                $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save your logo', array('p'=>DIRECTORY_SEPARATOR.$data['picturepath'])), false);
            else
                $this->picturepath = '.'.DIRECTORY_SEPARATOR.'pictures'.DIRECTORY_SEPARATOR.$data['picturepath'].DIRECTORY_SEPARATOR;
        }
        else
        {
            if(!is_writeable('.'.DIRECTORY_SEPARATOR.'pictures'))
                $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save your logo', array('p'=>"")), false);
            else
                $this->picturepath = '.'.DIRECTORY_SEPARATOR.'pictures'.DIRECTORY_SEPARATOR;
        }

        if(!$this->isPostBack)
        {
          $this->setData();

            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0)
            {
                $this->tbb->Save->setEnabled(false);
            }
        }
    }

    protected function setData()
    {
        $cmd = $this->db->createCommand( SQL::SQL_GET_SITE );
        $query = $cmd->query();
        if($query)
        {
          	$data = $query->read();
			
			$this->name->Text = $data['name'];
			$this->street->Text = $data['street'];
			$this->npa->Text = $data['npa'];
			$this->city->Text = $data['city'];
			$this->phone->Text = $data['phone'];
			$this->fax->Text = $data['fax'];
			$this->email->Text = $data['email'];
			$this->website->Text = $data['website'];
			$this->tva_number->Text = $data['tva_number'];
			$this->tva->Text = $data['tva'];
			$this->devise->Text = $data['devise'];
			if($data['logo'] != "")
	      		$this->picture->setImageUrl($this->picturepath.$data['logo']);

        } 
    }

	public function onSave($sender, $param)
	{
        if($this->Page->IsValid)
        {
          if($this->saveData())
          {
            $pBack = array('okMsg'=>Prado::localize('The site was modifed successfully'));
          }
          else
          {
          	if($this->fileError != "")
				$pBack = array('koMsg'=>$this->fileError);          	
            else
            	$pBack = array('koMsg'=>Prado::localize('The site was not modified'));
          }
          $this->Response->redirect($this->Service->constructUrl('site.Site',$pBack));
        }		
	}

	public function saveData()
	{
  	  if($this->fileError != "")
  	  {
		return false;
  	  }		
	  $logo = basename ($this->picture->getImageUrl() );
	  if($this->hasFile)
	  {	
	  	if(file_exists($this->picture->getImageUrl()))
	  		unlink($this->picture->getImageUrl());
	  	$logo = $this->fileName;
	  }
	  
      $cmd = $this->db->createCommand( SQL::SQL_UPDATE_SITE );
      	
      $cmd->bindParameter(":name",$this->name->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":logo",$logo,PDO::PARAM_STR);
      $cmd->bindParameter(":street",$this->street->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":npa",$this->npa->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":city",$this->city->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":phone",$this->phone->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":email",$this->email->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":fax",$this->fax->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":website",$this->website->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":tva_number",$this->tva_number->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":tva",$this->tva->SafeText,PDO::PARAM_STR);
      $cmd->bindParameter(":devise",$this->devise->SafeText,PDO::PARAM_STR);
      
      if(!$cmd->execute()) return false;

      $this->log("Modify the site configuration");

      return true;
	} 
	

	public function fileUploaded($sender,$param)
    {
      $this->hasFile = $sender->HasFile; 
      	
	if(!is_writeable('.'.DIRECTORY_SEPARATOR.'pictures')) return;

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
	 
}
