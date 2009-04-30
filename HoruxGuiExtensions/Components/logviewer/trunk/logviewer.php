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


class logviewer extends Page
{

	

    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->IsPostBack)
        {          

			$data = $this->Data;

			if($data !== false)
			{
			
				$this->LogFile->DataTextField = "Text";
				$this->LogFile->DataValueField = "Value";
				$this->LogFile->DataSource=$this->Data;
				$this->LogFile->dataBind(); 

				if(count($this->Data))
				{
					$this->LogFile->setSelectedIndex(0) ; 
					$this->LogFileViewer->Text = file_get_contents( $this->LogFile->getSelectedValue() ); 
				}
			}
	
			$asset = $this->Application->getAssetManager();
			$asset->publishFilePath('./protected/pages/components/logviewer/assets/icon-48-logviewer.png');
			$url = $asset->publishFilePath('./protected/pages/components/logviewer/assets/logviewer.css');

			$this->getClientScript()->registerStyleSheetFile('logviewerJCss', $url);

        }
     }	

	protected function getData()
	{
		$d = array();

		$cmd = $this->db->createCommand( "SELECT * FROM hr_config WHERE id=1" );
		$query = $cmd->query();

		$data = $query->read();

		$dir = $data['log_path'];

		if(file_exists($dir))
		{
			if(!is_readable($dir))
			{
				$this->displayMessage(Prado::localize('The directory {dir} is not readable', array("dir"=>$dir)), false);	

            	return false;
			}
				
		}
		else
		{
			$this->displayMessage(Prado::localize('The directory {dir} does not exist', array("dir"=>$dir)), false);	
            return false;
		}
		
		$list = scandir($dir);
		$i=0;
		foreach($list as $l)
		{
			if($l != "." && $l!="..")
			{
        		$d[$i]['Value'] = "./horuxlog/".$l;
        		$d[$i++]['Text'] = $l;
			}
		}
		return $d;
	}
	
	public function onRefresh($sender, $param)
	{
       $this->LogFileViewer->Text = file_get_contents( $this->LogFile->getSelectedValue() );  
       $this->Page->CallbackClient->update('list', $this->LogFileViewer);		
	}

	public function selectionChangedName($sender, $param)
	{
       $this->LogFileViewer->Text = file_get_contents( $this->LogFile->getSelectedValue() ); 
       $this->Page->CallbackClient->update('list', $this->LogFileViewer);
	}
}

?>
