<?php

App::import('Vendor','tcpdf/tcpdf');

class MYPDF extends TCPDF {

	public function changeTheDefault($tcpdflink) {
		# to remove extra blank page
		$this->tcpdflink = $tcpdflink;
	}

	public function Footer() {
		// Position at 10 mm from bottom
        $this->SetY(-13);
        // Set font
        $this->SetFont('helvetica', 'I', 8);
        // Page number
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');	
	}
}


?>