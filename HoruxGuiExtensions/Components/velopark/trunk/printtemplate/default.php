<?php

		include("./fpdf/fpdf.php");

		$sql = "SELECT * FROM hr_site WHERE id=1";
		$cmd=$this->db->createCommand($sql);
        $cmd->bindParameter(":id",$subId);
		$site = $cmd->query();
		$site = $site->read();	

		$sql = "SELECT * FROM hr_vp_subscription_attribution WHERE id=".$sender->Text;
		$cmd=$this->db->createCommand($sql);
		$att = $cmd->query();
		$att = $att->read();	

		$sql = "SELECT * FROM hr_vp_subscription WHERE id=".$att['subcription_id'];
		$cmd=$this->db->createCommand($sql);
		$sub = $cmd->query();
		$sub = $sub->read();	

		$params =  array("tva"=>$site['tva'], 
						  "price"=>$sub["price"], 
						  "currency"=>$site["devise"], 
						  "value"=>($sub["price"]*$site['tva']/100),
						  "name"=>utf8_decode($sub["name"])
						);


		$pdf=new FPDF();
		$pdf->AddPage("L", array(150, 100));
		$pdf->SetFont('Arial','B',14);

		$pdf->Cell(0,5,utf8_decode($site['name']),0,1,"C");

		$pdf->SetFont('Arial','',11);

		$pdf->Cell(0,5,utf8_decode($site['street']),0,1,"C");
		$pdf->Cell(0,5,$site['npa']." ".utf8_decode($site['city']),0,1,"C");
		$pdf->Ln();
		$pdf->Cell(0,5,utf8_decode(Prado::localize("Bill number: {n}", array("n"=>$att["id"]))),0,1,"L");
		$pdf->Cell(0,5,$att["create_date"],0,1,"L");
		$pdf->Ln();
		$pdf->Ln();

		
		$pdf->Cell(0,5,utf8_decode(Prado::localize("1x {name}", $params)) ,0,1,"L");
		$pdf->Cell(0,5,utf8_decode(Prado::localize("{price} {currency}", $params)) ,0,1,"R");
		$pdf->Ln();

		$pdf->SetFont('Arial','B',11);
		$pdf->Cell(0,5,utf8_decode(Prado::localize("TOTAL :   {price} {currency}", $params)),"T",1,"R");
		$pdf->SetFont('Arial','',11);
		$pdf->Ln();
		$pdf->Ln();		

		$pdf->Cell(0,5,utf8_decode(Prado::localize("Incl VAT ({tva} %) {price} {currency}:    {value} {currency}", $params)) ,0,1,"L");
		$pdf->Ln();
		$pdf->Cell(0,5,utf8_decode(Prado::localize("At your service")).": ".utf8_decode($att['create_by']),0,1,"L");

		$pdf->Ln();
		$pdf->Ln();

		if($site['tva_number'] != "")
			$pdf->Cell(0,5,utf8_decode(Prado::localize("VAT Number: ")).$site['tva_number'],0,1,"C");

		if($site['phone'] != "")
			$pdf->Cell(0,5,utf8_decode(Prado::localize("Phone: ")).$site['phone'],0,1,"C");
		if($site['fax'] != "")
			$pdf->Cell(0,5,utf8_decode(Prado::localize("Fax: ")).$site['fax'],0,1,"C");
		if($site['email'] != "")
			$pdf->Cell(0,5,utf8_decode(Prado::localize("Email: ")).$site['email'],0,1,"C");


		$pdf->Output();

?> 
