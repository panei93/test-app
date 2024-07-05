<?php
    App::import('Vendor', 'mypdf');
    // create new PDF document
    
    $pdf = new MYPDF('p', PDF_UNIT, 'PDF_PAGE_FORMAT', true, 'UTF-8', false);
    
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetTitle('Progress Chart Report');
    $pdf->SetSubject('Progress Chart Report ');
    $pdf->SetKeywords('Progress Chart Report');

    $pdf->changeTheDefault(false); # changes the default to false

    //set false to header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);

    // set default font subsetting mode to false (for performance)
    $pdf->setFontSubsetting(false);
    $pdf->setMargins(10, 10, 15);

    //set auto page break
    $pdf->SetAutoPageBreak(true, 25);

    $pdf->AddPage();

    $html  = '
	<style>
		.title {
			font-size: 17px;
			text-align: center;
			font-weight: bolder;
		}
		.align_center, th {
			text-align: center;
		}
		.head {
			background-color: #daeef3;
		}
		.right_align {
			text-align: right;
		}
		valign-th {
			background-color: #daeef3;

		}
		td.not-done, td.not-approve {
			background-color: #f7c8c8;
		}
		.test
		{
			 padding-top: 0000px;
		}
		.b_name
		{
			font-weight: bolder;
		}

	</style>';
    $html .= '<body>';
    $html .= '<table cellpadding="3" cellspacing="0">';
    $html .= '	<thead>';
    $html .= '		<tr>';
    $html .= '			<td><p class="title">'.__('進捗管理表').'</p></td>';
    $html .= '		</tr>';
    $html .= '		<tr class="test">';
    $html .= '			<td class="b_name">'.__('イベント名').' :'.$eventname_session.'<br/></td>';
    $html .= '		</tr>';
    $html .= '		<tr>';
    $width = 300/count($header)."px";
    foreach ($header as $key => $head) {
        $html .= '<th width="'.$width.'" height="20"  class="valign-th head" border="0.2" rowspan="2">&nbsp;<br/><span class="b_name">&nbsp;'.__($head).'</span><br></th>';
    }
    $html .= '			<th width="100px" height="20"  class="valign-th head" border="0.2" rowspan="2">&nbsp;<br/><span class="b_name">&nbsp;'.__("Code").'</span><br></th>';
   // $html .= '			<th width="100px" height="20"  class="valign-th head" border="0.2" rowspan="2">&nbsp;<br/><span class="b_name">&nbsp;'.__("Name").'</span><br></th>';
    $html .= '			<th width="90px" height="20"  class="valign-th head" border="0.2" rowspan="2">&nbsp;<br/><span class="b_name">&nbsp;'.__("担当者欄追").'</span><br></th>';
    
    $html .= '			<th width="65px" class="valign-th head"  border="0.2" rowspan="2">&nbsp;<br/><span class="b_name">'.__("営業部長").'(Approved)</span></th>';

    $html .= '		</tr>';
    
    $html .= '	</thead>';
    $html .= '	<tbody>';
    $width = 300/(count($header))."px";
    if (!empty($progress_data)) {
        foreach ($progress_data as $code => $values) {
            $addDate = $values['appDate'];
            $html .= '<tr nobr="true">';
            foreach($layers as $order => $val) {
                if(!empty($values[$order])){
                    $html .= '<td width="'.$width.'" border="0.2" class="align_left">'.$values[$order].'</td>';
                }
            }
            $html .= '	<td width="100px" border="0.2" class="align_left">'.$values[$code].'</td>';
            $html .= '  <td width="100px" border="0.2" class="align_left">'.$code.'</td>';
           
            $html .= '	<td width="90px" border="0.2" class="align_left">'.$values['user_name'].'</td>';
            
            if (!empty($addDate)) {
                $html .= '	<td width="65px" border="0.2" class="align_left">'.date("Y-m-d", strtotime($addDate)).'</td>';
            } else {
                $html .= '<td width="65px" border="0.2" class="align_center" style="background-color:pink;"></td>';
            }
            
            $html .= '</tr>';
        }
    }
    $html .= '	</tbody>';
    $html .= '</table>';
    $html .= '</body>';
    
    //write html
    $pdf->SetFont("cid0jp", "", 9);
    $pdf->writeHTML($html, true, false, true, false, '');
        
    $dateTime = date('dMyHi');
    $file_name = 'ProgressChart_Report'.$dateTime.'.pdf';

    echo $pdf->Output($file_name, 'I');
    exit();
