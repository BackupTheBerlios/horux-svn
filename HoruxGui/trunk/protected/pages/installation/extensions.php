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

class extensions extends Page
{
    protected $isUploadFileOk;


    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!is_writeable('.'.DIRECTORY_SEPARATOR.'tmp'))
        {
            $this->displayMessage(Prado::localize('The directory ./tmp must be writeable to install an extension'), false);
        }

        if(!$this->isPostBack)
        {
            $this->installButton->setCssClass('active');
            $this->componentsButton->setCssClass('');
            $this->templatesButton->setCssClass('');
            $this->languagesButton->setCssClass('');
            $this->tbb->uninstall->setVisible(false);

            $this->InstallMultiView->ActiveView = $this->InstallView;

            $param = $this->Application->getParameters();
            $superAdmin = $this->Application->getUser()->getSuperAdmin();

            if($param['appMode'] == 'demo' && $superAdmin == 0)
            {
                $this->tbb->uninstall->setEnabled(false);
            }
        }


        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
        }

        if( isset($this->Request['view']) )
        {
            $this->installButton->setCssClass('');
            $this->componentsButton->setCssClass('');
            $this->templatesButton->setCssClass('');
            $this->languagesButton->setCssClass('');

            switch($this->Request['view'])
            {
                case 'install':
                    $this->installButton->setCssClass('active');
                    $this->InstallMultiView->ActiveView = $this->InstallView;
                    break;
                case 'components':
                    $this->componentDataGrid->DataSource=$this->getComponent();
                    $this->componentDataGrid->dataBind();
                    $this->componentsButton->setCssClass('active');
                    $this->InstallMultiView->ActiveView = $this->ComponentsView;
                    $this->tbb->uninstall->setVisible(true);
                    break;
                case 'templates':
                    $this->templateDataGrid->DataSource=$this->getTemplateList();
                    $this->templateDataGrid->dataBind();
                    $this->templatesButton->setCssClass('active');
                    $this->InstallMultiView->ActiveView = $this->TemplatesView;
                    $this->tbb->uninstall->setVisible(true);
                    break;
                case 'languages':
                    $this->languageDataGrid->DataSource=$this->getLanguage();
                    $this->languageDataGrid->dataBind();
                    $this->languagesButton->setCssClass('active');
                    $this->InstallMultiView->ActiveView = $this->LanguagesView;
                    $this->tbb->uninstall->setVisible(true);
                    break;
                case 'devices':
                    $this->devicesDataGrid->DataSource=$this->getDevices();
                    $this->devicesDataGrid->dataBind();
                    $this->devicesButton->setCssClass('active');
                    $this->InstallMultiView->ActiveView = $this->DevicesView;
                    $this->tbb->uninstall->setVisible(true);
                    break;
            }
        }
    }

    public function onUninstall($sender, $param)
    {
        $controlId = $this->InstallMultiView->getActiveView()->getID();

        if($controlId == $this->ComponentsView->getID())
        $this->unInstallComponent();

        if($controlId == $this->TemplatesView->getID())
        $this->unInstallTemplate();

        if($controlId == $this->LanguagesView->getID())
        $this->unInstallLanguage();

        if($controlId == $this->DevicesView->getID())
        $this->unInstallDevices();

    }

    protected function unInstallComponent()
    {
        $ids = array();

        foreach($this->Request as $k=>$v)
        {
            $control = $this->convertUniqueIdToClientId($k);
            if($control != $k)
            {
                $control  = explode('_', $control);
                if($control[4] == 'item')
                $ids[] = $v;
            }

        }
        $nDelete = 0;
        $koMsg = '';
        if(count($ids)==0)
        {
            $koMsg = Prado::localize('Select one item');
        }
        else
        {
            foreach($ids as $id)
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE id=:id AND type='component'");
                $cmd->bindValue(":id",$id);
                $data = $cmd->query();
                $data = $data->read();

                $name = $data['name'];

                $cmd=$this->db->createCommand("DELETE FROM hr_install WHERE id=:id AND type='component'");
                $cmd->bindValue(":id",$id);
                $cmd->execute();

                $cmd=$this->db->createCommand("DELETE FROM hr_component WHERE id_install=:id");
                $cmd->bindValue(":id",$id);
                $cmd->execute();

                $doc=new TXmlDocument();
                $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.''.$name.DIRECTORY_SEPARATOR.'install.xml');

                $sqluninstall =  $doc->getElementByTagName('sqluninstall');

                if($sqluninstall)
                $this->sqlUninstall('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.''.$name.DIRECTORY_SEPARATOR.$sqluninstall->getValue());


                $permissions = $doc->getElementByTagName('permissions');
                $permissions = $permissions->getElements();
                foreach($permissions as $perm)
                {
                    $cmd=$this->db->createCommand("DELETE FROM hr_gui_permissions WHERE page=:page");
                    $cmd->bindValue(":page", $perm->getValue());
                    $cmd->execute();
                }


                $nDelete++;

                $this->recursive_remove_directory('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$name);


                //! remove language file if existing
                $this->removeLanguagesFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages',$name);
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg,'view'=>'components');
        else
        $pBack = array('okMsg'=>Prado::localize('{n} components was deleted',array('n'=>$nDelete)),'view'=>'components');

        $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
    }

    protected function unInstallDevices()
    {
        $ids = array();

        foreach($this->Request as $k=>$v)
        {
            $control = $this->convertUniqueIdToClientId($k);
            if($control != $k)
            {
                $control  = explode('_', $control);
                if($control[4] == 'item')
                $ids[] = $v;
            }

        }
        $nDelete = 0;
        $koMsg = '';
        if(count($ids)==0)
        {
            $koMsg = Prado::localize('Select one item');
        }
        else
        {
            foreach($ids as $id)
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE id=:id AND type='device'");
                $cmd->bindValue(":id",$id);
                $data = $cmd->query();
                $data = $data->read();

                $param = $data['param'];

                $cmd=$this->db->createCommand("DELETE FROM hr_install WHERE id=:id AND type='device'");
                $cmd->bindValue(":id",$id);
                $cmd->execute();

                $cmd=$this->db->createCommand("SELECT * FROM hr_".$param);
                $data = $cmd->query();
                $data = $data->readAll();

                foreach($data as $d)
                {
                    $deviceId = $d['id_device'];

                    $cmd=$this->db->createCommand("DELETE FROM hr_device WHERE id=".$deviceId);
                    $cmd->execute();

                    $cmd=$this->db->createCommand("DELETE FROM hr_user_group_access WHERE id_device=".$deviceId);
                    $cmd->execute();

                }


                $doc=new TXmlDocument();
                $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.''.$param.DIRECTORY_SEPARATOR.'install.xml');

                $sqluninstall =  $doc->getElementByTagName('sqluninstall');

                if($sqluninstall)
                $this->sqlUninstall('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$param.DIRECTORY_SEPARATOR.$sqluninstall->getValue());


                $permissions = $doc->getElementByTagName('permissions');
                $permissions = $permissions->getElements();
                foreach($permissions as $perm)
                {
                    $cmd=$this->db->createCommand("DELETE FROM hr_gui_permissions WHERE page=:page");
                    $cmd->bindValue(":page", $perm->getValue());
                    $cmd->execute();
                }


                $nDelete++;

                $this->recursive_remove_directory('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$param);

                //! remove language file if existing
                $this->removeLanguagesFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages',$param);


            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg,'view'=>'devices');
        else
        $pBack = array('okMsg'=>Prado::localize('{n} devices was deleted',array('n'=>$nDelete)),'view'=>'components');

        $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
    }


    protected function unInstallTemplate()
    {
        $ids = array();

        foreach($this->Request as $k=>$v)
        {
            $control = $this->convertUniqueIdToClientId($k);
            if($control != $k)
            {
                $control  = explode('_', $control);
                if($control[4] == 'item')
                $ids[] = $v;
            }

        }
        $nDelete = 0;
        $koMsg = '';
        if(count($ids)==0)
        {
            $koMsg = Prado::localize('Select one item');
        }
        else
        {
            foreach($ids as $id)
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE id=:id AND type='template'");
                $cmd->bindValue(":id",$id);
                $data = $cmd->query();
                $data = $data->read();

                $name = $data['name'];

                $cmd=$this->db->createCommand("DELETE FROM hr_install WHERE id=:id AND type='template'");
                $cmd->bindValue(":id",$id);
                $cmd->execute();
                $nDelete++;

                $this->recursive_remove_directory('.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$name);
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg,'view'=>'templates');
        else
        $pBack = array('okMsg'=>Prado::localize('{n} themes was deleted',array('n'=>$nDelete)),'view'=>'templates');

        $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
    }

    protected function unInstallLanguage()
    {
        $ids = array();

        foreach($this->Request as $k=>$v)
        {
            $control = $this->convertUniqueIdToClientId($k);
            if($control != $k)
            {
                $control  = explode('_', $control);
                if($control[4] == 'item')
                $ids[] = $v;
            }

        }
        $nDelete = 0;
        $koMsg = '';
        if(count($ids)==0)
        {
            $koMsg = Prado::localize('Select one item');
        }
        else
        {
            foreach($ids as $id)
            {
                $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE id=:id AND type='language'");
                $cmd->bindValue(":id",$id);
                $data = $cmd->query();
                $data = $data->read();

                $name = $data['param'];
                $default = $data['default'];

                // if the language was the default, set the english as default
                if($default)
                {
                    $cmd=$this->db->createCommand("UPDATE hr_install SET `default`='1' WHERE param='en' AND name='English'");
                    $cmd->bindValue(":id",$cb->Value);
                    $cmd->execute();

                    $this->Session['lang'] ='en';
                    $this->getApplication()->getGlobalization()->setCulture($this->Session['lang']);

                }

                $cmd=$this->db->createCommand("DELETE FROM hr_install WHERE id=:id AND type='language'");
                $cmd->bindValue(":id",$id);
                $cmd->execute();
                $nDelete++;

                $this->recursive_remove_directory('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$name);
            }
        }

        if($koMsg !== '')
        $pBack = array('koMsg'=>$koMsg,'view'=>'languages');
        else
        $pBack = array('okMsg'=>Prado::localize('{n} language was deleted',array('n'=>$nDelete)),'view'=>'languages');

        $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
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

    public function getTemplateList()
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='template' ORDER BY system");
        $data = $cmd->query();
        $data = $data->readAll();

        $template = array();

        foreach($data as $d)
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$d['name'].DIRECTORY_SEPARATOR.'install.xml');

            $version = $doc->getElementByTagName('version');
            $date = $doc->getElementByTagName('creationDate');
            $description = $doc->getElementByTagName('description');
            $author = $doc->getElementByTagName('author');
            $license = $doc->getElementByTagName('license');

            $template[] = array('id' => $d['id'],
                                'name' => $d['name'],
                                'default' => $d['default'],
                                'system' => $d['system'],
                                'version' => $version->getValue(),
                                'date' => $date->getValue(),
                                'description' => $description->getValue(),
                                'author' => $author->getValue(),
                                'license' => $license->getValue()
            );
        }

        return $template;
    }

    public function getLanguage()
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='language' ORDER BY system");
        $data = $cmd->query();
        $data = $data->readAll();

        $template = array();

        foreach($data as $d)
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$d['param'].DIRECTORY_SEPARATOR.'install.xml');

            $version = $doc->getElementByTagName('version');
            $date = $doc->getElementByTagName('creationDate');
            $description = $doc->getElementByTagName('description');
            $author = $doc->getElementByTagName('author');
            $license = $doc->getElementByTagName('license');

            $template[] = array('id' => $d['id'],
                                'name' => $d['name'],
                                'default' => $d['default'],
                                'system' => $d['system'],
                                'version' => $version->getValue(),
                                'date' => $date->getValue(),
                                'description' => $description->getValue(),
                                'author' => $author->getValue(),
                                'license' => $license->getValue()
            );
        }

        return $template;
    }

    public function getDevices()
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='device' ORDER BY system");
        $data = $cmd->query();
        $data = $data->readAll();

        $template = array();

        foreach($data as $d)
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$d['param'].DIRECTORY_SEPARATOR.'install.xml');

            $version = $doc->getElementByTagName('version');
            $date = $doc->getElementByTagName('creationDate');
            $description = $doc->getElementByTagName('description');
            $author = $doc->getElementByTagName('author');
            $license = $doc->getElementByTagName('license');

            $template[] = array('id' => $d['id'],
                                'name' => $d['name'],
                                'default' => $d['default'],
                                'system' => $d['system'],
                                'version' => $version->getValue(),
                                'date' => $date->getValue(),
                                'description' => $description->getValue(),
                                'author' => $author->getValue(),
                                'license' => $license->getValue()
            );
        }

        return $template;

    }

    public function getComponent()
    {
        $cmd=$this->db->createCommand("SELECT * FROM hr_install WHERE type='component' ORDER BY system");
        $data = $cmd->query();
        $data = $data->readAll();

        $template = array();

        foreach($data as $d)
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$d['name'].DIRECTORY_SEPARATOR.'install.xml');

            $version = $doc->getElementByTagName('version');
            $date = $doc->getElementByTagName('creationDate');
            $description = $doc->getElementByTagName('description');
            $author = $doc->getElementByTagName('author');
            $license = $doc->getElementByTagName('license');

            $template[] = array('id' => $d['id'],
                                'name' => $d['name'],
                                'system' => $d['system'],
                                'version' => $version->getValue(),
                                'date' => $date->getValue(),
                                'description' => $description->getValue(),
                                'author' => $author->getValue(),
                                'license' => $license->getValue()
            );
        }

        return $template;
    }

    public function onInstall($sender, $param)
    {
    }

    public function fileUploaded($sender,$param)
    {
        if(!is_writeable('.'.DIRECTORY_SEPARATOR.'tmp')) return;
        if($this->fileUpload->HasFile)
        {
            $path_info = pathinfo($this->fileUpload->fileName);
            if(strtolower($path_info['extension']) == 'zip')
            {
                if($this->fileUpload->saveAs('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName))
                {
                    if($this->unzip('..'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName, '.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR))
                    {
                        $type = $this->checkInstall(basename($this->fileUpload->fileName, '.zip'));
                        if($type == 'component' ||
                            $type == 'language' ||
                            $type == 'template' ||
                            $type == 'device')
                        {

                            $this->recursive_remove_directory('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName, '.zip'));

                            if($type == 'component')
                            {

                                if(!is_writeable('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'))
                                {
                                    unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('The directory ./protected/pages/components must be writeable to install a component extension'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                if(file_exists('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName, '.zip')))
                                {
                                    unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('Warning! - This component is already installed'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                rename('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName), '.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.''.basename($this->fileUpload->fileName));

                                $this->unzip('..'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.''.$this->fileUpload->fileName, '.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'');
                                unlink('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName);
                                $this->installComponent(basename($this->fileUpload->fileName, '.zip'));
                                $pBack = array('okMsg' => Prado::localize('The component is well installed. You have now to give the right access for this component.'));
                                $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));

                            }

                            if($type == 'language')
                            {
                                if(!is_writeable('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'))
                                {
                                    unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('The directory ./protected/messages must be writeable to install a language extension'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                if(file_exists('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName, '.zip')))
                                {
                                    unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('Warning! - This language is already installed'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                rename('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName), '.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));

                                $this->unzip('..'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName, '.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR);
                                unlink('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName);
                                $this->installLanguage(basename($this->fileUpload->fileName, '.zip'));
                                $pBack = array('okMsg' => Prado::localize('The language is well installed.'));
                                $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                            }

                            if($type == 'template')
                            {

                                if(!is_writeable('.'.DIRECTORY_SEPARATOR.'themes'))
                                {
                                    unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('The directory ./themes must be writeable to install a template extension'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                if(file_exists('.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName, '.zip')))
                                {
                                    unlink('..'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('Warning! - This template is already installed'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                rename('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName), '.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));

                                $this->unzip('..'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName, '.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR);
                                unlink('.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName);
                                $this->installTemplate(basename($this->fileUpload->fileName, '.zip'));
                                $pBack = array('okMsg' => Prado::localize('The theme is well installed.'));
                                $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                            }

                            if($type == 'device')
                            {

                                if(!is_writeable('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'))
                                {
                                    unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('The directory ./protected/pages/hardware/device must be writeable to install a device extension'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                if(file_exists('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName, '.zip')))
                                {
                                    unlink('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));
                                    $pBack = array('koMsg' => Prado::localize('Warning! - This device is already installed'));
                                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                                }

                                rename('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName), '.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.basename($this->fileUpload->fileName));

                                $this->unzip('..'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName, '.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.'');
                                unlink('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$this->fileUpload->fileName);
                                $this->installDevice(basename($this->fileUpload->fileName, '.zip'));
                                $pBack = array('okMsg' => Prado::localize('The device is well installed. You have now to give the right access for this device.'));
                                $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));

                            }


                        }
                        else
                        {
                            $pBack = array('koMsg' => Prado::localize('Warning! - Unknown install type:{type}',array('type'=>$type)));
                            $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
                        }
                    }
                    else
                    {
                        $pBack = array('koMsg' => Prado::localize('Warning! - Failed to move file. Impossible to unzip the archive'));
                        $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));

                    }
                }
                else
                {
                    $pBack = array('koMsg' => Prado::localize('Warning! - Failed to move file. Cannot save the archive'));
                    $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));

                }
            }
            else
            {
                $pBack = array('koMsg' => Prado::localize('Warning! - Failed to move file. Unknown Archive Type'));
                $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
            }

        }
        else
        {
            $pBack = array('koMsg' => Prado::localize('There was an error uploading this file to the server.'));
            $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
        }
    }

    protected function checkInstall($dir)
    {
        if(file_exists('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'install.xml'))
        {
            $doc=new TXmlDocument();
            $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR.'install.xml');

            $type = $doc->getAttribute('type');

            return $type;
        }
        else
        {
            $this->recursive_remove_directory('.'.DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$dir);
            $pBack = array('koMsg' => Prado::localize('This archive does not contain the file install.xml'));
            $this->Response->redirect($this->Service->constructUrl('installation.extensions',$pBack));
        }
    }

    function recursive_remove_directory($directory, $empty=FALSE)
    {
        // if the path has a slash at the end we remove it here
        if(substr($directory,-1) == DIRECTORY_SEPARATOR)
        {
            $directory = substr($directory,0,-1);
        }

        // if the path is not valid or is not a directory ...
        if(!file_exists($directory) || !is_dir($directory))
        {
            // ... we return false and exit the function
            return FALSE;

            // ... if the path is not readable
        }elseif(!is_readable($directory))
        {
            // ... we return false and exit the function
            return FALSE;

            // ... else if the path is readable
        }else{

            // we open the directory
            $handle = opendir($directory);

            // and scan through the items inside
            while (FALSE !== ($item = readdir($handle)))
            {
                // if the filepointer is not the current directory
                // or the parent directory
                if($item != '.' && $item != '..')
                {
                    // we build the new path to delete
                    $path = $directory.DIRECTORY_SEPARATOR.$item;

                    // if the new path is a directory
                    if(is_dir($path))
                    {
                        // we call this function with the new path
                        $this->recursive_remove_directory($path);

                        // if the new path is a file
                    }else{
                        // we remove the file
                        unlink($path);
                    }
                }
            }
            // close the directory
            closedir($handle);

            // if the option to empty is not set to true
            if($empty == FALSE)
            {
                // try to delete the now empty directory
                if(!rmdir($directory))
                {
                    // return false if not possible
                    return FALSE;
                }
            }
            // return success
            return TRUE;
        }
    }

    protected function unzip($zipfile, $path)
    {
        $zipfile = $this->application->getBasePath().DIRECTORY_SEPARATOR.$zipfile;
        $zip = zip_open($zipfile);
        while ($zip_entry = zip_read($zip))    {
            zip_entry_open($zip, $zip_entry);
            if (substr(zip_entry_name($zip_entry), -1) == '/') {
                $zdir = $path.substr(zip_entry_name($zip_entry), 0, -1);
                if (file_exists($zdir)) {
                    trigger_error('Directory "<b>.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$zdir . '</b>" exists', E_USER_ERROR);
                    return false;
                }
                mkdir($zdir);
            }
            else {
                $name = $path.zip_entry_name($zip_entry);
                if (file_exists($name)) {
                    trigger_error('File "<b>' . $name . '</b>" exists', E_USER_ERROR);
                    return false;
                }
                $fopen = fopen($name, "w");
                fwrite($fopen, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)), zip_entry_filesize($zip_entry));
            }
            zip_entry_close($zip_entry);
        }
        zip_close($zip);
        return true;
    }

    protected function installLanguage($langName)
    {
        $doc=new TXmlDocument();
        $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.''.$langName.DIRECTORY_SEPARATOR.'install.xml');

        $installName = $doc->getElementByTagName('installName');
        $name = $doc->getElementByTagName('name');

        $cmd = $this->db->createCommand( "INSERT INTO hr_install (`name`,`type`,`system`, `default`, `param`) VALUES (:name, 'language',0,0,:installName)" );
        $cmd->bindValue(":name",$name->getValue(),PDO::PARAM_STR);
        $cmd->bindValue(":installName",$installName->getValue(),PDO::PARAM_STR);
        $cmd->execute();
    }

    protected function installTemplate($tempName)
    {
        $doc=new TXmlDocument();
        $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'themes'.DIRECTORY_SEPARATOR.$tempName.DIRECTORY_SEPARATOR.'install.xml');

        $installName = $doc->getElementByTagName('installName');

        $cmd = $this->db->createCommand( "INSERT INTO hr_install (`name`,`type`,`system`, `default`, `param`) VALUES (:installName, 'template',0,0,'')" );
        $cmd->bindValue(":installName",$installName->getValue(),PDO::PARAM_STR);
        $cmd->execute();
    }

    protected function installDevice($deviceName)
    {
        $doc=new TXmlDocument();
        $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$deviceName.DIRECTORY_SEPARATOR.'install.xml');

        $installName = $doc->getElementByTagName('installName');
        $name = $doc->getElementByTagName('name');
        $sqlinstall =  $doc->getElementByTagName('sqlinstall');

        if($sqlinstall)
        $this->sqlInstall('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$deviceName.DIRECTORY_SEPARATOR.$sqlinstall->getValue());

        $cmd = $this->db->createCommand( "INSERT INTO hr_install (`name`,`type`,`system`, `default`, `param`) VALUES (:name, 'device',0,0,:installName)" );
        $cmd->bindValue(":installName",$installName->getValue(),PDO::PARAM_STR);
        $cmd->bindValue(":name",$name->getValue(),PDO::PARAM_STR);
        $cmd->execute();

        $this->moveLanguageDirectory('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'hardware'.DIRECTORY_SEPARATOR.'device'.DIRECTORY_SEPARATOR.$deviceName.DIRECTORY_SEPARATOR,$installName->getValue());

        return true;

    }

    protected function installComponent($compName)
    {
        $doc=new TXmlDocument();
        $doc->loadFromFile('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.''.$compName.DIRECTORY_SEPARATOR.'install.xml');

        $installName = $doc->getElementByTagName('installName');
        $sqlinstall =  $doc->getElementByTagName('sqlinstall');

        if($sqlinstall)
        $this->sqlInstall('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$compName.DIRECTORY_SEPARATOR.$sqlinstall->getValue());

        $cmd = $this->db->createCommand( "INSERT INTO hr_install (`name`,`type`,`system`, `default`, `param`) VALUES (:name, 'component',0,0,'')" );
        $cmd->bindValue(":name",$installName->getValue(),PDO::PARAM_STR);
        $cmd->execute();

        $lastId = $this->db->getLastInsertId();

        $mainmenu = $doc->getElementByTagName('mainmenu');
        $mainmenu = $mainmenu->getElements();
        foreach($mainmenu as $menu)
        {
            $iconmenu = $menu->getAttribute('iconmenu');
            $url =  $menu->getAttribute('url');
            $menuname = $menu->getAttribute('name');
            $parentmenu = 0;

            $cmd = $this->db->createCommand( "INSERT INTO hr_component (`id_install`,`parentmenu`,`menuname`, `page`, `iconmenu`) VALUES (:lastId, :parentmenu,:menuname,:url,:iconmenu)" );
            $cmd->bindValue(":lastId",$lastId,PDO::PARAM_INT);
            $cmd->bindValue(":iconmenu",$iconmenu,PDO::PARAM_STR);
            $cmd->bindValue(":url",$url,PDO::PARAM_STR);
            $cmd->bindValue(":menuname",$menuname,PDO::PARAM_STR);
            $cmd->bindValue(":parentmenu",$parentmenu,PDO::PARAM_INT);
            $cmd->execute();

            if($menu->getHasElement())
            {
                $submenu = $menu->getElementByTagName('submenus');
                $submenu = $submenu->getElements();
                $lastMenuId = $this->db->getLastInsertId();

                foreach($submenu as $smenu)
                {
                    $iconmenu = $smenu->getAttribute('iconmenu');
                    $url =  $smenu->getAttribute('url');
                    $menuname = $smenu->getAttribute('name');

                    $cmd = $this->db->createCommand( "INSERT INTO hr_component (`id_install`,`parentmenu`,`menuname`, `page`, `iconmenu`) VALUES (:lastId, :parentmenu,:menuname,:url,:iconmenu)" );
                    $cmd->bindValue(":lastId",$lastId,PDO::PARAM_INT);
                    $cmd->bindValue(":iconmenu",$iconmenu,PDO::PARAM_STR);
                    $cmd->bindValue(":url",$url,PDO::PARAM_STR);
                    $cmd->bindValue(":menuname",$menuname,PDO::PARAM_STR);
                    $cmd->bindValue(":parentmenu",$lastMenuId,PDO::PARAM_INT);
                    $cmd->execute();
                }

            }
        }

        $this->moveLanguageDirectory('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.''.$compName.DIRECTORY_SEPARATOR,$installName->getValue());

        return true;
    }

    protected function sqlInstall($filename)
    {
        if(!(@$buffer = file_get_contents($filename)) )
        {
            return false;
        }

        $queries = $this->splitSql($buffer);


        foreach ($queries as $query)
        {
            $query = trim($query);
            if ($query != '' && $query {0} != '#')
            {
                $cmd = $this->db->createCommand($query);
                $cmd->execute();
            }
        }
    }

    protected function sqlUninstall($filename)
    {
        if(!(@$buffer = file_get_contents($filename)) )
        {
            return false;
        }

        $queries = $this->splitSql($buffer);


        foreach ($queries as $query)
        {
            $query = trim($query);
            if ($query != '' && $query {0} != '#')
            {
                $cmd = $this->db->createCommand($query);
                $cmd->execute();
            }
        }
    }

    protected function splitSql($sql)
    {
        $sql = trim($sql);
        $sql = preg_replace("/\n\#[^\n]*/", '', "\n".$sql);
        $buffer = array ();
        $ret = array ();
        $in_string = false;

        for ($i = 0; $i < strlen($sql) - 1; $i ++) {
            if ($sql[$i] == ";" && !$in_string)
            {
                $ret[] = substr($sql, 0, $i);
                $sql = substr($sql, $i +1);
                $i = 0;
            }

            if ($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\")
            {
                $in_string = false;
            }
            elseif (!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset ($buffer[0]) || $buffer[0] != "\\"))
            {
                $in_string = $sql[$i];
            }
            if (isset ($buffer[1]))
            {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
        }

        if (!empty ($sql))
        {
            $ret[] = $sql;
        }
        return ($ret);

    }

    protected function removeLanguagesFile($directory, $langFile)
    {
        $langFile .= '.xml';

        $handle = opendir($directory);

        // and scan through the items inside
        while (FALSE !== ($item = readdir($handle)))
        {
            // if the filepointer is not the current directory
            // or the parent directory
            if($item != '.' && $item != '..')
            {
                // we build the new path to delete
                $path = $directory.DIRECTORY_SEPARATOR.$item;

                // if the new path is a directory
                if(is_dir($path))
                {
                    if(file_exists($path.DIRECTORY_SEPARATOR.$langFile))
                    unlink($path.DIRECTORY_SEPARATOR.$langFile);
                }
            }
        }

        closedir($handle);
    }

    protected function moveLanguageDirectory($basePath, $langFile)
    {
        if(file_exists($basePath.'lang'))
        {

            $handle = opendir($basePath.'lang');
            // and scan through the items inside
            while (FALSE !== ($item = readdir($handle)))
            {
                // if the filepointer is not the current directory
                // or the parent directory
                if($item != '.' && $item != '..')
                {
                    // we build the new path to delete
                    $path = $basePath.'lang'.DIRECTORY_SEPARATOR.$item;

                    // if the new path is a directory
                    if(is_dir($path))
                    {
                        $src = $path.DIRECTORY_SEPARATOR.$langFile.'.xml';
                        $dest = '.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$item.DIRECTORY_SEPARATOR.$langFile.'.xml';

                        if(file_exists('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$item.DIRECTORY_SEPARATOR) &&
                            is_writeable('.'.DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'messages'.DIRECTORY_SEPARATOR.$item.DIRECTORY_SEPARATOR))
                        {
                            rename($src, $dest);
                        }
                    }

                }
            }
            closedir($handle);

            //!remove the language directory
            $this->recursive_remove_directory($basePath.'lang');
        }
    }

}

?>
