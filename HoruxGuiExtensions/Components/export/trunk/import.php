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

Prado::using('horux.pages.components.export.sql');

class import extends PageList {
    public function onLoad($param) {
        $this->getClientScript()->registerStyleSheetFile('treeCss','./protected/pages/components/export/assets/icon.css');

        parent::onLoad($param);

        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
        }

        if(!$this->IsPostBack) {
            $cmd = NULL;

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

        }
    }

    public function getData() {
        $cmd=$this->db->createCommand(SQL::SQL_GET_ALL_IMPORT);
        $data = $cmd->query();
        $data = $data->readAll();

        return $data;
    }

    public function onImport($sender, $param) {
        $session = Prado::getApplication()->getSession();
        $session['format'] = $this->format->Value;
        //////$session['csv_terminated'] = $this->csv_terminated->SafeText;
        //////$session['csv_enclosed'] = $this->csv_enclosed->SafeText;
        //////$session['csv_escaped'] = $this->csv_escaped->SafeText;
        $session['tbl_name'] = $this->tbl_name->SafeText;
        //W$session['csv_new_line'] = $this->csv_new_line->SafeText;
        //$session['csv_col_names'] = $this->csv_col_names->Value;
        $this->file->saveAs("./tmp/".$this->file->Filename);
        $session['upfile'] = $this->file->FileName;

        $cbs = $this->findControlsByType("TActiveCheckBox");
        $session['selected_conf'] = "";
        if ($this->custom_config->Checked)
            foreach($cbs as $cb)
                if( (bool)$cb->getChecked() && $cb->Value != "0")
                    $session['selected_conf'] = $cb->Value;

        $this->Response->redirect($this->Service->constructUrl('components.export.importData'));

       /* $msg = array('okMsg'=>Prado::localize('...'));
        $this->Response->redirect($this->Service->constructUrl('components.export.import',$msg));*/
    }

    public function onCheckChange($sender,$param)
    {
        if ($sender->Checked == "true")
            $this->DataGrid->SetStyle("display:inline;");
        else
            $this->DataGrid->SetStyle("display:none;");
    }

    public function onRadioChange($sender,$param)
    {
        $cbs = $this->findControlsByType("TActiveCheckBox");
        $isChecked = $sender->getChecked();

        if ($isChecked)
            foreach($cbs as $cb) {
                $cb->setChecked(!$isChecked);
            }
        $sender->setChecked($isChecked);
    }

    public function onDeleteConf($sender,$param)
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
            $koMsg = Prado::localize('Select one item', array(), "messages");
        }
        else
        {
            foreach($cbs as $cb)
            {
                if( (bool)$cb->getChecked() && $cb->Value != "0")
                {
                    $cmd=$this->db->createCommand(SQL::SQL_DELETE_IMPORT);
                    $cmd->bindValue(":id",$cb->Value);
                    if($cmd->execute())
                        $nDelete++;

                }
            }
        }

        if($koMsg !== '')
            $pBack = array('koMsg'=>$koMsg);
        else
            $pBack = array('okMsg'=>Prado::localize('{n} import configuration was deleted',array('n'=>$nDelete)));

        $this->Response->redirect($this->Service->constructUrl('components.export.import',$pBack));
    }
}
?>
