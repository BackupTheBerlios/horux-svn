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

class importData extends PageList {

    protected $fname = '';
    protected $data;
    protected $colNames;
    protected $tblName;

    public function onInit($param) {
        $this->displayMessage(Prado::localize('Here are the imported data! You can choose to save or cancel the import..'), true);
    }

    public function onLoad($param) {
        parent::onLoad($param);

        $session = Prado::getApplication()->getSession();

        $selected_conf = $session['selected_conf'];
        if ($selected_conf != "") {
            $cmd = $this->db->createCommand( SQL::SQL_GET_IMPORT );
            $cmd->bindValue(":id",$selected_conf, PDO::PARAM_INT);
            $query = $cmd->query();
            if($query) {
                $data = $query->read();
                $csv_terminated = $data['terminated_by'];
                $csv_enclosed = $data['enclosed_by'];
                $csv_escaped = $data['escaped_by'];
                $tb_name = $data['tb_name'];
                $cols = $data['cols'];
            }
        }

        // default values (TODO: should perhaps be declare as const...)
        if ($csv_terminated == "" || $csv_enclosed == "" || $csv_escaped == "") {
            $csv_terminated = ',';
            $csv_enclosed = '"';
            $csv_escaped = '\\';
        }
        if ($tb_name == "")
            $tb_name = $session['tbl_name'];
        if ($cols != "")
            $colNames = str_getcsv($cols);

        $idRow = -1;
        $fname = "./tmp/".$session['upfile'];
        if(is_file($fname) && $tb_name != "hr_") {
            $handle = fopen ($fname,"r");

            $data = array();
            $colNames;

            while ($row = fgetcsv($handle, 1000, $csv_terminated, $csv_enclosed, $csv_escaped))
            {
                if ($idRow >= 0 || $cols != "") {
                    $idCol = 0;
                    foreach ($colNames as $col) {
                        if ($row[0] != "") {
                            if ($col == "")
                                $idCol++;
                            else {
                                $row[$idCol] = stripcslashes($row[$idCol]);
                                $data[$idRow][$col] = $row[$idCol++];
                            }
                        }
                    }
                }
                else
                    $colNames = $row;

              $idRow++;
            }

            fclose ($handle);

            $this->DataGrid->DataSource=$data;
            $this->DataGrid->dataBind();
            $this->fname = $fname;
            $this->data = $data;
            $this->colNames = $colNames;
            $this->tblName = $tb_name;
        }
        else {
            $msg = array('koMsg'=>Prado::localize('Can\'t import the file!').$session['selected_conf']);
            $this->Response->redirect($this->Service->constructUrl('components.export.import',$msg));
        }

    }

    public function onCancel($sender, $param) {
        if(is_file($this->fname))
            unlink($this->fname);
        $this->Response->redirect($this->Service->constructUrl('components.export.import'));
    }

    public function onSave($sender, $param) {
        if(is_file($this->fname))
            unlink($this->fname);

        // Create the command
        $cmdP1 = "INSERT INTO ".$this->tblName." (";
        $cmdP2 = "VALUES (";
        $firstCol = 1;
        foreach ($this->colNames as $col) {
            if ($col != "") {
                if (!$firstCol) {
                    $cmdP1 .= ", ";
                    $cmdP2 .= ", ";
                }
                else
                    $firstCol = 0;
                $cmdP1 .= "`".$col."`";
                $cmdP2 .= ":".$col;
            }
        }
        $cmdP1 .= ") ";
        $cmdP2 .= ")";

        // Add the values to import with the created command
        $errorMsg = "";
        foreach ($this->data as $row) {
            $cmd=$this->db->createCommand($cmdP1.$cmdP2);
            foreach ($this->colNames as $col) {
                if ($col != "")
                    $cmd->bindValue(":".$col,$row[$col],PDO::PARAM_STR);
            }
            try {
                if(!$cmd->execute())
                    $errorMsg = "Can't execute the cmd";
            }
            catch(Exception $e) {
                $errorMsg = $e->getMessage();
            }
        }

        if($errorMsg != "")
            $msg = array('koMsg'=>Prado::localize('The import has not been saved! (').$errorMsg.")");
        else
            $msg = array('okMsg'=>Prado::localize('The import has been saved!'));
        $this->Response->redirect($this->Service->constructUrl('components.export.import', $msg));
    }
}
?>
