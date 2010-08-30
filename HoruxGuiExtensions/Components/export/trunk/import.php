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
        parent::onLoad($param);

        if(isset($this->Request['okMsg']))
        {
            $this->displayMessage($this->Request['okMsg'], true);
        }
        if(isset($this->Request['koMsg']))
        {
            $this->displayMessage($this->Request['koMsg'], false);
        }
    }

    public function onImport($sender, $param) {
        $session = Prado::getApplication()->getSession();
        $session['format'] = $this->format->Value;
        $session['csv_terminated'] = $this->csv_terminated->SafeText;
        $session['csv_enclosed'] = $this->csv_enclosed->SafeText;
        $session['csv_escaped'] = $this->csv_escaped->SafeText;
        $session['tbl_name'] = $this->tbl_name->SafeText;
        //W$session['csv_new_line'] = $this->csv_new_line->SafeText;
        //$session['csv_col_names'] = $this->csv_col_names->Value;
        $this->file->saveAs("./tmp/".$this->file->Filename);
        $session['upfile'] = $this->file->FileName;


        $this->Response->redirect($this->Service->constructUrl('components.export.importData'));

       /* $msg = array('okMsg'=>Prado::localize('...'));
        $this->Response->redirect($this->Service->constructUrl('components.export.import',$msg));*/
    }
}
?>
