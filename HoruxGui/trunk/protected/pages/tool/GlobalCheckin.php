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


class GlobalCheckin extends Page
{
	public function onLoad($param)
	{
		parent::onLoad($param);
		
		$this->Checkin();

        $this->log("Global checking");
	}	
	
	protected function Checkin()
	{
        $cmd = NULL;
        if($this->db->DriverName == 'sqlite')
        {
            $cmd = $this->db->createCommand( "select * from sqlite_master" );
        }
        else
        {
            $cmd = $this->db->createCommand( "SHOW TABLE STATUS" );
        }
		$data = $cmd->query();
		
		$data = $data->readAll();
		
		$tables = array();
		
		foreach($data as $d)
		{
            $cmd = NULL;
            if($this->db->DriverName == 'sqlite')
            {
                $cmd = $this->db->createCommand("pragma table_info('".$d['name']."')");
                $data3 = $cmd->query();
                $data3 = $data3->readAll();

                $data2 = false;
                if($data3)
                {
                    foreach($data3 as $data4)
                    {
                       if( $data4['name'] == 'locked' ) $data2 = true;
                    }
                }

            }
            else
            {
                $cmd = $this->db->createCommand("SHOW COLUMNS FROM ".$d['Name']." WHERE Field='locked'");
                $data2 = $cmd->query();
                $data2 = $data2->read();
            }

			if($data2)
			{
                if($this->db->DriverName == 'sqlite')
                    $cmd = $this->db->createCommand("SELECT COUNT(*) AS nb FROM ".$d['name']." WHERE locked>0" );
                else
                    $cmd = $this->db->createCommand("SELECT COUNT(*) AS nb FROM ".$d['Name']." WHERE locked>0" );

				$data2 = $cmd->query();
				$data2 = $data2->read();
				
                if($this->db->DriverName == 'sqlite')
    				$cmd = $this->db->createCommand("UPDATE ".$d['name']." SET locked=0");
                else
    				$cmd = $this->db->createCommand("UPDATE ".$d['Name']." SET locked=0");

				$cmd->execute();
				
                if($this->db->DriverName == 'sqlite')
    				$tables[] = array('name'=>$d['name'], 'item'=>Prado::localize('Checked in {n} Items', array('n'=>$data2['nb'])));
                else
    				$tables[] = array('name'=>$d['Name'], 'item'=>Prado::localize('Checked in {n} Items', array('n'=>$data2['nb'])));
			}
		}
		
        $this->DataGrid->DataSource=$tables;
        $this->DataGrid->dataBind();				
	}
}

?>
