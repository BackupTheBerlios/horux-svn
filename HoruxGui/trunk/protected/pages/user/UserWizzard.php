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

	public function onLoad($param)
      {
        parent::onLoad($param); 
        
        if(isset($this->Request['serialNumber']))
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
        
        if(!$this->IsPostBack)
        {       
        	$this->UnusedGroup->DataSource=$this->Groups;
        	$this->UnusedGroup->dataBind();

        	$this->UnusedKey->DataSource=$this->Keys;
        	$this->UnusedKey->dataBind();
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
			$this->addStandalone('add', $lastId);

            $this->log("Create with the wizard the user: ".$this->name->SafeText." ".$this->firstname->SafeText);

            $this->Response->redirect($this->Service->constructUrl('user.UserList'));
		}
	}
	
	protected function addStandalone($function, $userId)
	{
		
		$cmd=$this->db->createCommand(SQL::SQL_GET_KEY);
		$cmd->bindParameter(":id",$userId);
		$data = $cmd->query();
		$data = $data->readAll();
		
		//pour chaque rfid
		foreach($data as $d)
		{
			$rfid = $d['serialNumber'];
			if( $d['isBlocked'] == 0 )
			{
				$cmd=$this->db->createCommand(SQL::SQL_GET_GROUPS);
				$cmd->bindParameter(":id",$userId);
				$data2 = $cmd->query();
				$data2 = $data2->readAll();
				
				//pour chaque groupe
				foreach($data2 as $d2)
				{
					$idgroup = $d2['id'];
					$cmd=$this->db->createCommand("SELECT * FROM hr_user_group_access WHERE id_group=:id");
					$cmd->bindParameter(":id",$idgroup);
					$data3 = $cmd->query();
					$data3 = $data3->readAll();
					
					foreach($data3 as $d3)
					{
						$idreader = $d3['id_device'];
						
						$cmd=$this->db->createCommand("INSERT INTO hr_standalone_action_service (`type`, `serialNumber`, `rd_id`) VALUES (:func,:rfid,:rdid)");
						$cmd->bindParameter(":func",$function);
						$cmd->bindParameter(":rfid",$rfid);
						$cmd->bindParameter(":rdid",$idreader);
						$cmd->execute();
					}
					
				}
			}			
		}
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