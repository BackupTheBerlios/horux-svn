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

class PageList extends TPage
{
    protected $db = NULL;
	protected $pdf = NULL;
	
    public function onPreInit($param)
    {

      $this->db = $this->Application->getModule('horuxDb')->DbConnection;
       $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
      $this->db->Active=true;
    
	  $sql = "SELECT * FROM hr_install WHERE `default`=1 AND type='template'";
	  $cmd=$this->db->createCommand($sql);
	  $data = $cmd->query();
	  $data = $data->read();
      
      $this->setTheme($data['name']);      
      
      $this->setMasterClass('themes.'.$data['name'].'.MainLayout');
      
      if(isset($this->Request['lang']))
      {
      	$lang = $this->Request['lang'];
      	if($lang != "default")
      	{
	    	$this->application->setGlobalState('lang',$lang);
      	}
		else
		{
		  $sql = "SELECT * FROM hr_install WHERE `default`=1 AND type='language'";
		  $cmd=$this->db->createCommand($sql);
		  $data = $cmd->query();
		  $data = $data->read();
		  $lang = $data['param'];
		  $this->application->setGlobalState('lang',$lang);
			
		}
      }
	  else
	  {
	  	if(!($lang = $this->application->getGlobalState('lang',false)))
	  	{
		  $sql = "SELECT * FROM hr_install WHERE `default`=1 AND type='language'";
		  $cmd=$this->db->createCommand($sql);
		  $data = $cmd->query();
		  $data = $data->read();
		  $lang = $data['param'];
		  $this->application->setGlobalState('lang',$lang);
	  		
	  	}
	  	
	  }

      
      $this->getApplication()->getGlobalization()->setCulture($this->application->getGlobalState('lang'));      
      
	  $this->checkUsersSession();
      
    }
	
    public function onLoad($param)
    {
        parent::onLoad($param); 
        if(isset($this->Request['action']) && $this->Request['action']  == 'print')
        {
                $this->onPrint();
        }	

        if(!$this->IsPostBack)
        {
          $currentPage = $this->getApplication()->getGlobalState($this->getApplication()->getService()->getRequestedPagePath().'currentPage', false);
          
          if($currentPage)
          {
            $this->DataGrid->CurrentPageIndex = $currentPage;
          }
        }
    }

    protected function log($text)
    {
        $guiLog = new TGuiLog();
        $guiLog->log($text);
    }
    
    protected function checkUsersSession()
    {
	  $sql = "SELECT * FROM hr_superusers WHERE isLogged=1";
	  $cmd=$this->db->createCommand($sql);
	  $data = $cmd->query();
	  $data = $data->readAll();
	  
	  foreach($data as $d)
	  {
	  	$session_id = $d['session_id'];
	  	$session_path = session_save_path();
	  	
		$handle = @opendir($session_path);
		if(!$handle) return;
		 // and scan through the items inside
		 while (FALSE !== ($item = readdir($handle)))
		 {
		     // if the filepointer is not the current directory
			 // or the parent directory
			 if($item != '.' && $item != '..')
			 {
	  		         // we build the new path to delete
			        $path = $session_path.DIRECTORY_SEPARATOR.$item;
		     		if(filesize($path) == 0)
		     		{
		     			$sess = explode("_", $item);	
		     		
						$sql = "UPDATE hr_superusers SET isLogged=0 WHERE session_id='".$sess[1]."'";
						$cmd = $this->db->createCommand($sql);
						$res = $cmd->Execute();			     			
		     		}
			 }		
		 }			
		closedir($handle);	
	  	
	  }
    	
    }  
    
    public function getUserName($id)
    {
	  $sql = "SELECT name FROM hr_superusers WHERE id=".$id;
	  $cmd=$this->db->createCommand($sql);
	  $data = $cmd->query();
	  $data = $data->read();

	  return $data['name'];    	
    }  


    protected function setAccessLink($flag)
    {
        $c = $this->findControlsById("mainMenu");
        $c[0]->setAccessLink($flag);
    }

    protected function displayMessage($text, $isOk)
    {
        if($isOk)
        {
          $this->okMsg->Text = '<dl id="system-message">';
          $this->okMsg->Text .= '<dt class="message">Message</dt>';
          $this->okMsg->Text .= '<dd class="message message fade"><ul><li>';
          $this->okMsg->Text .= $text;
          $this->okMsg->Text .= '</li></ul></dd></dl>';
        }
        else
        {
          $this->okMsg->Text = '<dl id="system-message">';
          $this->okMsg->Text .= '<dt class="notice">Message</dt>';
          $this->okMsg->Text .= '<dd class="notice notice fade"><ul><li>';
          $this->okMsg->Text .= $text;
          $this->okMsg->Text .= '</li></ul></dd></dl>';
        }

    }

    public function changePage($sender,$param)
    {
        $this->getApplication()->setGlobalState($this->getApplication()->getService()->getRequestedPagePath().'currentPage', $param->NewPageIndex);
        $this->DataGrid->CurrentPageIndex=$param->NewPageIndex;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
    }

    public function pagerCreated($sender,$param)
    {
        $param->Pager->Controls->insertAt(0,'Page: ');
    }
 
    public function changePagerPosition($sender,$param)
    {
        $top=$sender->Items[0]->Selected;
        $bottom=$sender->Items[1]->Selected;
        if($top && $bottom)
            $position='TopAndBottom';
        else if($top)
            $position='Top';
        else if($bottom)
            $position='Bottom';
        else
            $position='';

        if($position==='')
            $this->DataGrid->PagerStyle->Visible=false;
        else
        {
            $this->DataGrid->PagerStyle->Position=$position;
            $this->DataGrid->PagerStyle->Visible=true;
        }
    }
 
    public function useNumericPager($sender,$param)
    {
        $this->DataGrid->PagerStyle->Mode='Numeric';
        $this->DataGrid->PagerStyle->NextPageText=$this->NextPageText->Text;
        $this->DataGrid->PagerStyle->PrevPageText=$this->PrevPageText->Text;
        $this->DataGrid->PagerStyle->PageButtonCount=$this->PageButtonCount->Text;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
    }
 
    public function useNextPrevPager($sender,$param)
    {
        $this->DataGrid->PagerStyle->Mode='NextPrev';
        $this->DataGrid->PagerStyle->NextPageText=$this->NextPageText->Text;
        $this->DataGrid->PagerStyle->PrevPageText=$this->PrevPageText->Text;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
    }
 
    public function changePageSize($sender,$param)
    {
        $this->DataGrid->PageSize=TPropertyValue::ensureInteger($this->PageSize->Text);
        $this->DataGrid->CurrentPageIndex=0;
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
    }
    
    protected function dateToSql($date)
    {
    	if($date == "")	return $date;	
    
		$date = explode('-',$date);
		return $date[2].'-'.$date[1].'-'.$date[0];	
    }

    protected function dateFromSql($date)
    {
    	if($date == "")	return $date;	

		$date = explode('-',$date);
		return $date[2].'-'.$date[1].'-'.$date[0];	
    }
    
    protected function onPrint()
    {
        $this->db = $this->Application->getModule('horuxDb')->DbConnection;
        $this->db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,true);
        $this->db->Active=true;
    
            $cmd = $this->db->createCommand( "SELECT * FROM hr_site WHERE id=1" );	
            $query = $cmd->query();
            $data = $query->read();
            
            include("PrintList.php");
            
            $this->pdf = new PrintListPDF();
            $this->pdf->userName = $this->application->getUser()->getName();
            $this->pdf->siteName = utf8_decode($data['name']);
            //$this->pdf->AddPage();
            $this->pdf->SetFont('Arial','',10);		
    }
	
    protected function blockRecord($table, $id, $flag)
    {
    	$sql = "UPDATE $table SET locked=$flag WHERE id=$id";
    	
    	$cmd=$this->db->createCommand($sql);
    	$cmd->Execute();
    }
    
    public function isRecordBlock($table,$id)
    {
    	if($id == '') return false;	
    
    	$userId = $this->application->getUser()->getUserID();	
    
    	$sql = "SELECT locked FROM $table WHERE id=$id AND locked<>$userId";

    	$cmd=$this->db->createCommand($sql);
    	$data = $cmd->query();
    	$data = $data->read();
    	return $data['locked'] > 0;
    }
}

?>
