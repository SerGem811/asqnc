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
		$q1 = $db->pdo->prepare("SELECT *, SUM(A.percent) AS a_percent FROM asqi AS A 
		WHERE A.school LIKE :school AND (A.answer = 'Agree' OR A.answer = 'Strongly agree') GROUP BY A.code
        ORDER BY A.code ASC");
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
				$sql = "SELECT SUM(A.percent) AS d_percent FROM asqi AS A 
				WHERE A.school LIKE 'District' AND A.code = '$code' AND A.answer = '$answer'";
				
				$q2 = $db->pdo->prepare($sql);
		
				$q2->execute();
				$d_school = $q2->fetch(PDO::FETCH_ASSOC);
				
				$d_school_percent = isset($d_school['d_percent']) ? $d_school['d_percent'] : 0 ;
				$asqi_ret = $q1->fetchAll(PDO::FETCH_ASSOC);
				
				$question = strtr($question, array('[1]' => '(1)', '[2]' => '(2)', '[3]' => '(3)', '[4]' => '(4)', '[5]' => '(5)'));
			
				if(strpos($code,'[') !== false)
				{
					$q_code = substr($code, 0, strpos($code,'['));
					$main_q = substr($question, 0, strpos($question,'['));
					$row['main_q'] = $main_q;
					$sub_q = substr($question, strpos($question,'['), strpos($question,']'));
					$sub_q = strtr($sub_q, array('[' => '', ']' => ''));
					$ret[$category][$q_code][$code]['title'] = $main_q;
					$ret[$category][$q_code][$code]['n'] = $row['n'];
					$ret[$category][$q_code][$code]['sub_q_title'] = substr(trim($sub_q),3, -1);
					$ret[$category][$q_code][$code]['percent'] = $row['a_percent'];
					$ret[$category][$q_code][$code]['d_percent'] = $d_school_percent;
					
					
				}
				else
				{
					

					//$ret[$category][$code]['title'] = $question;
					//$ret[$category][$code]['n'] = $row['n'];
					//$ret[$category][$code]['sub_q_title'] = '';
					//$ret[$category][$code]['percent'] = $row['a_percent'];
					//$ret[$category][$code]['d_percent'] = $d_school_percent;
					
				}
				
			}

			/*echo '<pre>';
			print_r($ret);
			echo '</pre>';*/

			$html = "";
			
			
			foreach($ret as $category => $obj)
			{
				$html  .= '<table class="tbl-category" style="width: 100%;">';
				$html  .= '<tr><td align="left" style="width: 100%;">';
			    $html  .= '<h4 class="q_category">'.$category.'</h4>';
			    $html  .= '</td></tr>';
				
				foreach($obj as $q_code => $q_obj) 
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
								
								
								$html  .= '</tr></table>';
								$html  .= '</td></tr>';
								
							}
							
							$html  .= '<tr><td align="left" style="width: 100%;">';
							$html  .= '<table class="sub-q-table" style="padding: 15px 10px 15px 0px;width:95%">';
							$html  .= '<tr><td align="left" colspan="2" style="border:1px solid;">';
							$html  .= '<h5 class="q_sub_title">'.$sub_q_obj['sub_q_title'].'</h5>';
							$html  .= '</td><td style="width:20%; border:1px solid; text-align:center;">'.$sub_q_obj['percent'].'%</td>
											<td style="width:20%; border:1px solid; text-align:center;">'.$sub_q_obj['d_percent'].'%</td></tr>';
							
							$html  .= '<tr>';
							$html  .= '<td style="width:10%"></td>';
							$html  .= '<td style="width:90%;">';
								$html  .= '<table class="bar-table" style="width: 100%;padding:0;margin:0">';
								$html  .= '<tr>';
						
							$detail_i = 0;
							
							
							$html  .= '</tr></table>';
							$html  .= '</td></tr>';
							
							
							$html  .= '</table></td></tr>';
							
							
							$sub_cnt++;
	
						}
											
				}
				
				$html  .= '</table>';
			}
           
			$template = file_get_contents(__DIR__ .'/pdf/template_agree-report.php');
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



	