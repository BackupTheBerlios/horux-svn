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

spl_autoload_unregister(array('Prado','autoload'));

spl_autoload_register(array('Prado','autoload'));

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
        $test = $session['format']." | ".$session['csv_terminated']." | ".$session['csv_enclosed']." | ".$session['csv_escaped']." | ".$session['csv_new_line']." | ".$session['csv_col_names']."<br />";

        $idRow = -1;
        $fname = "./tmp/".$session['upfile'];
        if(is_file($fname) && $session['tbl_name'] != "hr_") {
            $handle = fopen ($fname,"r");

            $data = array();
            $colNames;

            while ($row = fgetcsv($handle, 1000, $session['csv_terminated'], $session['csv_enclosed'], $session['csv_escaped']))
            {
                if ($idRow >= 0) {
                    $idCol = 0;
                    foreach ($colNames as $col) {
                        if ($row[0] != "")
                            $data[$idRow][$col] = $row[$idCol++];
                    }
                }
                else
                    $colNames = $row;

            //mysql_query("INSERT INTO matable(`id`, `marque`, `cartouche`, `Descriptif`, `Imprimante`, `fournisseur`, `url`) VALUES('".$row."', '".$data[0]."', '".$data[1]."', '".$data[2]."', '".$data[3]."', '".$data[4]."', '".$data[5]."')");

              $idRow++;
            }

            fclose ($handle);

            $this->DataGrid->DataSource=$data;
            $this->DataGrid->dataBind();
            $this->fname = $fname;
            $this->data = $data;
            $this->colNames = $colNames;
            $this->tblName = $session['tbl_name'];
        }
        else {
            $msg = array('koMsg'=>Prado::localize('Can\'t import the file!'));
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
            if (!$firstCol) {
                $cmdP1 .= ", ";
                $cmdP2 .= ", ";
            }
            else
                $firstCol = 0;
            $cmdP1 .= "`".$col."`";
            $cmdP2 .= ":".$col;
        }
        $cmdP1 .= ") ";
        $cmdP2 .= ")";

        // Add the values to import with the created command
        $errorMsg = "";
        foreach ($this->data as $row) {
            $cmd=$this->db->createCommand($cmdP1.$cmdP2);
            foreach ($this->colNames as $col) {
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
