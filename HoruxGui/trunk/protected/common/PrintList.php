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
		
include("./fpdf/fpdf.php");

class PrintListPDF extends FPDF 
{
	public $userName = "";
	public $siteName = "";

	function Header()
	{
	    //Police Arial gras 15
	    $this->SetFont('Arial','',12);
	    //Titre
		$date = date("d-m-Y G:i:s");
	    $this->Cell(0,10,utf8_decode(Prado::localize('Report generate at {date} by {user}', array('date'=>$date, 'user'=>$this->userName))),'B',0,'L');
	    $this->Cell(0,10,$this->siteName,'B',0,'R');
	    //Saut de ligne
	    $this->Ln(20);
	}

	function setDefaultFont()
	{
		$this->SetFont('Arial','',10);
	}
	
	//Pied de page
	function Footer()
	{
	    //Positionnement à 1,5 cm du bas
	    $this->SetY(-15);
	    //Police Arial italique 8
	    $this->SetFont('Arial','I',8);
	    //Numéro de page
	    $this->Cell(0,10,Prado::localize('Page').' '.$this->PageNo(),'T',0,'C');
	}

	function render()
	{
		$this->Output();
		exit;
	}
	
	
	function VCell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0)
	{
	    //Output a cell
	    $k=$this->k;
	    if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
	    {
	        $x=$this->x;
	        $ws=$this->ws;
	        if($ws>0)
	        {
	            $this->ws=0;
	            $this->_out('0 Tw');
	        }
	        $this->AddPage($this->CurOrientation);
	        $this->x=$x;
	        if($ws>0)
	        {
	            $this->ws=$ws;
	            $this->_out(sprintf('%.3f Tw',$ws*$k));
	        }
	    }
	    if($w==0)
	        $w=$this->w-$this->rMargin-$this->x;
	    $s='';
	// begin change Cell function 
	    if($fill==1 or $border>0)
	    {
	        if($fill==1)
	            $op=($border>0) ? 'B' : 'f';
	        else
	            $op='S';
	        if ($border>1) {
	            $s=sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ',$border,
	                        $this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	        }
	        else
	            $s=sprintf('%.2f %.2f %.2f %.2f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	    }
	    if(is_string($border))
	    {
	        $x=$this->x;
	        $y=$this->y;
	        if(is_int(strpos($border,'L')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
	        else if(is_int(strpos($border,'l')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
	            
	        if(is_int(strpos($border,'T')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
	        else if(is_int(strpos($border,'t')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
	        
	        if(is_int(strpos($border,'R')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	        else if(is_int(strpos($border,'r')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	        
	        if(is_int(strpos($border,'B')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	        else if(is_int(strpos($border,'b')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	    }
	    if(trim($txt)!='')
	    {
	        $cr=substr_count($txt,"\n");
	        if ($cr>0) { // Multi line
	            $txts = explode("\n", $txt);
	            $lines = count($txts);
	            for($l=0;$l<$lines;$l++) {
	                $txt=$txts[$l];
	                $w_txt=$this->GetStringWidth($txt);
	                if ($align=='U')
	                    $dy=$this->cMargin+$w_txt;
	                elseif($align=='D')
	                    $dy=$h-$this->cMargin;
	                else
	                    $dy=($h+$w_txt)/2;
	                $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
	                if($this->ColorFlag)
	                    $s.='q '.$this->TextColor.' ';
	                $s.=sprintf('BT 0 1 -1 0 %.2f %.2f Tm (%s) Tj ET ',
	                    ($this->x+.5*$w+(.7+$l-$lines/2)*$this->FontSize)*$k,
	                    ($this->h-($this->y+$dy))*$k,$txt);
	                if($this->ColorFlag)
	                    $s.=' Q ';
	            }
	        }
	        else { // Single line
	            $w_txt=$this->GetStringWidth($txt);
	            $Tz=100;
	            if ($w_txt>$h-2*$this->cMargin) {
	                $Tz=($h-2*$this->cMargin)/$w_txt*100;
	                $w_txt=$h-2*$this->cMargin;
	            }
	            if ($align=='U')
	                $dy=$this->cMargin+$w_txt;
	            elseif($align=='D')
	                $dy=$h-$this->cMargin;
	            else
	                $dy=($h+$w_txt)/2;
	            $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
	            if($this->ColorFlag)
	                $s.='q '.$this->TextColor.' ';
	            $s.=sprintf('q BT 0 1 -1 0 %.2f %.2f Tm %.2f Tz (%s) Tj ET Q ',
	                        ($this->x+.5*$w+.3*$this->FontSize)*$k,
	                        ($this->h-($this->y+$dy))*$k,$Tz,$txt);
	            if($this->ColorFlag)
	                $s.=' Q ';
	        }
	    }
	// end change Cell function 
	    if($s)
	        $this->_out($s);
	    $this->lasth=$h;
	    if($ln>0)
	    {
	        //Go to next line
	        $this->y+=$h;
	        if($ln==1)
	            $this->x=$this->lMargin;
	    }
	    else
	        $this->x+=$w;
	}

	function CellExt($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
	{
	    //Output a cell
	    $k=$this->k;
	    if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
	    {
	        $x=$this->x;
	        $ws=$this->ws;
	        if($ws>0)
	        {
	            $this->ws=0;
	            $this->_out('0 Tw');
	        }
	        $this->AddPage($this->CurOrientation);
	        $this->x=$x;
	        if($ws>0)
	        {
	            $this->ws=$ws;
	            $this->_out(sprintf('%.3f Tw',$ws*$k));
	        }
	    }
	    if($w==0)
	        $w=$this->w-$this->rMargin-$this->x;
	    $s='';
	// begin change Cell function 12.08.2003 
	    if($fill==1 or $border>0)
	    {
	        if($fill==1)
	            $op=($border>0) ? 'B' : 'f';
	        else
	            $op='S';
	        if ($border>1) {
	            $s=sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ',$border,
	                $this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	        }
	        else
	            $s=sprintf('%.2f %.2f %.2f %.2f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	    }
	    if(is_string($border))
	    {
	        $x=$this->x;
	        $y=$this->y;
	        if(is_int(strpos($border,'L')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
	        else if(is_int(strpos($border,'l')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
	            
	        if(is_int(strpos($border,'T')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
	        else if(is_int(strpos($border,'t')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
	        
	        if(is_int(strpos($border,'R')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	        else if(is_int(strpos($border,'r')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	        
	        if(is_int(strpos($border,'B')))
	            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	        else if(is_int(strpos($border,'b')))
	            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	    }
	    if (trim($txt)!='') {
	        $cr=substr_count($txt,"\n");
	        if ($cr>0) { // Multi line
	            $txts = explode("\n", $txt);
	            $lines = count($txts);
	            //$dy=($h-2*$this->cMargin)/$lines;
	            for($l=0;$l<$lines;$l++) {
	                $txt=$txts[$l];
	                $w_txt=$this->GetStringWidth($txt);
	                if($align=='R')
	                    $dx=$w-$w_txt-$this->cMargin;
	                elseif($align=='C')
	                    $dx=($w-$w_txt)/2;
	                else
	                    $dx=$this->cMargin;

	                $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
	                if($this->ColorFlag)
	                    $s.='q '.$this->TextColor.' ';
	                $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET ',
	                    ($this->x+$dx)*$k,
	                    ($this->h-($this->y+.5*$h+(.7+$l-$lines/2)*$this->FontSize))*$k,
	                    $txt);
	                if($this->underline)
	                    $s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
	                if($this->ColorFlag)
	                    $s.=' Q ';
	                if($link)
	                    $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$w_txt,$this->FontSize,$link);
	            }
	        }
	        else { // Single line
	            $w_txt=$this->GetStringWidth($txt);
	            $Tz=100;
	            if ($w_txt>$w-2*$this->cMargin) { // Need compression
	                $Tz=($w-2*$this->cMargin)/$w_txt*100;
	                $w_txt=$w-2*$this->cMargin;
	            }
	            if($align=='R')
	                $dx=$w-$w_txt-$this->cMargin;
	            elseif($align=='C')
	                $dx=($w-$w_txt)/2;
	            else
	                $dx=$this->cMargin;
	            $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
	            if($this->ColorFlag)
	                $s.='q '.$this->TextColor.' ';
	            $s.=sprintf('q BT %.2f %.2f Td %.2f Tz (%s) Tj ET Q ',
	                        ($this->x+$dx)*$k,
	                        ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,
	                        $Tz,$txt);
	            if($this->underline)
	                $s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
	            if($this->ColorFlag)
	                $s.=' Q ';
	            if($link)
	                $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$w_txt,$this->FontSize,$link);
	        }
	    }
	// end change Cell function 12.08.2003
	    if($s)
	        $this->_out($s);
	    $this->lasth=$h;
	    if($ln>0)
	    {
	        //Go to next line
	        $this->y+=$h;
	        if($ln==1)
	            $this->x=$this->lMargin;
	    }
	    else
	        $this->x+=$w;
	}	
}	
?>