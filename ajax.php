<?php
require_once('includes/config.php');
require_once('includes/functions.php');
if (!isset($_SESSION)) {
    session_start();
}
//if(!is_ajax())
//{
global $db;
global $year;
if (!isset($db)) {
    include_once("includes/Pdodb.class.php");
    //MySQL
    $db = new Pdodb(DB_DSN, DB_USER, DB_PASSWORD);
}

if (isset($_REQUEST["action"]) && !empty($_REQUEST["action"])) { //Checks if action value exists
    $action = trim($_REQUEST["action"]);

    switch ($action) {
        //Switch case for value of action
        case "get_school_list":
            get_school_list();
            break;
        case "reports":
            reports();
            break;
        case "agree_reports":
            agree_reports();
            break;
        case "schools":
            schools();
            break;
        case "get_total_rate":
            get_total_rate();
            break;
    }
}
//}

//Function to check if the request is an AJAX request
function is_ajax()
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

function schools()
{
    try {
        global $db;
        $sth = $db->pdo->prepare("SELECT A.school FROM asqi AS A GROUP BY  A.school");
        $asqi_ret = array();
        $ret = array();
        if ($sth->execute()) {
            $asqi_ret = $sth->fetchAll(PDO::FETCH_ASSOC);
        }

        if (count($asqi_ret) > 0) {
            foreach ($asqi_ret as $data) {
                $report_url = 'report.php?s=' . urlencode($data['school']);
                $agree_report_url = 'agree-report.php?s=' . urlencode($data['school']);

                $total_invitees = 0;
                $data['total_invitees'] = $total_invitees;
                $respondents = 0;
                $data['respondents'] = $respondents;
                $respondents_pert = 0;
                $data['respondents_pert'] = $respondents_pert;

                $ret[] = $data;
            }
            echo '{"aaData": ' . json_encode($ret) . '}';
            exit;
        } else {
            echo '{
                "sEcho": 1,
                "iTotalRecords": "0",
                "iTotalDisplayRecords": "0",
                "aaData": []
            }';
            exit;
        }
    } catch (PDOException $e) {
        $data = array('success' => false, 'message' => $e->getMessage());
        echo json_encode($data);
        exit();
        // JSON encode and send back to the server

    }

}

function get_school_list()
{
    try {
        global $db;
        global $year;

        $statement = $db->pdo->prepare("select s.id as school_id, s.district_id as district_id,
                                        s.name as school_name, d.name as district_name, r.invitees as invitees, 
                                        r.respondents as respondents from tbl_school as s left join tbl_rates as r on s.id=r.school_id left join tbl_district as d on s.district_id = d.id where r.year=".$year);

        $lst_school = array();
        if ($statement->execute()) {
            $lst_school = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        $ret = array();
        
        if (count($lst_school) > 0) {
            $district_id = -1;

            foreach ($lst_school as $school) {
                if($school['district_id'] != $district_id) {
                    $district_id = $school['district_id'];
                    $district_data = get_district_info($district_id, $school['district_name']);
                    array_push($ret, $district_data);
                }
                

                $data = array();

                $report_url = 'report.php?s_id=' . urlencode($school['school_id']) . '&d_id=' . urlencode($school['district_id']);
                $agree_report_url = 'agree-report.php?s_id=' . urlencode($school['school_id']) . '&d_id=' . urlencode($school['district_id']);

                $data['district'] = $school['district_name'];

                $data['school'] = $school['school_name'];

                if ($school['invitees'] == null || $school['invitees'] == 0)
                    $respondents_pert = 0;
                else
                    $respondents_pert = round($school['respondents'] / $school['invitees'] * 100, 2);

                if ($respondents_pert >= 49) {
                    $data['reports'] = '<a target="_blank" href="' . $report_url . '"><i class="fa fa-tasks"></i></a>';
                    if ($data['school'] != '')
                        $data['reports'] .= '<a target="_blank" href="' . $agree_report_url . '"><i class="fa fa-table"></i></a>';
                } else {
                    $data['reports'] = '';
                }

                $data['total_invitees'] = $school['invitees'] == null ? 0 : $school['invitees'];
                $data['respondents'] = $school['respondents'] == null ? 0 : $school['respondents'];
                $data['respondents_pert'] = $respondents_pert;

                array_push($ret, $data);
            }
            echo '{"aaData": ' . json_encode($ret) . '}';
        } else {
            echo '{
                    "sEcho": 1,
                    "iTotalRecords": "0",
                    "iTotalDisplayRecords": "0",
                    "aaData": []
                }';
        }

    } catch (PDOException $e) {
        $data = array('success' => false, 'message' => $e->getMessage());
        echo json_encode($data);
        exit();
    }
}

// report ajax
function get_district_report($arr, $q_id, $sq_id, $a_id) {
    foreach($arr as $dr) {
        if($dr['q_id'] == $q_id && $dr['sq_id'] == $sq_id && $dr['a_id'] == $a_id)
            return $dr;
    }
    return null;
}

function get_state_report($arr, $q_id, $sq_id, $a_id) {
    foreach($arr as $sr) {
        if($sr['q_id'] == $q_id && $sr['sq_id'] == $sq_id && $sr['a_id'] == $a_id)
            return $sr;
    }
}

function get_table_name($d_id, $s_id) {

    if($d_id == -1) {
        return 'tbl_record_0';
    }

    if($s_id == -1) {
        return 'tbl_record_0';
    }

    // get district info
    global $db;
    $stmt = $db->pdo->prepare("select d.table_name from tbl_district as d where id =" . $d_id);
    $table_name = "";
    if ($stmt->execute()) {
        $row = $stmt->fetch();
        if (count($row) > 0) {
            $table_name = $row['table_name'];
        } else {
            $table_name = "";
        }
    }
    return $table_name;
}

function reports()
{
    try {
        global $db;
        $d_id = isset($_REQUEST['d_id']) ? urldecode(($_REQUEST['d_id'])) : "";
        if ($d_id == "") {
            $data = array('success' => false, 'message' => 'Invalid data');
            echo json_encode($data);
            exit;
        }
        $s_id = isset($_REQUEST['s_id']) ? urldecode($_REQUEST['s_id']) : "";
        if ($s_id == "") {
            $data = array('success' => false, 'message' => 'Invalid data');
            echo json_encode($data);
            exit;
        }
        $table_name = get_table_name($d_id, $s_id);

        if($table_name == "") {
            // return;
        }
        // select questions
        $query = "select q.id as q_id, q.prefix as q_prefix, q.content as q_content, 
                sq.id as sq_id, sq.order as sq_order, sq.content as sq_content, 
                a.id as a_id, a.view_order as a_order, a.content as a_content, a.ag_id as ag_id,
                r.percent as percent, r.n as n, r.dk as dk, r.non_dk as non_dk, 
                c.name as cat_name  
                from ".$table_name." as r 
                left join tbl_question as q on r.question_id = q.id 
                left join tbl_sub_question as sq on r.sub_question_id = sq.id 
                left join tbl_answer as a on r.answer_id = a.id
                left join tbl_category as c on r.category_id = c.id
                where r.school_id = ".$s_id." and r.district_id = ".$d_id."
                order by q_id ASC, cast(sq_order as unsigned integer) ASC, cast(a_order as unsigned integer) ASC";

        $db->pdo->query('SET CHARACTER SET utf8');

        $stmt = $db->pdo->prepare($query);
        if ($stmt->execute()) {
            $rows_q = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $res = array();
            if (count($rows_q) > 0) {
                $q_prefix = "";
                $q_array = array();
                $q_array['answer'] = array();
                $q_array['answer_d'] = array();
                $q_array['answer_header'] = array();
                $q_array['subs'] = array();
                $ag_id = -1;

                $rows_dr = array();
                $rows_sr = array();

                if($d_id != -1) {
                    // district info
                    $query1 = "select q.id as q_id, 
                            sq.id as sq_id, sq.order as sq_order, 
                            a.id as a_id, a.content as a_content,
                            r.percent as percent, r.n as n, r.dk as dk, r.non_dk as non_dk 
                            from tbl_record_0 as r 
                            left join tbl_question as q on r.question_id = q.id 
                            left join tbl_sub_question as sq on r.sub_question_id = sq.id 
                            left join tbl_answer as a on r.answer_id = a.id
                            left join tbl_category as c on r.category_id = c.id
                            where r.district_id = " . $d_id . " 
                            order by q_id ASC, cast(sq.order as unsigned integer) ASC, cast(a.view_order as unsigned integer) ASC";

                    $stmt_dis = $db->pdo->prepare($query1);

                    if($stmt_dis->execute()) {
                        $rows_dr = $stmt_dis->fetchAll(PDO::FETCH_ASSOC);
                    }

                    // state info
                    $query2 = "select q.id as q_id, 
                            sq.id as sq_id,  sq.order as sq_irder,
                            a.id as a_id, a.content as a_content,
                            r.percent as percent, r.n as n, r.dk as dk, r.non_dk as non_dk 
                            from tbl_record_0 as r 
                            left join tbl_question as q on r.question_id = q.id 
                            left join tbl_sub_question as sq on r.sub_question_id = sq.id 
                            left join tbl_answer as a on r.answer_id = a.id
                            left join tbl_category as c on r.category_id = c.id
                            where r.district_id = -1 
                            order by q_id ASC, cast(sq.order as unsigned integer) ASC, cast(a.view_order as unsigned integer) ASC";

                    $stmt_st = $db->pdo->prepare($query2);
                    if($stmt_st->execute()) {
                        $rows_sr = $stmt_st->fetchAll(PDO::FETCH_ASSOC);
                    }
                }


                foreach ($rows_q as $q) {
                    // create A
                    $qa = array();
                    $qa_d = array();
                    $qa['content'] = $q['a_content'];
                    $qa['percent'] = $q['percent'];
                    $qa['n'] = $q['n'];
                    $qa['dk'] = $q['dk'];
                    $qa['non_dk'] = $q['non_dk'];
                    if ($ag_id == -1)
                        $ag_id = $q['ag_id'];

                    // get district info
                    $dr = null;
                    if($d_id != -1)
                        $dr = get_district_report($rows_dr, $q['q_id'], $q['sq_id'], $q['a_id']);

                    if($dr == null) {
                        $qa_d['content'] = "";
                        $qa_d['percent'] = 0;
                        $qa_d['n'] = 0;
                        $qa_d['dk'] = 0;
                        $qa_d['non_dk'] = 0;
                    } else {
                        $qa_d['content'] = $dr['a_content'];
                        $qa_d['percent'] = $dr['percent'];
                        $qa_d['n'] = $dr['n'];
                        $qa_d['dk'] = $dr['dk'];
                        $qa_d['non_dk'] = $dr['non_dk'];
                    }

                    // get state info

                    $sr = null;
                    if($d_id != -1)
                        $sr = get_state_report($rows_sr, $q['q_id'], $q['sq_id'], $q['a_id']);

                    if($sr == null) {
                        $qa_s['content'] = "";
                        $qa_s['percent'] = 0;
                        $qa_s['n'] = 0;
                        $qa_s['dk'] = 0;
                        $qa_s['non_dk'] = 0;
                    } else {
                        $qa_s['content'] = $sr['a_content'];
                        $qa_s['percent'] = $sr['percent'];
                        $qa_s['n'] = $sr['n'];
                        $qa_s['dk'] = $sr['dk'];
                        $qa_s['non_dk'] = $sr['non_dk'];
                    }

                    if ($q_prefix != $q['q_prefix']) {
                        // push Q
                        if (count($q_array['answer']) > 0 || count($q_array['subs']) > 0) {
                            // get answer header
                            $stmt_a = $db->pdo->prepare("select content, style from tbl_answer where ag_id = ".$ag_id." order by view_order asc");
                            if ($stmt_a->execute()) {
                                $rows_a = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($rows_a as $a) {
                                    array_push($q_array['answer_header'], $a);
                                }
                            }
                            array_push($res, $q_array);
                            $ag_id = $q['ag_id'];
                        }

                        // create Q
                        $q_array = array();
                        $q_array['prefix'] = $q['q_prefix'];
                        $q_array['content'] = $q['q_content'];
                        $q_array['category'] = $q['cat_name'];
                        $q_array['answer'] = array();
                        $q_array['answer_header'] = array();
                        $q_array['subs'] = array();

                        $q_prefix = $q['q_prefix'];
                    }
                    if ($q['sq_order'] == "") {
                        $q_array['answer'][$q['a_order']] = $qa;
                        $q_array['answer_d'][$q['a_order']] = $qa_d;
                        $q_array['answer_s'][$q['a_order']] = $qa_s;
                    } else {
                        $alphabet = range('a', 'z');
                        $q_array['subs'][$q['sq_order']]['content'] = $alphabet[$q['sq_order'] - 1].'. '.$q['sq_content'];
                        $q_array['subs'][$q['sq_order']]['answer'][$q['a_order']] = $qa;
                        $q_array['subs'][$q['sq_order']]['answer_d'][$q['a_order']] = $qa_d;
                        $q_array['subs'][$q['sq_order']]['answer_s'][$q['a_order']] = $qa_s;
                    }
                }

                if (count($q_array['answer']) > 0 || count($q_array['subs']) > 0) {
                    $stmt_a = $db->pdo->prepare("select content, style from tbl_answer where ag_id = ".$ag_id." order by view_order asc");
                    if ($stmt_a->execute()) {
                        $rows_a = $stmt_a->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($rows_a as $a) {
                            array_push($q_array['answer_header'], $a);
                        }
                    }

                    array_push($res, $q_array);
                }

                $data = array('success' => true, 'school_id' => $s_id, 'district_id' => $d_id, 'result' => $res);

                echo json_encode($data, true);
                exit;

            } else {
                $data = array('success' => false, 'message' => 'Invalid data');
                echo json_encode($data);
                exit;
            }
        }
    } catch (PDOException $e) {
        $data = array('success' => false, 'message' => $e->getMessage());
        echo json_encode($data);
        exit();
    }
}

function agree_reports() {
    try {
        // get Agree and Strongly Agree id
        global $db;
        $d_id = isset($_REQUEST['d_id']) ? urldecode(($_REQUEST['d_id'])) : "";
        if ($d_id == "") {
            $data = array('success' => false, 'message' => 'Invalid data');
            echo json_encode($data);
            exit;
        }
        $s_id = isset($_REQUEST['s_id']) ? urldecode($_REQUEST['s_id']) : "";
        if ($s_id == "") {
            $data = array('success' => false, 'message' => 'Invalid data');
            echo json_encode($data);
            exit;
        }

        $stmt_a = $db->pdo->prepare('select id from tbl_answer where content="Agree" OR content="Strongly agree"');
        if(!$stmt_a->execute()) {
            $data = array('success' => false, 'message' => "Cannot find Agree and Strongly agree from Answer Table." );
            echo json_encode($data);
            exit();
        }
        $a = $stmt_a->fetchAll(PDO::FETCH_ASSOC);

        if(count($a) > 0) {
            $id1 = $a[0]['id'];
            $id2 = $a[1]['id'];
        } else {
            $data = array('success' => false, 'message' => "Cannot find Agree and Strongly agree from Answer Table." );
            echo json_encode($data);
            exit();
        }

        // get district info
        $table_name = get_table_name($d_id, $s_id);

        $query = "select q.id as q_id, q.prefix as q_prefix, q.content as q_content, 
                sq.id as sq_id, sq.order as sq_order, sq.content as sq_content, 
                a.id as a_id, a.view_order as a_order, a.content as a_content,
                sum(r.percent) as percent, 
                c.name as cat_name  
                from ".$table_name." as r 
                left join tbl_question as q on r.question_id = q.id 
                left join tbl_sub_question as sq on r.sub_question_id = sq.id 
                left join tbl_answer as a on r.answer_id = a.id
                left join tbl_category as c on r.category_id = c.id
                where r.school_id =".$s_id."  and r.district_id = ".$d_id." and (a.id = ".$id1." OR a.id = ".$id2.") 
                group by sq_content, q_content order by q_id ASC, cast(sq_order as unsigned integer) ASC, cast(a_order as unsigned integer) ASC ";

        $db->pdo->query('SET CHARACTER SET utf8');

        $stmt_q = $db->pdo->prepare($query);

        if(!$stmt_q->execute()) {
            $data = array('success' => false, 'message' => "Cannot find Agree and Strongly agree from Answer Table." );
            echo json_encode($data);
            exit();
        }

        $rows_q = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

        $rows_dr = array();
        $rows_sr = array();

        if($d_id != -1) {
            $query = "select q.id as q_id, q.prefix as q_prefix, q.content as q_content, 
                sq.id as sq_id, sq.order as sq_order, sq.content as sq_content, 
                a.id as a_id, a.view_order as a_order, a.content as a_content,
                sum(r.percent) as percent, 
                c.name as cat_name  
                from tbl_record_0 as r 
                left join tbl_question as q on r.question_id = q.id 
                left join tbl_sub_question as sq on r.sub_question_id = sq.id 
                left join tbl_answer as a on r.answer_id = a.id
                left join tbl_category as c on r.category_id = c.id
                where r.district_id =".$d_id."  and (a.id = ".$id1." OR a.id = ".$id2.") 
                group by sq_content, q_content order by q_id ASC, cast(sq_order as unsigned integer) ASC, cast(a_order as unsigned integer) ASC ";

            $stmt_d = $db->pdo->prepare($query);
            if(!$stmt_d->execute()) {
                $data = array('success' => false, 'message' => "Cannot find Agree and Strongly agree from Answer Table." );
                echo json_encode($data);
                exit();
            }
            $rows_dr = $stmt_d->fetchAll(PDO::FETCH_ASSOC);

            // state
            $query = "select q.id as q_id, q.prefix as q_prefix, q.content as q_content, 
                sq.id as sq_id, sq.order as sq_order, sq.content as sq_content, 
                a.id as a_id, a.view_order as a_order, a.content as a_content,
                sum(r.percent) as percent, 
                c.name as cat_name  
                from tbl_record_0 as r 
                left join tbl_question as q on r.question_id = q.id 
                left join tbl_sub_question as sq on r.sub_question_id = sq.id 
                left join tbl_answer as a on r.answer_id = a.id
                left join tbl_category as c on r.category_id = c.id
                where r.district_id = -1  and (a.id = ".$id1." OR a.id = ".$id2.") 
                group by sq_content, q_content order by q_id ASC, cast(sq_order as unsigned integer) ASC, cast(a_order as unsigned integer) ASC ";

            $stmt_s = $db->pdo->prepare($query);
            if(!$stmt_s->execute()) {
                $data = array('success' => false, 'message' => "Cannot find Agree and Strongly agree from Answer Table." );
                echo json_encode($data);
                exit();
            }
            $rows_sr = $stmt_s->fetchAll(PDO::FETCH_ASSOC);
        }

        $q_prefix = "";
        $q_array = array();
        $q_array['answer'] = array();
        $q_array['answer_d'] = array();
        $q_array['answer_s'] = array();
        $q_array['answer_header'] = array();
        $q_array['subs'] = array();

        $res = array();

        foreach($rows_q as $q) {
            $qa = array();
            $qa_d = array();
            $qa_s = array();
            $qa['content'] = $q['a_content'];
            $qa['percent'] = $q['percent'];

            $dr = null;
            $sr = null;
            if($d_id != -1)
                $dr = get_district_report($rows_dr, $q['q_id'], $q['sq_id'], $q['a_id']);

            $sr = get_district_report($rows_sr, $q['q_id'], $q['sq_id'], $q['a_id']);

            if($dr == null) {
                $qa_d['content'] = "";
                $qa_s['content'] = "";
            } else {
                $qa_d['content'] = $dr['a_content'];
                $qa_d['percent'] = $dr['percent'];
                $qa_s['content'] = $sr['a_content'];
                $qa_s['percent'] = $sr['percent'];
            }

            if($q_prefix != $q['q_prefix']) {

                if (count($q_array['answer']) > 0 || count($q_array['subs']) > 0) {
                    array_push($res, $q_array);
                }

                $q_array = array();
                $q_array['prefix'] = $q['q_prefix'];
                $q_array['content'] = $q['q_content'];
                $q_array['category'] = $q['cat_name'];
                $q_array['answer'] = array();
                $q_array['answer_d'] = array();
                $q_array['answer_s'] = array();
                $q_array['subs'] = array();

                $q_prefix = $q['q_prefix'];
            }
            if ($q['sq_order'] == "") {
                $q_array['answer'][$q['a_order']] = $qa;
                if($d_id != -1)
                    $q_array['answer_d'][$q['a_order']] = $qa_d;
                    $q_array['answer_s'][$q['a_order']] = $qa_s;
            } else {
                $q_array['subs'][$q['sq_order']]['content'] = $q['sq_content'];
                $q_array['subs'][$q['sq_order']]['answer'][$q['a_order']] = $qa;
                if($d_id != -1)
                    $q_array['subs'][$q['sq_order']]['answer_d'][$q['a_order']] = $qa_d;
                
                    $q_array['subs'][$q['sq_order']]['answer_s'][$q['a_order']] = $qa_s;
            }
        }
        if (count($q_array['answer']) > 0 || count($q_array['subs']) > 0) {
            array_push($res, $q_array);
        }

        $data = array('success' => true, 'school_id' => $s_id, 'district_id' => $d_id, 'result' => $res);
        echo json_encode($data, true);
        exit;


    } catch (PDOException $e) {
        $data = array('success' => false, 'message' => $e->getMessage());
        echo json_encode($data);
        exit();
    }

}

function get_total_rate() {
    try {
        global $db;

        $query = "select sum(invitees) as total_invitees, sum(respondents) as total_respondents from tbl_rates";
        $db->pdo->query('SET CHARACTER SET utf8');

        $stmt = $db->pdo->prepare($query);

        $res = array();

        if ($stmt->execute()) {
            $row = $stmt->fetch();
            if (count($row) > 0) {
                $res['invitees'] = $row['total_invitees'];
                $res['respondents'] = $row['total_respondents'];
            } else {

            }
        }

        $data = array('success' => true, 'result' => $res);

        echo json_encode($data, true);
        exit;

    } catch (PDOException $e){
        $data = array('success' => false, 'message' => $e->getMessage());
        echo json_encode($data);
        exit();
    }

}

function get_district_info($district_id, $district_name) {
    $data = array();
    try {
        global $db;
        global $year;
        $report_url = 'report.php?s_id=-1&d_id=' . urlencode($district_id);
        $agree_report_url = 'agree-report.php?s_id=-1&d_id=' . urlencode($district_id);

        $data['district'] = $district_name;

        $data['school'] = ' District report';

        $stmt = $db->pdo->prepare("select sum(r.invitees) as invitees, sum(r.respondents) as respondents 
                                        from tbl_rates as r left join tbl_school as s on r.school_id = s.id where r.year=".$year." and s.district_id=".$district_id);

        if($stmt->execute()) {
            $row = $stmt->fetch();
            if(count($row) > 0) {
                $data['total_invitees'] = $row['invitees'];
                $data['respondents'] = $row['respondents'];
            } else {
                $data['total_invitees'] = 0;
                $data['respondents'] = 0;
            }
        }

        if($data['total_invitees'] == null || $data['total_invitees'] == 0) {
            $respondents_pert = 0;
        } else {
            $respondents_pert = round($data['respondents'] / $data['total_invitees'] * 100, 2);
        }

        $data['reports'] = '<a target="_blank" href="' . $report_url . '"><i class="fa fa-tasks"></i></a>';
        $data['reports'] .= '<a target="_blank" href="' . $agree_report_url . '"><i class="fa fa-table"></i></a>';

        $data['respondents_pert'] = $respondents_pert;

    } catch (PDOException $e) {
        $data = array();
        $data['district'] = $district_name;
        $data['school'] = '';
        $data['total_invitees'] = 0;
        $data['respondents'] = 0;
        $data['respondents_pert'] = 0;
        $data['reports'] = '';
    }
    return $data;
}
?>