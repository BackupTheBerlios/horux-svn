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

Prado::using('horux.pages.components.tracking.sql');

class tracking extends PageList
{

    protected $accessMessage = NULL;

    public function onLoad($param)
    {
        parent::onLoad($param);

        $asset = $this->Application->getAssetManager();
        $url = $asset->publishFilePath('./protected/pages/components/tracking/assets/icon-48-tracking.png');


        if(!$this->IsPostBack)
        {
            $cmd = NULL;
            if($this->db->DriverName == 'sqlite')
            $cmd=$this->db->createCommand("SELECT name || ' ' || firstname AS Text, id AS Value FROM hr_user WHERE name!='??'");
            else
            $cmd=$this->db->createCommand("SELECT CONCAT(name,' ', firstname) AS Text, id AS Value FROM hr_user WHERE name!='??'");


            $data = $cmd->query();
            $data = $data->readAll();
            $d[0]['Value'] = 'all';
            $d[0]['Text'] = Prado::localize('All');
            $data = array_merge($d, $data);
            $this->FilterName->DataTextField = "Text";
            $this->FilterName->DataValueField = "Value";
            $this->FilterName->DataSource=$data;
            $this->FilterName->dataBind();
            $this->FilterName->setSelectedValue('all') ;

            $cmd=$this->db->createCommand(SQL::SQL_GET_ACCESS_POINT);
            $data = $cmd->query();
            $data = $data->readAll();
            $d[0]['Value'] = 'all';
            $d[0]['Text'] = Prado::localize('All');
            $data = array_merge($d, $data);

            $this->FilterAccessPoint->DataTextField = "Text";
            $this->FilterAccessPoint->DataValueField = "Value";
            $this->FilterAccessPoint->DataSource=$data;
            $this->FilterAccessPoint->dataBind();
            $this->FilterAccessPoint->setSelectedValue('all') ;

            if(Prado::getApplication()->getSession()->contains($this->getApplication()->getService()->getRequestedPagePath().'FilterName'))
            {
                $FilterName = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterName'];
                $FilterAccessPoint = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterAccessPoint'];
                $FilterStatus = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'];
            }
            else
            {
                $FilterName = 'all';
                $FilterAccessPoint = 'all';
                $FilterStatus = 'all';
            }
            
            $FilterFrom = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterFrom'];
            $FilterUntil = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterUntil'];


            if($FilterName)
                $this->FilterName->setSelectedValue($FilterName);
            if($FilterAccessPoint)
                $this->FilterAccessPoint->setSelectedValue($FilterAccessPoint);
            if($FilterStatus)
                $this->FilterStatus->setSelectedValue($FilterStatus);
            if($FilterFrom)
                $this->from->Text = $FilterFrom;
            if($FilterUntil)
                $this->until->Text = $FilterUntil;


            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();




        }
    }

    public function getData()
    {
        if(isset($this->Request['f1']))
        {
            $name = $this->Request['f1'];
            $status = $this->Request['f3'];
            $entry = $this->Request['f4'];

            $from = "";
            $until = "";

            $from = $this->dateToSql( $this->Request['f5'] );
            $until = $this->dateToSql( $this->Request['f6'] );
        }
        else
        {
            $name = $this->FilterName->getSelectedValue();
            $status = $this->FilterStatus->getSelectedValue();
            $entry = $this->FilterAccessPoint->getSelectedValue();

            $from = "";
            $until = "";

            $from = $this->dateToSql( $this->from->SafeText );
            $until = $this->dateToSql( $this->until->SafeText );
        }

        $user = "";

        if($name != "all")
        $user = ' u.id='.$name.' AND ';

        switch($status)
        {
            case 'ok':
                $status = ' t.is_access=1 AND ';
                break;
            case 'ko':
                $status = ' t.is_access=0 AND ';
                break;
            default:
                $status = '';

            }

            if($entry != "all")
            $entry = " t.id_entry=".$entry." AND ";
            else
            $entry = '';

            $date = "";

            if($from == "" && $until == "")
            {
                $date = "";
            }
            else
            {
                if($from != "" && $until != "")
                {
                    $date = " t.date>='$from' AND t.date<='$until' AND ";
                }
                if($from != "" && $until == "")
                {
                    $date = " t.date>='$from' AND ";
                }
                if($from == "" && $until != "")
                {
                    $date = " t.date<='$until' AND ";
                }

            }

            $cmd=$this->db->createCommand("SELECT t.id, u.name, u.firstName, t.date, t.time, d.name AS device, t.id_comment, k.identificator, t.is_access FROM hr_tracking AS t LEFT JOIN hr_user AS u ON u.id = t.id_user LEFT JOIN hr_device AS d ON d.id=t.id_entry LEFT JOIN hr_keys AS k ON k.serialNumber=t.key WHERE $date $entry $status $user d.name!='' ORDER BY t.id DESC LIMIT 0,1000");
            $data = $cmd->query();
            $data = $data->readAll();

            return $data;
        }


        protected function onPrint()
        {
            parent::onPrint();
            $this->pdf->AddPage();



            $this->pdf->SetFont('Arial','',11);
            $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of tracking')),0,0,'L');
            $this->pdf->Ln(10);
            $this->pdf->setDefaultFont();

            $name = $this->Request['f1'];
            if($name == "all" )
            $name = "*";
            else
            {
                if($this->db->DriverName == 'sqlite')
                $cmd=$this->db->createCommand("SELECT name || ' ' || firstname AS Text, id AS Value FROM hr_user WHERE id=".$name);
                else
                $cmd=$this->db->createCommand("SELECT CONCAT(name,' ', firstname) AS Text, id AS Value FROM hr_user WHERE id=".$name);

                $data = $cmd->query();
                $data = $data->read();

                $name = $data['Text'];
            }

            $status = $this->Request['f3'];

            switch( $status )
            {
                case "all":
                    $status = utf8_decode(Prado::localize('All'));
                    break;
                case "ok":
                    $status = utf8_decode(Prado::localize('Access ok'));
                    break;
                case "ko":
                    $status = utf8_decode(Prado::localize('Access ko'));
                    break;
            }

            $accessPoint = $this->Request['f4'];
            if($accessPoint != "all")
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_device WHERE id=".$accessPoint);
                $data = $cmd->query();
                $data = $data->read();
                $accessPoint =  utf8_decode($data['name']);
            }
            else
            $accessPoint = utf8_decode(Prado::localize('All'));

            $from = $this->Request['f5'];
            $until = $this->Request['f6'];


            $this->pdf->Cell(10,5,utf8_decode(Prado::localize('Filter')),'B',1,'L');
            $this->pdf->Ln(1);

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Name'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$name,0,1,'L');

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Status'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$status,0,1,'L');

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('From'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$from,0,1,'L');

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Until'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$until,0,1,'L');

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Access point'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$accessPoint,0,1,'L');


            $this->pdf->Ln(10);


            $header = array(utf8_decode(Prado::localize("Name")),
                utf8_decode(Prado::localize("Date")),
                utf8_decode(Prado::localize("Time")),
                utf8_decode(Prado::localize("Access point")),
                utf8_decode(Prado::localize("Key")),
                utf8_decode(Prado::localize("Is Access")),
            );

            //Couleurs, �paisseur du trait et police grasse
            $this->pdf->SetFillColor(124,124,124);
            $this->pdf->SetTextColor(255);
            $this->pdf->SetDrawColor(255);
            $this->pdf->SetLineWidth(.3);
            $this->pdf->SetFont('','B');
            //En-t�te
            $w=array(40,30,30,30,30,25);
            for($i=0;$i<count($header);$i++)
            $this->pdf->Cell($w[$i],7,$header[$i],1,0,'C',1);
            $this->pdf->Ln();
            //Restauration des couleurs et de la police
            $this->pdf->SetFillColor(215,215,215);
            $this->pdf->SetTextColor(0);
            $this->pdf->SetFont('');
            //Donn�es
            $fill=false;

            $data = $this->getData();

            foreach($data as $d)
            {
                $user= utf8_decode($d['name']." ".$d['firstName']);
                $date = utf8_decode($this->dateFromSql($d['date']));
                $time = utf8_decode($d['time']);
                $device = utf8_decode($d['device']);
                $identificator = utf8_decode($d['identificator']);

                $is_access= utf8_decode($d['is_access'] == 1 ? Prado::Localize("Yes") : Prado::Localize("No"));

                $this->pdf->Cell($w[0],6,$user,'LR',0,'L',$fill);
                $this->pdf->Cell($w[1],6,$date,'LR',0,'L',$fill);
                $this->pdf->Cell($w[2],6,$time,'LR',0,'L',$fill);
                $this->pdf->Cell($w[3],6,$device,'LR',0,'L',$fill);
                $this->pdf->Cell($w[4],6,$identificator,'LR',0,'L',$fill);
                $this->pdf->Cell($w[5],6,$is_access,'LR',0,'L',$fill);
                $this->pdf->Ln();
                $fill=!$fill;
            }

            $this->pdf->Cell(array_sum($w),0,'','T');

            $this->pdf->render();

        }

        public function itemCreated($sender, $param)
        {
            $item=$param->Item;

            $accessMessage[0] = Prado::localize("Authorize access");
            $accessMessage[1] = Prado::localize("The key is bloqued");
            $accessMessage[2] = Prado::localize("Unknown key");
            $accessMessage[3] = Prado::localize("The key is not attributed");
            $accessMessage[4] = Prado::localize("The person does not belong to any group");
            $accessMessage[5] = Prado::localize("Access refused during the week-end");
            $accessMessage[6] = Prado::localize("Access refused during the non-workinf day");
            $accessMessage[7] = Prado::localize("Access refused at this date");
            $accessMessage[8] = Prado::localize("Access refused at this time");
            $accessMessage[9] = Prado::localize("No access for this access point");
            $accessMessage[10] = Prado::localize("Not handled by Horux");
            $accessMessage[11] = Prado::localize("The person is blocked");

            if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem' )
            {
                $i = $item->DataItem['id_comment'];
                $item->CComment->Comment->Text = $accessMessage[$i];

                if($item->DataItem['is_access'] == 1)
                $item->CComment->Comment->ForeColor = "green";
                else
                $item->CComment->Comment->ForeColor = "red";
            }
        }

        public function selectionChangedName($sender, $param)
        {
            $this->onRefresh($sender, $param);
        }

        public function selectionChangedFirstName($sender, $param)
        {
            $this->onRefresh($sender, $param);
        }

        public function selectionChangedStatus($sender, $param)
        {
            $this->onRefresh($sender, $param);
        }

        public function selectionChangedAccessPoint($sender, $param)
        {
            $this->onRefresh($sender, $param);
        }

        public function onRefresh($sender, $param)
        {
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterName'] = $this->FilterName->getSelectedValue();
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterAccessPoint'] =  $this->FilterAccessPoint->getSelectedValue();
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'] = $this->FilterStatus->getSelectedValue();
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterFrom'] = $this->from->Text;
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterUntil'] = $this->until->Text;

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
        }
    }

    ?>
