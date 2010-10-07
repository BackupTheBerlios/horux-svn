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

class nonWorkingDay extends Page
{
    public function onLoad($param)
    {
        parent::onLoad($param);

        if(!$this->isPostBack)
        {
            $year = $this->Session['nonWorkingDayYear'];
            if($year)
                $this->year->setSelectedValue($year);

        }
        else
            $this->Session['nonWorkingDayYear'] = $this->year->getSelectedValue();

    }

    protected function onPrint()
    {
        parent::onPrint();

        $this->pdf->AddPage();

        $year = $this->Session['nonWorkingDayYear']."-01-01";
        $cmd = $this->db->createCommand( "SELECT * FROM hr_non_working_day WHERE `from`>='".$year."' ORDER BY `from`"  );

        $data =  $cmd->query();
        $data = $data->readAll();

        $this->pdf->SetFont('Arial','',11);
        $this->pdf->Cell(0,10,utf8_decode(Prado::localize('List of the non working days since the {year}', array("year"=>$this->dateFromSql($year)))),0,0,'L');
        $this->pdf->Ln(10);

        $this->pdf->setDefaultFont();

        $header = array(utf8_decode(Prado::localize("Name")),
            utf8_decode(Prado::localize("Description")),
            utf8_decode(Prado::localize("From")),
            utf8_decode(Prado::localize("Until")));

        //Couleurs, �paisseur du trait et police grasse
        $this->pdf->SetFillColor(124,124,124);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetDrawColor(255);
        $this->pdf->SetLineWidth(.3);
        $this->pdf->SetFont('','B');
        //En-t�te
        $w=array(40,90,30,30);
        for($i=0;$i<count($header);$i++)
        $this->pdf->Cell($w[$i],7,$header[$i],1,0,'C',1);
        $this->pdf->Ln();
        //Restauration des couleurs et de la police
        $this->pdf->SetFillColor(215,215,215);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('');
        //Donn�es
        $fill=false;


        foreach($data as $d)
        {
            $from = $this->dateFromSql($d['from']);
            $until = $this->dateFromSql($d['until']);


            $name = $d['name'];
            $comment  = $d['comment'];

            $this->pdf->Cell($w[0],6,utf8_decode($name),'LR',0,'L',$fill);
            $this->pdf->Cell($w[1],6,utf8_decode($comment),'LR',0,'L',$fill);
            $this->pdf->Cell($w[2],6,$from,'LR',0,'C',$fill);
            $this->pdf->Cell($w[3],6,$until,'LR',0,'C',$fill);
            $this->pdf->Ln();
            $fill=!$fill;

        }

        $this->pdf->Cell(array_sum($w),0,'','T');

        $this->pdf->render();

    }

    public function selectionChanged($sender, $param)
    {
        $this->Session['nonWorkingDayYear'] = $sender->getSelectedValue();
        $this->hiddenMessage();
    }

    public function displayCalendar($month, $monName)
    {
        //$command=$this->db->createCommand("SET NAMES utf8");
        //$command->query();
        echo '
            <table border="0" style="border:1px solid #666666;height:150px">
                <tbody>
                    <tr style="height:25px;background-color:#fff8d7"><th colspan="7" align="center">'.$monName.'</th></tr>
                    <tr style="background-color:#eee">
                        <td>'.Prado::localize('Mon').'</td>
                        <td>'.Prado::localize('Tue').'</td>
                        <td>'.Prado::localize('Wed').'</td>
                        <td>'.Prado::localize('Thu').'</td>
                        <td>'.Prado::localize('Fri').'</td>
                        <td>'.Prado::localize('Sat').'</td>
                        <td>'.Prado::localize('Sun').'</td>
                    </tr>
                </tbody>';
        $nDay = date('t', mktime(0,0,0,$month,1,$this->year->getSelectedValue()));
        $firstDay = date('N', mktime(0,0,0,$month,1,$this->year->getSelectedValue()));
        echo '<tr>';
        for($i=1; $i<$firstDay; $i++)
        {
            echo '<td width="20"  align="center"> </td>';
        }
        $index = $firstDay;
        for($i=$index; $i<$nDay+$index;$i++)
        {
            $d1 = date('Y-m-d',mktime(0,0,0,$month,$i-$index+1,$this->year->getSelectedValue()));
            $cmd = $this->db->createCommand( "SELECT * FROM hr_non_working_day WHERE `from`<='$d1' AND `until`>='$d1'" );
            $data =  $cmd->query();
            $data = $data->read();


            if(($i-1)%7==0 && $nDay+$index-$i>7)
            {
                echo '</tr><tr>';
            }
            elseif(($i-1)%7==0)
            echo '</tr>';

            if(count($data)>1)
            {
                $username = $this->Page->getUserName($data['locked']);

                $time = "";
                if($data['period'] == "allday")
                    $time = "<br>".Prado::localize('All the day');
                if($data['period'] == "morning")
                    $time = "<br>".Prado::localize('Morning only');
                if($data['period'] == "afternoon")
                    $time = "<br>".Prado::localize('Afternoon only');

                if(!$this->isRecordBlock('hr_non_working_day', $data['id']))
                {
                    echo '<td width="20" style="background-color:'.$data['color'].';"  align="center"><span onmouseover="Tip(\''.addslashes($data['name']).'<br/>'.addslashes($data['comment']).$time.'\', BALLOON, true, BALLOONIMGPATH, \'./js/tip_balloon\', OFFSETX, -10, TEXTALIGN, \'justify\', FADEIN, 600, FADEOUT, 600, PADDING, 8)"><a href="'.$this->Service->constructUrl('nonWorkingDay.mod',array('id'=>$data['id'])).'">'.($i-$index+1).'</a></span></td>';
                }
                else
                {
                    echo '<td width="20" style="background-color:'.$data['color'].';"  align="center"><span onmouseover="Tip(\''.addslashes($data['name']).'<br/>'.addslashes($data['comment']).$time.'<br><i>This record is currently modify by <strong>'.$username.'</strong><i>\', BALLOON, true, BALLOONIMGPATH, \'./js/tip_balloon\', OFFSETX, -10, TEXTALIGN, \'justify\', FADEIN, 600, FADEOUT, 600, PADDING, 8)">'.($i-$index+1).'</span></td>';
                }
            }
            else
                echo '<td width="20"  align="center">'.($i-$index+1).'</td>';
        }

        echo '</table>';
    }
}

?>
