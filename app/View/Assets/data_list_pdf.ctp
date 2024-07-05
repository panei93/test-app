<?php
	App::import('Vendor','mypdf');
	// create new PDF document
	$pdf = new MYPDF('L', PDF_UNIT, 'PDF_PAGE_FORMAT', true, 'UTF-8', false);
	
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetTitle('Field Survey Sheet (Fixed Asset)');
	$pdf->SetSubject('Field Survey Sheet (Fixed Asset)');
	$pdf->SetKeywords('Field Survey Sheet (Fixed Asset)');

	$pdf->changeTheDefault(false); # changes the default to false

	//set false to header and footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(true);

	// set default font subsetting mode to false (for performance)
	$pdf->setFontSubsetting(false);
	$pdf->setMargins(10, 10, 10);

	//set auto page break
	$pdf->SetAutoPageBreak(true, 20);

	$pdf->AddPage();
	
	$html  = '
	<style>
		.title {
			font-size: 17px;
			text-align: center;
			font-weight: bold;
		}
		.info {
			font-size: 10px;
			font-weight: bold;
		}
		.align_center {
			text-align: center;
		}
		th {
			text-align: center;
			background-color: #daeef3;
			font-weight: bolder;
		}
		.right_align {
			text-align: right;
		}
		.left_align {
			text-align: left;
			background-color: #ddd;
		}
	</style>';
	$choose_ba = (empty($this->Session->read('SESSION_LAYER_CODE')))? 'All' : $this->Session->read('SESSION_LAYER_CODE');
	$event_name = $this->Session->read('EVENT_NAME');
	$html .= '<body>';
	$html .= '<table cellpadding="3" cellspacing="0">';
	$html .= '	<thead>';
	$html .= '		<tr>';
	$html .= '			<td colspan="17"><p class="title">'.__('実地調査票【固定資産】').'</p></td>';
	$html .= '		</tr>';
	$html .= '		<tr>';
	$html .= '			<td colspan="17"><span class="info">';
	$html .= 				__('部署コード').' : '.$choose_ba;
	$html .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	$html .= 				__('イベント名').' : '.$event_name;
	$html .= '              <br/>'.$total_records;
	$html .= '			</span></td>';
	$html .= '		</tr>';
	$html .= '		<tr>';
	$html .= '			<th border="0.2" width="55px"><b>'. __("画像") .'</b></th>';
	$html .= '			<th border="0.2" width="45px"><b>'.__("資産番号") .'</b></th>';
	$html .= '			<th border="0.2" width="60px"><b>'.__("資産名称") .'</b></th>';
	$html .= '			<th border="0.2" width="50px"><b>'.__("取得年月日") .'</b></th>';
	$html .= '			<th border="0.2" width="40px"><b>';
	$html .= 				__("第1キーコード") .'<br/>';
	$html .= '				('.__("部署コード").')';
	$html .= '			</b></th>';
	$html .= '			<th border="0.2" width="40px"><b>';
	$html .= 				__("第1キー名称") .'<br/>';
	$html .= '				('.__("部署名").')';
	$html .= '			</b></th>';
	$html .= '			<th border="0.2" width="40px"><b>';
	$html .= 			 	__("第2キー名称") .'<br/>';
	$html .= '				('. __("種類") .')';
	$html .= '			</b></th>';
						// change from 取得価額 to 当月末帳簿価額 by khin hnin myo 
	$html .= '			<th border="0.2"><b>'.__("当月末帳簿価額").'</b></th>';
	/* Begin edit for status BCMM Sandi */
	$html .= '			<th border="0.2" width="30px"><b>'.__("状態").'</b></th>';
	/* End edit for status BCMM Sandi */
	$html .= '			<th border="0.2" width="35px"><b>'. __("数量") .'</b></th>';
	$html .= '			<th border="0.2" width="50px"><b>'. __("設置場所") .'</b></th>';
	$html .= '			<th border="0.2" width="35px"><b>';
	$html .=  				__("現物確認欄") .'<br/>';
	$html .= '			</b></th>';
	$html .= '			<th border="0.2" width="65px"><b>'. __("確認事項に関するコメント") .'</b></th>';
	$html .= '			<th border="0.2" width="40px"><b>'. __("ラベル番号") .'</b></th>';
	$html .= '			<th border="0.2" width="35px"><b>';
	$html .=  				__("ラベル確認欄") .'<br/>';
	$html .= '			</b></th>';
	$html .= '			<th border="0.2" width="65px"><b>'. __("ラベル貼付不可理由") .'</b></th>';

	$html .= '		</tr>';
	$html .= '	</thead>';
	$html .= '	<tbody>';
	$j = "0";
	$start = date("H:i:sa");
    for($i=0; $i<$count; $i++) {
    	$j++;
        $asset_id = $data[$i]['Asset']['asset_id'];
        $event_id = $data[$i]['Asset']['event_id'];
		$tbl_layer_code = $data[$i]['Asset']['layer_code'];
        $name_jp = $data[$i]['Asset']['layer_name'];
        $sec_key_name = $data[$i]['Asset']['2nd_key_name'];
        $amount = $data[$i]['Asset']['amount'];
        /* Begin edit for status BCMM Sandi */
        $asset_status = $data[$i]['Asset']['status'];
			if($asset_status == 1) {
				$asset_status = __("新規");
			} else if($asset_status == 2) {
				$asset_status = __("済");
			} else if($asset_status == 3) {
				$asset_status = __("移動");
			} else if($asset_status == 4) {
				$asset_status = __("除却");
			} else if ($asset_status == 5) {
				$asset_status = __("売却");
			} else {
				$asset_status = '';
			}
		/* End edit for status BCMM Sandi */
        $asset_no = $data[$i]['Asset']['asset_no'];
        $asset_name = $data[$i]['Asset']['asset_name'];
        $quantity = $data[$i]['Asset']['quantity'];
        $acq_date = $data[$i]['Asset']['acq_date'];
        $place_name = $data[$i]['Asset']['place_name'];
        $img_url = $data[$i]['pic']['real_path'];
		if(!empty($img_url) && @getimagesize($img_url)) {
			$photo = $img_url;
		} else {
			$photo = './img/no_image.png';
		}
        $label_no = $data[$i]['Asset']['label_no'];
        $asset_flag = $data[$i]['Asset']['flag'];

        # data for no reference event_id
        $physical_chk_not_ref = $data[$i]['Asset']['not_ref_physical_chk'];
        $label_chk_not_ref = $data[$i]['Asset']['not_ref_label_chk'];
        $cmt_not_ref_comment = $data[$i]['cmt_not_ref']['cmt_not_ref_comment'];
        $cmt_not_ref_remark = $data[$i]['cmt_not_ref']['cmt_not_ref_remark'];

        # data for reference event_id with same asset_no
        $physical_chk_ref = $data[$i]['ref_event_data']['physical_chk_ref'];
        $label_chk_ref = $data[$i]['ref_event_data']['label_chk_ref'];
        $cmt_ref_comment = $data[$i]['ref_event_data']['cmt_ref_comment'];
        $cmt_ref_remark = $data[$i]['ref_event_data']['cmt_ref_remark'];

        # if tbl_m_asset flag is 1, then show reference data
        if($asset_flag == 1) {
            if($physical_chk_ref == 1) {
                $phy_status = "1";
            } else {
                $phy_status = "0";
            }
            if($label_chk_ref == 1) {
                $lbl_status = "1";
            } else {
                $lbl_status = "0";
            }
            $comment = nl2br(h($cmt_ref_comment));
            $remark = nl2br(h($cmt_ref_remark));
        } else {
            if($physical_chk_not_ref == 1) {
                $phy_status = "1";
            } else {
                $phy_status = "0";
            }
            if($label_chk_not_ref == 1) {
                $lbl_status = "1";
            } else {
                $lbl_status = "0";
            }
            $comment = nl2br(h($cmt_not_ref_comment));
            $remark = nl2br(h($cmt_not_ref_remark));
        }

        $html .= '<tr nobr="true">';
		$html .= '<td  border="0.2" width="55px"><img src="'.$photo.'" /></td>';
        $html .= '<td  border="0.2" width="45px" class="right_align">'.$asset_no.'</td>';
        $html .= '<td  border="0.2" width="60px">'.$asset_name.'</td>';
        $html .= '<td  border="0.2" width="50px" class="right_align">'.$acq_date.'</td>';
        $html .= '<td  border="0.2" width="40px" class="align_left">'.$tbl_layer_code.'</td>';
        $html .= '<td  border="0.2" width="40px" class="align_left">'.$name_jp.'</td>';
        $html .= '<td  border="0.2" width="40px" class="align_left">'.$sec_key_name.'</td>';
        $html .= '<td  border="0.2" class="right_align">'.number_format($amount).'</td>';
        /* Begin edit for status BCMM Sandi */
        $html .= '<td  border="0.2" width="30px" class="align_left"><font color="blue">'.$asset_status .'</font></td>';
        /* End edit for status BCMM Sandi */
        $html .= '<td  border="0.2" width="35px" class="right_align">'.$quantity.'</td>';
        $html .= '<td  border="0.2" width="50px">'.$place_name.'</td>';

        $html2="";
         #show physical check with checkbox style image
        if($phy_status=='1')
        {
	        $html2 = '
	        <td  border="0.2" width="35px" class="align_center">
	        <img src="./img/chkBox.png" width="10" height="10">
	        </td>      
	        ';
        }
        else
        {
	        $html2 = '
	        <td  border="0.2" width="35px" class="align_center">
	        <img src="./img/unchkBox.png" width="10" height="10">
	        </td>
	        ';
        }       
        $html .=$html2;
		$html .= '<td  border="0.2" width="65px">'.$comment.'</td>';
        $html .= '<td  border="0.2" width="40px" class="right_align">'.$label_no.'</td>';
		$html3="";
        if($lbl_status=='1')
        {
	        $html3 = '
	        <td  border="0.2" width="35px" class="align_center">
	        <img src="./img/chkBox.png" width="10" height="10">
	        </td>      
	        ';
        }
        else
        {
	        $html3 = '
	        <td  border="0.2" width="35px" class="align_center">
	        <img src="./img/unchkBox.png" width="10" height="10">
	        </td>
	        ';
        }       
        $html .=$html3;
        $html .= '<td  border="0.2" width="65px">'.$remark.'</td>';      
        $html .= '</tr>';
       
        CakeLog::write('debug', $j. 'rows have been exported [Asset NO:'.$asset_no.'].');
    }
    $end = date("H:i:sa");

	$sst = strtotime($start);
	$eet=  strtotime($end);
	$diff= $eet-$sst;
	$timeElapsed= gmdate("H:i:s",$diff);
    CakeLog::write('debug','Downloading begins from '.$start. ' ends  '.$end.'. After downloading '.$j.' rows, it takes '.$timeElapsed.'.');
   
	$html .= '	</tbody>';
    $html .= '</table>';
	$html .= '</body>';
	//write html
	$date = date('Y_m_d');
	$file_name = 'FixedAssetsDataList_'.$date.'.pdf';
	$pdf->SetFont("cid0jp", "", 9);
	$pdf->writeHTML($html, true, false, true, false, '');
	echo $pdf->Output($file_name, 'D');
	exit();
?>
