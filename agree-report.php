<!DOCTYPE html>
<html lang="en">
<?php
require_once('header.php');
require_once('includes/config.php');

global $db;

$school_name = "";
$district_name = "";

//create URL
    
$school_id = isset($_REQUEST['s_id']) ? urldecode($_REQUEST['s_id']) : "";
if ($school_id == "") {
    header('Location:' . BASE_URL . '/index.php');
    exit();
}

$district_id = isset($_REQUEST['d_id']) ? urldecode($_REQUEST['d_id']) : "";
if ($district_id == "") {
    header('Location' . BASE_URL . '/index.php');
    exit();
}


$school_name = "";
$district_name = "";
	

// get data about school and district
if($school_id != -1 && $district_id != -1) {
	$stmt = $db->pdo->prepare("select s.name as school_name, d.name as district_name from tbl_school as s left join tbl_district as d on s.district_id = d.id where s.id=".$school_id." and d.id=".$district_id);
	if($stmt->execute()) {
			$tmp = $stmt->fetch();
			if(count($tmp) > 0) {
					$school_name = $tmp['school_name'];
					$district_name = $tmp['district_name'];
			} else {
					// return
					header('Location'.BASE_URL.'/index.php');
					exit();
			}
	}
} else if($school_id == -1 && $district_id != -1) {
	$stmt = $db->pdo->prepare("select name from tbl_district where id=".$district_id);
	if($stmt->execute()) {
			$tmp = $stmt->fetch();
			if(count($tmp) > 0) {
					$district_name = $tmp['name'];
			} else {
					header('Location'.BASE_URL.'/index.php');
					exit(); 
			}
	}
}

?>
<body class="dash" id="printable">
<nav class="navbar sticky-top navbar-dark bg-dark">
    <a class="navbar-brand" href="https://nc2012.asqnc.com"><h3> <span class="year"><?php echo $year; ?></span> NC TWC Survey - Home</h3>
    </a>
</nav
<div class="page-wrapper">
    <!-- Page Content-->
    <div class="page-content">
        <div class="container">

            <div class="row">
                <div class="col-sm-12">
                    <div class="page-title-box">
                        <h3 class="page-title">NC TWC <span class="year"><?php echo $year; ?></span>  Survey (% Agree) Analysis </h3>
                        <?php if($district_id != -1) { ?>
                            <h4 class="school-name">District : <?php echo $district_name; ?></h4>
                        <?php } ?>

                        <?php if($school_id != -1) { ?>
                            <h4 class="school-name">  School : <?php echo $school_name; ?></h4>
                        <?php } ?>

                        <div class="q_legend_info col-12">
                            <div class="row">
                                <div class="col-md-3 legend-title-col"><span class="legendBox range27"></span>Under 29%</div>
                                <div class="col-md-3 legend-title-col"><span class="legendBox range48"></span>30%-49%</div>
                                <div class="col-md-3 legend-title-col"><span class="legendBox range69"></span>50%-69%</div>
                                <div class="col-md-3 legend-title-col"><span class="legendBox range100"></span>Above 70%</div>
                            </div>
                        </div>
                    <input type="hidden" id="hide_school_id" name="hide_school_id" value="<?php echo $school_id; ?>"/> 
                        <input type="hidden" id="hide_school_name" name="hide_school_name" value="<?php echo $school_name; ?>"/>
                     
                        <input type="hidden" id="hide_district_id" name="hide_district_id" value="<?php echo $district_id; ?>"/>
                        <input type="hidden" id="hide_district_name" name="hide_district_name" value="<?php echo $district_name; ?>"/>
                        <a target="_blank" class="btn btn-primary " onclick="ExportPdf();">EXPORT TO PDF</a>
                    </div>
                </div>
            </div>
            <div class="row ">
                <div class="col-md-12 loading-wrap">
                    <div class="spinner-border text-danger" style="width: 3rem; height: 3rem;" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div class="col-md-12 reports-col">

                </div> <!-- end col -->
            </div> <!-- end row -->

        </div><!-- container -->
    </div>
    <!-- end page content -->
</div>
<!-- end page-wrapper -->
<?php require_once('footer.php') ?>
<script>
    var base_url = "<?php echo BASE_URL ?>";
</script>
<script src="js/agree-report.js?v=1.1.1"></script>
<script src="https://kendo.cdn.telerik.com/2017.2.621/js/jszip.min.js"></script>
<script src="https://kendo.cdn.telerik.com/2017.2.621/js/kendo.all.min.js"></script>
<script>

    function ExportPdf() {
        var draw = kendo.drawing;
        draw.drawDOM($("#printable"), {
            avoidLinks: true,
            paperSize: "A4",
            margin: {top: "1cm"},
            landscape: false,
            scale: 0.5,
            keepTogether: ".prevent-split"
            //template: $("#page-template").html()
        })
            .then(function (root) {
                return draw.exportPDF(root);
            })
            .done(function (data) {
                kendo.saveAs({
                    dataURI: data,
                    fileName: "2012NCTWC_SurveyResults.pdf"
                });
            });
    }
</script>
</body>
</html>
