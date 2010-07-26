<?php
/**
* @version      $Id$
* @package      Horux
* @subpackage   Horux
* @copyright    Copyright (C) 2007  Letux. All rights reserved.
* @license      GNU/GPL, see LICENSE.php
* Horus is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/


class status extends Page
{

    public function onLoad($param)
    {
        parent::onLoad($param);

        if($this->Request['dispimage_p'])
        {
                $this->getImage_p($this->Request['dispimage_p']);
        }

        if($this->Request['dispimage_s1'])
        {
                $this->getImage_s1();
        }

        if($this->Request['dispimage_s2'])
        {
                $this->getImage_s2($this->Request['dispimage_s2']);
        }


        if(!$this->IsPostBack)
        {          
            $sql = "SELECT * FROM  hr_vp_parking";

            $cmd=$this->db->createCommand($sql);
            $data = $cmd->query();

            $this->Repeater->DataSource= $data->readAll();
            $this->Repeater->dataBind();

          $sql = "SELECT * FROM  hr_vp_subscription ORDER BY id";

          $cmd=$this->db->createCommand($sql);
          $data = $cmd->query();
            $this->Repeater2->DataSource= $data->readAll();
            $this->Repeater2->dataBind();

        }
     }	

        //! create a pie statistics the subscription
      protected function getImage_s2($i)
      {
        require_once "./protected/pages/components/velopark/artichow/BarPlot.class.php";


          $graph = new Graph(500, 300);
          $graph->setBackgroundGradient(
                  new LinearGradient(
                          new White,
                          new VeryLightGray(40),
                          0
                  )
          );            
      }

        //! create a pie statistics the subscription
      protected function getImage_s1()
      {
        require_once "./protected/pages/components/velopark/artichow/Pie.class.php";


          $graph = new Graph(500, 300);
          $graph->setBackgroundGradient(
                  new LinearGradient(
                          new White,
                          new VeryLightGray(40),
                          0
                  )
          );            

          $graph->title->set(Prado::localize("Subscription sales"));
          $graph->shadow->setSize(3);
          $graph->shadow->smooth(TRUE);
          $graph->shadow->setPosition(Shadow::RIGHT_BOTTOM);
          $graph->shadow->setColor(new DarkGray);
          
          $sql = "SELECT COUNT(*) AS n FROM hr_vp_subscription_attribution GROUP BY subcription_id ORDER BY subcription_id";

          $cmd=$this->db->createCommand($sql);
          $dataSubGroup = $cmd->query();
          $dataSubGroup = $dataSubGroup->readAll();          
          
          $values = array();
          foreach($dataSubGroup as $d)
          {
            $values[] = $d['n'];
          }

          if(count($values) > 0) {
              $plot = new Pie($values, PIE_EARTH);
              $plot->setCenter(0.42, 0.55);
              $plot->setSize(0.7, 0.7);
              $plot->set3D(20);

              $sql = "SELECT * FROM  hr_vp_subscription ORDER BY id";

              $cmd=$this->db->createCommand($sql);
              $dataSub = $cmd->query();
              $dataSub = $dataSub->readAll();

              $a = array();
              foreach($dataSub as $d)
              {
                $a[] = utf8_decode($d['name']);
              }

              $plot->setLegend($a);

              $plot->legend->setPosition(1.3);
              $plot->legend->shadow->setSize(0);
              $plot->legend->setBackgroundColor(new VeryLightGray(30));

          
            $graph->add($plot);
          }
          
          $graph->draw();

          exit;
      }
      
        //! create the statiscs for a parking
	protected function getImage_p($id)
	{

          require_once "./protected/pages/components/velopark/artichow/Pie.class.php";
		
          $sql = "SELECT id, area,filling, name FROM  hr_vp_parking ";

          $cmd=$this->db->createCommand($sql);
          $data = $cmd->query();
          $data = $data->read();

          
          $graph = new Graph(500, 300);
          $graph->setBackgroundGradient(
                  new LinearGradient(
                          new White,
                          new VeryLightGray(40),
                          0
                  )
          );		


          $graph->title->set(Prado::localize("Parking {name}", array("name" => utf8_decode($data['name']))));
          $graph->shadow->setSize(3);
          $graph->shadow->smooth(TRUE);
          $graph->shadow->setPosition(Shadow::RIGHT_BOTTOM);
          $graph->shadow->setColor(new DarkGray);		


          $values = array($data['filling'],$data['area']-$data['filling']+0.000000001);
          //$values = array(22.0,0.000000001);
          
          $colors = array(
                          new LightRed,
                          new LightGreen,
                          );

          $plot = new Pie($values, $colors);
          $plot->setCenter(0.42, 0.55);
          $plot->setSize(0.7, 0.7);
          $plot->set3D(20);

          
          /*if($data['filling']>0)
                  $plot->explode(array(1 => 10));*/
          
          $plot->setLegend(array(
                  utf8_decode(Prado::localize('Used')),
                  utf8_decode(Prado::localize('Free')),
          ));
          
          $plot->legend->setPosition(1.3);
          $plot->legend->shadow->setSize(0);
          $plot->legend->setBackgroundColor(new VeryLightGray(30));		


          $graph->add($plot);
          $graph->draw();

          exit;
	}	
}

?>