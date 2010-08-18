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

include_once( 'phpExcel/Classes/PHPExcel.php' );
include_once( 'phpExcel/Classes/PHPExcel/Writer/Excel2007.php' );
include_once( 'phpExcel/Classes/PHPExcel/Writer/PDF.php');

spl_autoload_register(array('Prado','autoload'));

class exportData extends PageList {

    protected $catches = array();
    protected $jsPrint = '';

    public function onInit($param) {
            $this->setFilter();
        
    }

    public function onLoad($param) {
        parent::onLoad($param);

        if(!$this->IsPostBack) {

            $this->DataGrid->DataSource=$this->Data;
            $this->DataGrid->dataBind();

        }

    }

    protected function setFilter()
    {
        $cmd=$this->db->createCommand(SQL::SQL_GET_EXPORT);
        $cmd->bindValue(":id", $this->Request['id']);
        $data = $cmd->query();
        $data = $data->read();

        $sql = $data['sql'];

        $expreg = "(:\w*_\w*)";

        $jsParams = "var param = '&id=".$this->Request['id']."'";

        if(preg_match_all($expreg, $sql,$catches)) {
            foreach($catches[0] as $catch) {
                $type = explode("_", $catch);

                $this->catches[] = substr($catch,1,strlen($catch)-1);

                if(count($type)==2)
                    $fName = $type[1];
                else
                    $fName = 'TBD';

                $label = new TLabel();
                $label->setText($fName.' ');
                $this->filtre->Controls[] = $label;
                switch($type[0]) {
                    case ':date':
                       $date = new TActiveDatePicker();
                       $date->setText('');
                       $date->setMode('ImageButton');
                       $date->setID(substr($catch,1,strlen($catch)-1));
                       $date->OnCallBack = array($this, "filterChange");
                       $this->filtre->Controls[] = $date;
                       $this->jsPrint .= "var $fName = document.getElementById('".$date->getClientID()."');\n";
                       $jsParams .= "+ '&".substr($catch,1,strlen($catch)-1)."=' + $fName.value";
                       break;
                    case ':int':
                       $int = new TActiveTextBox();
                       $int->setID(substr($catch,1,strlen($catch)-1));
                       $int->setText(0);
                       $int->OnCallBack = array($this, "filterChange");
                       $this->filtre->Controls[] = $int;
                       $this->jsPrint .= "var $fName = document.getElementById('".$int->getClientID()."');\n";
                       $jsParams .= "+ '&".substr($catch,1,strlen($catch)-1)."=' + $fName.value";
                       break;
                    case ':string':
                       $string = new TActiveTextBox();
                       $string->setID(substr($catch,1,strlen($catch)-1));
                       $string->setText('');
                       $string->setAutoPostBack(true);
                       $string->OnCallBack = array($this, "filterChange");
                       $this->filtre->Controls[] = $string;
                       $this->jsPrint .= "var $fName = document.getElementById('".$string->getClientID()."');\n";
                       $jsParams .= "+ '&".substr($catch,1,strlen($catch)-1)."=' + $fName.value";
                       break;
                }

                $label = new TLabel();
                $label->setText('&nbsp;&nbsp;&nbsp;');
                $this->filtre->Controls[] = $label;
            }

        }
        
        $jsParams .= "+ '&pdf=' + document.getElementById('".$this->pdfExport->getClientID()."').checked";
        $jsParams .= "+ '&excel=' + document.getElementById('".$this->excelExport->getClientID()."').checked";
        $jsParams .= "+ '&csv=' + document.getElementById('".$this->csvExport->getClientID()."').checked";
        $this->jsPrint .= $jsParams.";\n";
        $this->jsPrint .= 'window.open( "'.$this->Service->constructUrl($this->getApplication()->getService()->getRequestedPagePath()).'&action=print" + param, target="_blank" ) ;';

    }

    protected function getData(){
        $cmd=$this->db->createCommand(SQL::SQL_GET_EXPORT);
        $cmd->bindValue(":id", $this->Request['id']);
        $data = $cmd->query();
        $data = $data->read();

        $sql = $data['sql'];

        $cmd=$this->db->createCommand($data['sql']);


        foreach($this->catches as $c) {            
            $control = $this->filtre->findControl($c);

            if(get_parent_class($control) == 'TDatePicker') {

                if(isset($this->Request[$c]))
                    $cmd->bindValue(":$c",$this->dateToSql( $this->Request[$c] ));
                else
                    $cmd->bindValue(":$c",$this->dateToSql( $control->SafeText ));
            }
            else {
                if(isset($this->Request[$c]))
                    $cmd->bindValue(":$c",$this->Request[$c]);
                else
                    $cmd->bindValue(":$c",$control->SafeText);
            }
        }

        $data = $cmd->query();
        $data = $data->readAll();
        
        return $data;
        
    }

    public function onCancel($sender, $param) {
        $this->Response->redirect($this->Service->constructUrl('components.export.export'));
    }


    public function filterChange($sender, $param) {
        $this->DataGrid->DataSource=$this->Data;
        $this->DataGrid->dataBind();
        $this->Page->CallbackClient->update('list', $this->DataGrid);

    }

    public function onRefresh($sender, $param) {
        $this->filterChange($sender, $param);
    }

    protected function onPrint() {

        $cmd=$this->db->createCommand(SQL::SQL_GET_EXPORT);
        $cmd->bindValue(":id", $this->Request['id']);
        $data = $cmd->query();
        $data1 = $data->read();


        $objPHPExcel = new PHPExcel;

        $sheet = $objPHPExcel->getActiveSheet();


        $data = $this->getData();

        $header = array();

        if(count($data)>0) {
            $column = 'A';
            $line = 1;
            foreach($data[0] as $k=>$v) {
                if($this->Request['pdf'] == 'true') {
                    $objPHPExcel->getActiveSheet()->getStyle($column.$line)->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->getStyle($column.$line)->getFont()->setColor(new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_WHITE ));
                    $objPHPExcel->getActiveSheet()->getStyle($column.$line)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $objPHPExcel->getActiveSheet()->getStyle($column.$line)->getFill()->getStartColor()->setRGB('7C7C7C');
                }
                $sheet->setCellValue($column.$line,$k);
                $objPHPExcel->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);

                $column++;

            }


            $line = 3;
            $fill = false;
            foreach($data as $d)
            {
                $column = 'A';
                foreach($d as $field) {
                    $sheet->setCellValue($column.$line,$field);
                    if($fill && $this->Request['pdf'] == 'true') {
                        $objPHPExcel->getActiveSheet()->getStyle($column.$line)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                        $objPHPExcel->getActiveSheet()->getStyle($column.$line)->getFill()->getStartColor()->setRGB('D7D7D7');
                    }
                    $column++;
                }
                $line++;
                
                $fill=!$fill;
            }

        }




        if($this->Request['pdf'] == 'true') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment;filename="'.$data1['name'].'.pdf"');
            header('Cache-Control: max-age=0');

            $objPHPExcel->getActiveSheet()->setShowGridLines(false);
            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
            $objWriter->save('php://output');
            exit;
        }
        else if($this->Request['excel'] == 'true') {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$data1['name'].'.xlsx"');
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->setOffice2003Compatibility(true);
            $objWriter->save('php://output');
            exit;
        } if($this->Request['csv'] == 'true') {

            header("Content-type: application/vnd.ms-excel");
            header('Content-Disposition: attachment;filename="'.$data1['name'].'.csv"');
            header('Cache-Control: max-age=0');

            $objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
            $objWriter->save('php://output');
            exit;

        }

    }

    protected function print2007() {
        
    }

    protected function printPDF() {

        parent::onPrint('L');

        $cmd=$this->db->createCommand(SQL::SQL_GET_EXPORT);
        $cmd->bindValue(":id", $this->Request['id']);
        $data = $cmd->query();
        $data = $data->read();



        $this->pdf->AddPage();

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode($data['name'].":".$data['description']),0,0,'L');
        $this->pdf->Ln(10);
        $this->pdf->setDefaultFont();

        $data = $this->getData();

        $header = array();

        if(count($data)>0) {
            foreach($data[0] as $k=>$v) {
                $header[] = utf8_decode($k);
            }
        }

        //Couleurs,épaisseur du trait et police grasse
        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        //En-tête
        foreach($header as $h)
            $this->pdf->Cell(280/count($header),7,$h,1,0,'C',1);
        $this->pdf->Ln();
        //Restauration des couleurs et de la police
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');
        //Données
        $fill=false;

        foreach($data as $d)
        {
            foreach($d as $field) {
                $this->pdf->Cell(280/count($header),6, utf8_decode($field),'LR',0,'L',$fill);
            }

            $this->pdf->Ln();

            $fill=!$fill;
        }

        $this->pdf->Cell(count($header),0,'','T');


        $this->pdf->render();
        
    }
}
?>
