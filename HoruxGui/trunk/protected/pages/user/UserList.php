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

class UserList extends PageList
{
    protected $picturepath = "";

    protected function getData()
    {
        if(isset($this->Request['f1']))
        {
            $name = $this->Request['f1'];
            $firstName = $this->Request['f2'];
            $status = $this->Request['f3'];

            $access = $this->Request['f5'];
            $group = $this->Request['f4'];
        }
        else
        {
            $name = $this->FilterName->SafeText;
            $firstName = $this->FilterFirstName->SafeText;
            $status = $this->FilterStatus->getSelectedValue();

            $access = $this->FilterAccessPoint->getSelectedValue();
            $group = $this->FilterGroup->getSelectedValue();
        }

        switch($status)
        {
            case 'block':
                $status = 'u.isBlocked=1 AND';
                break;
            case 'unblock':
                $status = 'u.isBlocked=0 AND';
                break;
            default:
                $status = '';

            }


            if($group>0)
            {
                if($access == 0)
                {
                    $sql = "SELECT u.name, u.firstname, u.email2, u.email1, u.phone2, u.id, u.isBlocked, u.locked, d.name AS department FROM  hr_user AS u LEFT JOIN hr_user_group_attribution AS g ON g.id_user = u.id LEFT JOIN hr_department AS d ON u.department=d.id WHERE u.name<>'??' AND $status u.name LIKE '%$name%' AND u.firstname LIKE '%$firstName%' AND g.id_group=".$group." ORDER BY u.name, u.firstname";
                }
                else
                {
                    $sql = "SELECT u.name, u.firstname, u.email2, u.email1,u.phone2, u.id, u.isBlocked, u.locked, d.name AS department  FROM  hr_user AS u LEFT JOIN hr_user_group_attribution AS ga ON ga.id_user = u.id LEFT JOIN hr_user_group_access AS a ON a.id_group=ga.id_group LEFT JOIN hr_user_group AS g ON g.id=ga.id_group LEFT JOIN hr_department AS d ON u.department=d.id  WHERE u.name<>'??' AND $status u.name LIKE '%$name%' AND u.firstname LIKE '%$firstName%' AND a.id_device=".$access." AND g.id=".$group." ORDER BY u.name, u.firstname";

                }
            }
            else
            {
                if($access == 0)
                {
                    $sql = "SELECT u.name, u.firstname, u.email2,u.email1, u.phone2, u.id, u.isBlocked, u.locked, d.name AS department  FROM  hr_user AS u LEFT JOIN hr_department AS d ON u.department=d.id  WHERE u.name<>'??' AND $status u.name LIKE '%$name%' AND u.firstname LIKE '%$firstName%' ORDER BY u.name, u.firstname";
                }
                else
                {
                    $sql = "SELECT u.name, u.firstname, u.email2,u.email1, u.phone2, u.id, u.isBlocked, u.locked, d.name AS department  FROM  hr_user AS u LEFT JOIN hr_user_group_attribution AS ga ON ga.id_user = u.id LEFT JOIN hr_user_group_access AS a ON a.id_group=ga.id_group LEFT JOIN hr_user_group AS g ON g.id=ga.id_group LEFT JOIN hr_department AS d ON u.department=d.id  WHERE u.name<>'??' AND $status u.name LIKE '%$name%' AND u.firstname LIKE '%$firstName%' AND a.id_device=".$access." ORDER BY u.name, u.firstname";

                }
            }


            $cmd=$this->db->createCommand($sql);
            $dataReader=$cmd->query();



            $connection->Active=false;

            $data = $dataReader->readAll();

            for($i=0; $i<count($data); $i++)
            {
                $id = $data[$i]['id'];

                $cmd=$this->db->createCommand("SELECT * FROM hr_user_group AS ug LEFT JOIN hr_user_group_attribution AS uga ON uga.id_group = ug.id WHERE uga.id_user=".$id);
                $data2 = $cmd->query();
                $data2 = $data2->readAll();

                $groupes = array();
                foreach($data2 as $d)
                    $groupes[] = $d['name'];

                $groupes = implode(", ",$groupes);

                $data[$i]['groups'] = $groupes;
                
            }


            return $data;
        }


        protected function onPrint()
        {
            parent::onPrint();
            $this->pdf->AddPage();

            $data = $this->getData();

            $this->pdf->SetFont('Arial','',11);
            $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the users')),0,0,'L');
            $this->pdf->Ln(10);
            $this->pdf->setDefaultFont();

            $name = $this->Request['f1'];
            if($name == "%" )
            $name = "*";
            else
            $name = $name."*";

            $firstname = $this->Request['f2'];
            if($firstname == "%" )
            $firstname = "*";
            else
            $firstname = $firstname."*";


            $status = $this->Request['f3'];

            switch( $status )
            {
                case "all":
                    $status = utf8_decode(Prado::localize('All'));
                    break;
                case "block":
                    $status = utf8_decode(Prado::localize('Blocked'));
                    break;
                case "unblock":
                    $status = utf8_decode(Prado::localize('Unblocked'));
                    break;
            }

            $group =  $this->Request['f4'];



            if($group != 0)
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_user_group WHERE id=".$group);
                $data = $cmd->query();
                $data = $data->read();
                $group =  utf8_decode($data['name']);
            }
            else
            $group = utf8_decode(Prado::localize('All'));

            $accessPoint =  $this->Request['f5'];

            if($accessPoint != 0)
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_device WHERE id=".$accessPoint);
                $data = $cmd->query();
                $data = $data->read();
                $accessPoint =  utf8_decode($data['name']);
            }
            else
            $accessPoint = utf8_decode(Prado::localize('All'));


            $this->pdf->Cell(10,5,utf8_decode(Prado::localize('Filter')),'B',1,'L');
            $this->pdf->Ln(1);

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Name'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$name ,0,1,'L');

            $this->pdf->Cell(30, 5,utf8_decode(Prado::localize('Firstname'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$firstname,0,1,'L');

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Status'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$status,0,1,'L');

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Group'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$group,0,1,'L');

            $this->pdf->Cell(30,5,utf8_decode(Prado::localize('Access'))." :",0,0,'L');
            $this->pdf->Cell(0,5,$accessPoint,0,1,'L');


            $this->pdf->Ln(10);


            $header = array(utf8_decode(Prado::localize("Name")),
                utf8_decode(Prado::localize("Firstname")),
                utf8_decode(Prado::localize("Email")),
                utf8_decode(Prado::localize("Phone")),
                utf8_decode(Prado::localize("Group")),
                utf8_decode(Prado::localize("Is Blocked")),
            );

            //Couleurs, �paisseur du trait et police grasse
            $this->pdf->SetFillColor(124,124,124);
            $this->pdf->SetTextColor(255);
            $this->pdf->SetDrawColor(255);
            $this->pdf->SetLineWidth(.3);
            $this->pdf->SetFont('','B');
            //En-t�te
            $w=array(30,30,30,30, 45, 30);
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
                $name= utf8_decode($d['name']);
                $firstname = utf8_decode($d['firstname']);
                $email = utf8_decode($d['email2']);
                $phone = utf8_decode($d['phone2']);
                $isBlocked= utf8_decode($d['isBlocked'] == 1 ? Prado::Localize("Yes") : Prado::Localize("No"));

                $cmd=$this->db->createCommand("SELECT * FROM hr_user_group AS ug LEFT JOIN hr_user_group_attribution AS uga ON ug.id = uga.id_group WHERE uga.id_user=".$d['id']);
                $dataGroup = $cmd->query();
                $dataGroup = $dataGroup->readAll();
                $groupList = array();
                foreach($dataGroup as $g)
                {
                    $groupList[] = $g['name'];
                }
                $groupList = join(", ", $groupList);

                $group = utf8_decode($groupList);

                $this->pdf->Cell($w[0],6,$name,'LR',0,'L',$fill);
                $this->pdf->Cell($w[1],6,$firstname,'LR',0,'L',$fill);
                $this->pdf->Cell($w[2],6,$email,'LR',0,'L',$fill);
                $this->pdf->Cell($w[3],6,$phone,'LR',0,'L',$fill);
                $this->pdf->Cell($w[4],6,$group,'LR',0,'L',$fill);
                $this->pdf->Cell($w[5],6,$isBlocked,'LR',0,'L',$fill);
                $this->pdf->Ln();
                $fill=!$fill;
            }

            $this->pdf->Cell(array_sum($w),0,'','T');

            $this->pdf->render();

        }

        protected function getGroup()
        {
            $sql = "SELECT id AS Value, name AS Text FROM hr_user_group";
            $cmd=$this->db->createCommand($sql);
            $data=$cmd->query();
            $data = $data->readAll();

            $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("All"));

            $data = array_merge($dataAll, $data);

            return $data;
        }

        protected function getAccessPoint()
        {
            $sql = "SELECT id AS Value, name AS Text FROM hr_device WHERE accessPoint=1";
            $cmd=$this->db->createCommand($sql);
            $data=$cmd->query();
            $data = $data->readAll();

            $dataAll[] = array("Value"=>0, "Text"=>Prado::localize("All"));

            $data = array_merge($dataAll, $data);

            return $data;
        }

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
                    $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save/delete your picture', array('p'=>DIRECTORY_SEPARATOR.$data['picturepath'])), false);
                else
                    $this->picturepath = '.'.DIRECTORY_SEPARATOR.'pictures'.DIRECTORY_SEPARATOR.$data['picturepath'].DIRECTORY_SEPARATOR;
            }
            else
            {
                if(!is_writeable('.'.DIRECTORY_SEPARATOR.'pictures'))
                    $this->displayMessage(Prado::localize('The directory ./pictures{p} must be writeable to save/delete your picture', array('p'=>"")), false);
                else
                    $this->picturepath = '.'.DIRECTORY_SEPARATOR.'pictures'.DIRECTORY_SEPARATOR;
            }

            //$this->setHoruxSysTray(true);

            if(!$this->IsPostBack)
            {

                $this->FilterGroup->DataTextField='Text';
                $this->FilterGroup->DataValueField='Value';
                $this->FilterGroup->DataSource=$this->Group;
                $this->FilterGroup->dataBind();
                $this->FilterGroup->setSelectedValue(0);


                $this->FilterAccessPoint->DataTextField='Text';
                $this->FilterAccessPoint->DataValueField='Value';
                $this->FilterAccessPoint->DataSource=$this->AccessPoint;
                $this->FilterAccessPoint->dataBind();
                $this->FilterAccessPoint->setSelectedValue(0);


                $FilterName = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterName'];
                $FilterFirstName = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterFirstName'];
                $FilterStatus = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'];
                $FilterGroup = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterGroup'];
                $FilterAccessPoint = $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterAccessPoint'];

                if($FilterName)
                    $this->FilterName->Text = $FilterName;
                if($FilterFirstName)
                    $this->FilterFirstName->Text = $FilterFirstName;
                if($FilterStatus)
                    $this->FilterStatus->setSelectedValue($FilterStatus);
                if($FilterGroup)
                    $this->FilterGroup->setSelectedValue($FilterGroup);
                if($FilterAccessPoint)
                    $this->FilterAccessPoint->setSelectedValue($FilterAccessPoint);

                $this->DataGrid->DataSource=$this->Data;
                $this->DataGrid->dataBind();
            }

            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterName'] =  $this->FilterName->SafeText;
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterFirstName'] = $this->FilterFirstName->SafeText;


            if(isset($this->Request['okMsg']))
            {
                $this->displayMessage($this->Request['okMsg'], true);
            }
            if(isset($this->Request['koMsg']))
            {
                $this->displayMessage($this->Request['koMsg'], false);
            }
        }

        public function filterChange($sender, $param)
        {
            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
        }

        public function dataBindSubRepeater($sender,$param)
        {
            $sql = "SELECT * FROM hr_user_action WHERE type='userList'";
            $cmd=$this->db->createCommand($sql);
            $data=$cmd->query();
            $data = $data->readAll();

            $d = array();
            for($i=0; $i<count($data); $i++)
            {
                if($this->isAccess($data[$i]['page']))
                $d[] = $data[$i];
            }

            $item=$param->Item;

            if(count($data)>0)
            {
                if($item->ItemType==='Item' || $item->ItemType==='AlternatingItem')
                {
                    $item->tplColExt->listExt->DataSource=$d;
                    $item->tplColExt->listExt->dataBind();
                }
            }
            else
            {
                $item->tplColExt->Visible = false;
            }
        }


        public function isAccess($page)
        {
            $app = $this->getApplication();
            $db = $this->db;

            $usedId = $app->getUser()->getUserID() == null ? 0 : $app->getUser()->getUserID();
            $groupId = $app->getUser()->getGroupID() == null ? 0 : $app->getUser()->getGroupID();

            $sql =  'SELECT `allowed` FROM hr_gui_permissions WHERE ' .
                          '(`page`=\''.$page.'\' OR `page` IS NULL) ' .
                          "AND (" .
                                  "(`selector`='user_id' AND `value`=".$usedId.") " .
                                  "OR (`selector`='group_id' AND `value`=".$groupId.") " .
                          ")" .
                  'ORDER BY `page` DESC';

            $cmd = $db->createCommand($sql);
            $res = $cmd->query();
            $res = $res->readAll();
            // If there were no results
            if (!$res)
            return false;
            else
            // Traverse results
            foreach ($res as $allowed)
            {
                // If we get deny here
                if (! $allowed)
                return false;
            }

            return true;
        }

        public function setBlocked($sender,$param)
        {
            $id = $sender->Text;
            $cmd=$this->db->createCommand(SQL::SQL_UPDATE_SETBLOCK_USER);
            $cmd->bindParameter(":id",$id);

            if($sender->ImageUrl == "./themes/letux/images/menu/icon-16-checkin.png")
            {
                $flag = 1;
                $sender->ImageUrl = "./themes/letux/images/menu/icon-16-access.png";
                $cmd->bindParameter(":flag",$flag);
                $this->addStandalone('block',$id);

                $cmd2=$this->db->createCommand(SQL::SQL_GET_PERSON);
                $cmd2->bindParameter(":id",$id);
                $cmd2 = $cmd2->query();
                $data2 = $cmd2->read();

                $this->log("Block the user ".$data2['name']." ".$data2['name']);
            }
            else
            {
                $flag = 0;
                $sender->ImageUrl = "./themes/letux/images/menu/icon-16-checkin.png";
                $cmd->bindParameter(":flag",$flag);
                $this->addStandalone('unblock',$id);

                $cmd2=$this->db->createCommand(SQL::SQL_GET_PERSON);
                $cmd2->bindParameter(":id",$id);
                $cmd2 = $cmd2->query();
                $data2 = $cmd2->read();

                $this->log("Unblock the user ".$data2['name']." ".$data2['name']);
            }
            $cmd->execute();

            $this->selectionChangedStatus($sender, $param);
        }

        protected function addStandalone($function, $userId)
        {

            $sa = new TStandAlone();
            $sa->addStandalone($function, $userId, 'UserList');

        }

        public function selectionChangedAccessPoint($sender,$param)
        {
            $this->selectionChangedStatus($sender,$param);
        }


        public function selectionChangedGroup($sender,$param)
        {
            $this->selectionChangedStatus($sender,$param);
        }


        public function selectionChangedName($sender,$param)
        {
            $this->selectionChangedStatus($sender,$param);
        }

        public function selectionChangedFirstName($sender,$param)
        {
            $this->selectionChangedStatus($sender,$param);
        }

        public function selectionChangedStatus($sender,$param)
        {
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterName'] = $this->FilterName->SafeText;
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterFirstName'] = $this->FilterFirstName->SafeText;
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterStatus'] = $this->FilterStatus->getSelectedValue();
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterGroup'] = $this->FilterGroup->getSelectedValue();
            $this->Session[$this->getApplication()->getService()->getRequestedPagePath().'FilterAccessPoint'] = $this->FilterAccessPoint->getSelectedValue();

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();
            $this->Page->CallbackClient->update('list', $this->DataGrid);
        }


        public function checkboxAllCallback($sender, $param)
        {
            $cbs = $this->findControlsByType("TActiveCheckBox");
            $isChecked = $sender->getChecked();

            foreach($cbs as $cb)
            {
                $cb->setChecked($isChecked);
            }

        }

        public function onDelete($sender,$param)
        {
            $cbs = $this->findControlsByType("TActiveCheckBox");
            $nDelete = 0;
            $koMsg = '';
            $cbChecked = 0;

            foreach($cbs as $cb)
            {
                if( (bool)$cb->getChecked() && $cb->Value != "0")
                $cbChecked++;
            }

            if($cbChecked==0)
            {
                $koMsg = Prado::localize('Select one item');
            }
            else
            {

                foreach($cbs as $cb)
                {

                    if( (bool)$cb->getChecked() && $cb->Value != "0")
                    {
                        $id_user = $cb->Value;

                        $cmd=$this->db->createCommand(SQL::SQL_GET_PERSON);
                        $cmd->bindParameter(":id",$id_user);
                        $cmd = $cmd->query();
                        $data = $cmd->read();
                        $this->log("Delete the user: ".$data['name']." ".$data['firstname']);

                        //remove image
                        $cmd=$this->db->createCommand(SQL::SQL_GET_PICTURE);
                        $cmd->bindParameter(":id",$id_user);
                        $cmd = $cmd->query();
                        $data = $cmd->read();

                        if($data['picture'] != "")
                        {
                            if(file_exists($this->picturepath.$data['picture']))
                            {
                                unlink($this->picturepath.$data['picture']);
                            }
                        }

                        $this->addStandalone('sub',$id_user);

                        //remove the person
                        $cmd=$this->db->createCommand(SQL::SQL_DELETE_PERSON);
                        $cmd->bindParameter(":id",$id_user);
                        $cmd->execute();

                        //remove the access group attribution
                        $cmd=$this->db->createCommand(SQL::SQL_DELETE_ALL_GROUP_ATTRIBUTION);
                        $cmd->bindParameter(":id",$id_user);
                        $cmd->execute();

                        //set the key used to unsused
                        $cmd=$this->db->createCommand(SQL::SQL_UPDATE_KEYS_FOR_IDPERSON);
                        $cmd->bindParameter(":id",$id_user);
                        $cmd->execute();


                        //remove the tag attribution
                        $cmd=$this->db->createCommand(SQL::SQL_DELETE_KEY_ATTRIBUTION_FROM_IDPERSON);
                        $cmd->bindParameter(":id",$id_user);
                        $cmd->execute();


                        //alow to clean extended data handled by a component
                        $sql = "SELECT * FROM hr_user_action WHERE type='module'";
                        $cmd=$this->db->createCommand($sql);
                        $data=$cmd->query();
                        $data = $data->readAll();

                        for($i=0; $i<count($data); $i++)
                        {
                            try
                            {
                                Prado::using('horux.pages.'.$data[$i]['page']);
                                $class = $data[$i]['name'];
                                $sa = new $class();
                                $sa->cleanData($this->db, $id_user);
                            }
                            catch(Exception $e)
                            {
                                //! do noting
                            }
                        }

                        $nDelete++;
                    }
                }
            }

            if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
            else
            $pBack = array('okMsg'=>Prado::localize('{n} user(s) was deleted',array('n'=>$nDelete)));
            $this->Response->redirect($this->Service->constructUrl('user.UserList',$pBack));
        }


        public function onEdit($sender,$param)
        {
            if(count($this->DataGrid->DataKeys) === 0)
            {
                $pBack = array('koMsg'=>Prado::localize('Select one item'));
                $this->Response->redirect($this->Service->constructUrl('user.UserList',$pBack));

            }

            $id = $this->DataGrid->DataKeys[$param->Item->ItemIndex];

            if(is_numeric($id))
            {
                $pBack = array('id'=>$id);
                $this->Response->redirect($this->Service->constructUrl('user.mod',$pBack));
            }

            $cbs = $this->findControlsByType("TActiveCheckBox");
            $nDelete = 0;
            foreach($cbs as $cb)
            {
                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {
                    $pBack = array('id'=>$cb->Value);
                    $this->Response->redirect($this->Service->constructUrl('user.mod',$pBack));
                }
            }

            $pBack = array('koMsg'=>Prado::localize('Select one item'));
            $this->Response->redirect($this->Service->constructUrl('user.UserList',$pBack));
        }


        public function setKeys($sender,$param)
        {
            $id = $sender->Text;
            $this->Response->redirect($this->Service->constructUrl('user.attribution',array('id'=>$id)));
        }

        public function setGroups($sender,$param)
        {
            $id = $sender->Text;
            $this->Response->redirect($this->Service->constructUrl('user.groups',array('id'=>$id)));
        }

    }
    ?>
