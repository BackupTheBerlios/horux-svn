<?php

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
