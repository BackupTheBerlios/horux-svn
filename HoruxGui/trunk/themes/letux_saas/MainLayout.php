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

class MainLayout extends TTemplateControl
{
	public function onLoad($param)
	{
          
          $p = $this->getService()->getRequestedPagePath();

          if($p == 'install.install')
          {
                  $this->site->Text = Prado::localize('Installation').' - ';
          }
          else
          {
              $db = $this->Application->getModule('horuxDb')->DbConnection;
              $db->Active=true;

              $cmd = $db->createCommand( "SELECT name FROM hr_site WHERE id=1" );
              $query = $cmd->query();
              if($query)
              {
                      $data = $query->read();
                      if($this->Application->Request['page'] == "controlPanel.ControlPanel")
                        $this->site->Text = $data['name'].' - ';
                      else
                        $this->site->Text = $data['name'].' - ';
              }
          }
	}
}

?>
