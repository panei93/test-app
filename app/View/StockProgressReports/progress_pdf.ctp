<?php
	App::import('Vendor','mypdf');
	// create new PDF document
	$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetTitle('Progress Report');
	$pdf->SetSubject('Progress Report');
	$pdf->SetKeywords('Progress Report');

	$pdf->changeTheDefault(false); # changes the default to false

	//set false to header and footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(true);

	// set default font subsetting mode to false (for performance)
	$pdf->setFontSubsetting(false);
	$pdf->setMargins(10, 13, 15);

	//set auto page break
	$pdf->SetAutoPageBreak(true, 25);

	$pdf->AddPage();

	$html  = '
	<style>
		.title {
			font-size: 17px;
			text-align: center;
			font-weight: bold;
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
	</style>';
	$html .= '<body>';
	$html .= '<table cellpadding="3" cellspacing="0">';
	$html .= '	<thead>';
	$html .= '		<tr>';
	$html .= '			<td><p class="title">'.__('進捗管理表').'</p><br/></td>';
	$html .= '		</tr>';
	$html .= '		<tr>';
	$html .= '			<td>'.__('対象月').' : '.$period.'<br/></td>';
	$html .= '		</tr>';
	$html .= '		<tr>';
	$html .= '			<th width="70px" class="valign-th head" border="0.5" rowspan="2">&nbsp;<br/>'.$data[0]['topLayer'].'</th>';
	$html .= '			<th width="70px" class="valign-th head" border="0.5" rowspan="2">&nbsp;<br/>'.$data[0]['middleLayer'].'</th>';
	$html .= '			<th width="30px" class="valign-th head" border="0.5" rowspan="2">&nbsp;<br/>'. $data[0]['bottomLayer'].'</th>';
	$html .= '			<th width="74px" class="valign-th head" border="0.5" rowspan="2">&nbsp;<br/>'.$data[0]['bottomLayer'].' '.__("名").'</th>';		
	$html .= '			<th width="134px" class="head" border="0.5" colspan="3">'.__("営業").'</th>';
	$html .= '			<th width="134px" class="head" border="0.5" colspan="3">'
	.__("財務経理部").'</th>';
	$html .= '			<th width="33px" class="valign-th head" border="0.5" rowspan="2">&nbsp;<br/>'.__("完了").'</th>';

	$html .= '		</tr>';
	$html .= '		<tr>';
	$html .= '			<th width="41px" class="head" border="0.5">'.__("担当者").'</th>';
	$html .= '			<th width="48px" class="head" border="0.5">'.__("管理職").'</th>';
	$html .= '			<th width="45px" class="head" border="0.5">'.__("責任者").'</th>';
	$html .= '			<th width="41px" class="head" border="0.5">'.__("担当者").'</th>';
	$html .= '			<th width="48px" class="head" border="0.5">'.__("管理職").'</th>';
	$html .= '			<th width="45px" class="head" border="0.5">'.__("責任者").'</th>';
	$html .= '		</tr>';
	$html .= '	</thead>';
	$html .= '	<tbody>';
	if(!empty($data)) {
		foreach ($data as $val) {
			$layer_code = $val['layer_code'];
			$head_dept = $val['head_dept'];
			$department = $val['department'];
			$name_jp = $val['name_jp'];
			$sale_incharge = ($val['sale_incharge'] == 'F')? __("済") : __("未");
			$sale_admin = ($val['sale_admin'] == 'F')? __("済") : __("未");
			$sale_manager = ($val['sale_manager'] == 'F')? __("済") : __("未");
			$sale_manager .= "<br/>";
			$sale_manager .= ($val['busi_approve_date'] != '')? $val['busi_approve_date'] : '';
			$acc_incharge = ($val['acc_incharge'] == 'F')? __("済") : __("未");
			$acc_admin = ($val['acc_admin'] == 'F')? __("済") : __("未");
			$acc_manager = ($val['acc_manager'] == 'F')? __("済") : __("未");
			$acc_manager .= "<br/>";
			$acc_manager .= ($val['acc_approve_date'] != '')? $val['acc_approve_date'] : '';
			$status = ($val['status'] == 'done')?__("完了") : '';

			$html .= '<tr nobr="true">';
			$html .= '	<td width="70px" border="0.5">'.$head_dept.'</td>';
			$html .= '	<td width="70px" border="0.5">'.$department.'</td>';
			$html .= '	<td width="30px" border="0.5">'.$layer_code.'</td>';
			$html .= '	<td width="74px" border="0.5">'.$name_jp.'</td>';
			$html .= '	<td width="41px" border="0.5">'.$sale_incharge.'</td>';
			$html .= '	<td width="48px" border="0.5">'.$sale_admin.'</td>';
			$html .= '	<td width="45px" border="0.5">'.$sale_manager.'</td>';
			$html .= '	<td width="41px" border="0.5">'.$acc_incharge.'</td>';
			$html .= '	<td width="48px" border="0.5">'.$acc_admin.'</td>';
			$html .= '	<td width="45px" border="0.5">'.$acc_manager.'</td>';
			$html .= '	<td width="33px" border="0.5">'.$status.'</td>';
			
			$html .= '</tr>';
		}
	}
	$html .= '	</tbody>';
	$html .= '</table>';
	$html .= '</body>';

	ob_end_clean();
	
	//write html
	$pdf->SetFont("cid0jp", "", 8);
	$pdf->writeHTML($html, true, false, true, false, '');
		
	$dateTime = date('dMyHi');
	$file_name = 'Progress_Report'.$dateTime.'.pdf';

 	echo $pdf->Output($file_name, 'I');
?>