<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/dompdf/vendor/autoload.php';
//require_once __DIR__ . '/includes/mpdf/vendor/autoload.php';
use Dompdf\Dompdf;
$db;
if ( !isset($db))
{
	$db = new Pdodb(DB_DSN, DB_USER ,DB_PASSWORD );
}
try
	{
		
		$s = isset($_REQUEST['s'])?urldecode($_REQUEST['s']):"";
		 if($s == "")
		 {
		 	 $data = array('success'=> false,'message'=>'Invalid data');
			 echo json_encode($data);
			 exit;
		 }
		 
		$q = $db->pdo->prepare("SELECT count(*) FROM asqi AS A WHERE A.school = :school");
		$q->bindParam(':school', $s, PDO::PARAM_STR);
		if ($q->execute()) 
		{
				if($q->fetchColumn() == 0)
				{
					 $data = array('success'=> false,'message'=>'Invalid data');
					 echo json_encode($data);
					 exit;
				}
		}
		$db->pdo->query("SET CHARACTER SET utf8;");
		$q1 = $db->pdo->prepare("SELECT * FROM asqi AS A WHERE A.school LIKE :school ORDER BY A.code ASC ");
		$q1->bindParam(':school', $s, PDO::PARAM_STR);
		$q1->execute();
		
		$asqi_ret = $q1->fetchAll(PDO::FETCH_ASSOC);
		
		
		if (count($asqi_ret) > 0)
        {
            $ret = array();
			$percentages = array();
			foreach($asqi_ret as $row)
            {
				$category = $row['category'];
				$code = $row['code'];
				$question = $row['question'];
				$answer = $row['answer'];
				$question = strtr($question, array('[1]' => '(1)', '[2]' => '(2)', '[3]' => '(3)', '[4]' => '(4)', '[5]' => '(5)'));
	
				if(strpos($code,'[') !== false)
				{
					$q_code = substr($code, 0, strpos($code,'['));
					$main_q = substr($question, 0, strpos($question,'['));
					$row['main_q'] = $main_q;
					$sub_q = substr($question, strpos($question,'['), strpos($question,']'));
					$sub_q = strtr($sub_q, array('[' => '', ']' => ''));
					$item['n'] = $row['n'];
					$item['dk'] = $row['dk'];
					$item['non_dk'] = $row['non_dk'];
					$item['percent'] = $row['percent'];
					$ret[$category][$q_code][$code]['answers'][] = $row['answer'];
					$ret[$category][$q_code][$code]['title'] = $main_q;
					$ret[$category][$q_code][$code]['question'] = $question;
					$ret[$category][$q_code][$code]['n'] = $row['n'];
					$ret[$category][$q_code][$code]['sub_q_title'] = $sub_q;
					$ret[$category][$q_code][$code]['details'][$answer] = $item;
				}
				else
				{
					$item['n'] = $row['n'];
					$item['dk'] = $row['dk'];
					$item['non_dk'] = $row['non_dk'];
					$item['percent'] = $row['percent'];
					$ret[$category][$code]['question'] = $question;
					$ret[$category][$code]['answers'][] = $row['answer'];
					$ret[$category][$code]['title'] = $question;
					$ret[$category][$code]['n'] = $row['n'];
					$ret[$category][$code]['sub_q_title'] = '';
					$ret[$category][$code]['details'][$answer] = $item;
				}
			}
			
			
			$html = "";
			
			
			foreach($ret as $category => $obj)
			{
				$html  .= '<table class="tbl-category" style="width: 100%;">';
				$html  .= '<tr><td align="left" style="width: 100%;">';
			    $html  .= '</td></tr>';
				
				foreach($obj as $q_code => $q_obj) 
				{
					if(!isset($q_obj['title']))
					{
						$sub_cnt = 0;
						foreach($q_obj as $q_sub_code => $sub_q_obj) 
						{
							if($sub_cnt == 0)
							{
								$html  .= '<tr><td align="left" style="width: 100%">';
								$html  .= '<h5 class="q_title">'.$sub_q_obj['title'].'</h5>';
								$html  .= '</td></tr>';
								$html  .= '<tr><td align="left" style="width: 100%">';
								$html  .= '<table class="legend"  style="width: 100%;margin-bottom: 10px;"><tr>';
								
								foreach($sub_q_obj['answers'] as $i => $q_answer) 
								{
									$legend_i = $i + 1;
									
									$html  .= '<td style="width: 33.3%"><table class="legend-detail" style="width: 100%">';
									$html  .= '<tr><td align="left" style="width:20px"><div class="legendbox legendColor'.$legend_i.'">&nbsp;&nbsp;</div></td>';
									$html  .= '<td class="legend-field" style="width: 95%;padding-left:5px;">'.$q_answer.'</td>';		
									$html  .= '</tr></table>';	
									$html  .= '</td>';
								}
								$html  .= '</tr></table>';
								$html  .= '</td></tr>';
								
							}
							
							$html  .= '<tr><td align="left" style="width: 100%;">';
							$html  .= '<table class="sub-q-table" style="padding: 15px 10px 15px 0px;width:95%">';
							$html  .= '<tr><td align="left" colspan="2">';
							$html  .= '<h5 class="q_sub_title">'.$sub_q_obj['sub_q_title'].'</h5>';
							$html  .= '</td></tr>';
							
							$html  .= '<tr>';
							$html  .= '<td style="width:10%"><p class="info">'.$s.'</p><p class="info">n = '.$sub_q_obj['n'].'</p></td>';
							$html  .= '<td style="width:90%;">';
								$html  .= '<table class="bar-table" style="width: 100%;padding:0;margin:0">';
								$html  .= '<tr>';
						
							$detail_i = 0;
							
							foreach($sub_q_obj['details'] as $answer => $answer_obj) 
							{
								$detail_i++;
								$percent = (float) $answer_obj['percent'];
								
								$html  .= '<td style="width:'.$percent.'%;float:left">';
								$html  .= '<div class="bargraph legendColor'.$detail_i.'"></div>';
								$html  .= '<div style="" class="graph-no">'.$percent.'%</div>';
								$html  .= '</td>';

							}
							$html  .= '</tr></table>';
							$html  .= '</td></tr>';
							
							
							$html  .= '</table></td></tr>';
							
							
							$sub_cnt++;
	
						}
			
					}
					else
					{
						/*$html  .= '<tr><td align="left" style="width: 100%">';
						$html  .= '<h5 class="q_title">'.$q_obj['title'].'</h5>';
						$html  .= '</td></tr>';
						$html  .= '<tr><td align="left" style="width: 100%">';
						$html  .= '<table class="legend"  style="width: 100%;margin-bottom: 10px;"><tr>';
						
						foreach($q_obj['answers'] as $i => $q_answer) 
						{
							$legend_i = $i + 1;
							$html  .= '<td style="width: 33.3%"><table class="legend-detail" style="width: 100%">';
							$html  .= '<tr><td align="left" class="legendColor'.$legend_i.'" style="width:20px">&nbsp;&nbsp;</td>';
							$html  .= '<td class="legend-field" style="width: 95%;padding-left:5px;">'.$q_answer.'</td>';		
							$html  .= '</tr></table>';	
							$html  .= '</td>';
						}
						
						$html  .= '</tr></table>';
						$html  .= '</td></tr>';
						
						$html  .= '<tr><td align="left" style="width: 100%;">';
							$html  .= '<table class="sub-q-table" style="width: 100%;padding: 15px 0px 15px 0px;">';
							$html  .= '<tr><td align="left" colspan="2">';
							$html  .= '<h5 class="q_sub_title">'.$sub_q_obj['sub_q_title'].'</h5>';
							$html  .= '</td></tr>';
							$html  .= '<tr>';
							$html  .= '<td style="width:10%"><p class="info">'.$s.'</p><p class="info">n = '.$sub_q_obj['n'].'</p></td>';
							$html  .= '<td style="width:90%;">';
							$html  .= '<table class="bar-table" style="width: 100%;padding:0;margin:0">';
							$html  .= '<tr>';
						
							$detail_i = 0;
							
							foreach($q_obj['details'] as $answer => $answer_obj) 
							{
								$detail_i++;
								$percent = (float) $answer_obj['percent'];
								
								$html  .= '<td style="width:'.$percent.'%;float:left">';
								$html  .= '<div class="bargraph legendColor'.$detail_i.'"></div>';
								$html  .= '<div style="" class="graph-no">'.$percent.'%</div>';
								$html  .= '</td>';

							}
							$html  .= '</tr></table></td></tr></table>';
							$html  .= '</td></tr>';
							*/
						
					}
											
				}
				
				$html  .= '</table>';
			}
           
			$template = file_get_contents(__DIR__ .'/pdf/template_report.php');
			$tokens = array(
			    'report_html' => $html,
			);
			$pattern = '[[%s]]';
			$map = array();
			foreach($tokens as $var => $value)
			{
			    $map[sprintf($pattern, $var)] = $value;
			}
			$template_html = strtr($template, $map);
			
			// instantiate and use the dompdf class
			$dompdf = new Dompdf();
			$dompdf->loadHtml($template_html);

			// (Optional) Setup the paper size and orientation
			$dompdf->setPaper('A4', 'landscape');

			// Render the HTML as PDF 
			$dompdf->render();
			$dompdf->stream("HCPS_2019_ASQI_Survey_Report.pdf", array("Attachment" => false));
			
			/*
			$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
			$mpdf->WriteHTML($template_html);
			$mpdf->defaultfooterline = 0;
$mpdf->debug = true;
			$mpdf->Output();
			 exit;
			*/
			
        }
        else
        {
             $data = array('success'=> false,'message'=>'Invalid data');
					 echo json_encode($data);
					 exit;
        }
	}
	catch (PDOException $e)
	{
		 $data = array('success'=> false,'message'=>$e->getMessage());
		 echo json_encode($data);
		exit();
		// JSON encode and send back to the server
		
	}



	