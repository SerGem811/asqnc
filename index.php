<!DOCTYPE html>
<html lang="en">
<?php
require_once('header.php');
require_once('includes/config.php');

?>
<body class="dash">
<nav class="navbar sticky-top navbar-dark bg-dark">
    <a class="navbar-brand" href="https://asqnc.com/"><img src="ncLogo.png" id="logo" height="103px" ;/><a>
</nav>

<div class="page-wrapper">
    <!-- Page Content-->
    <div class="page-content">
        <div class="container-fluid">
            <hr/>
            <h3> <span class='year'><?php echo $year ?></span> North Carolina Teacher Working Conditions Results</h3>
            <hr/>
        </div>
        <div class="container-fluid">
            <div class="row mb-5 total-box-row">
                <div class="col-md-4 ">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h3 id="total_invitees">14,871</h3>
                            <p class="total-info">TOTAL INVITED</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h3 id="total_respondents">10,621</h3>
                            <p class="total-info">TOTAL RESPONDED</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 ">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h3 id="total_rate">71%</h3>
                            <p class="total-info">RESPONSE RATE</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row ">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <!--
                            <div class="col-md-12">
                                <h4 class="mt-0 d-inline-block">2018 ASQi HCPS Survey Results</h4>
                            </div>
                            -->
                            <div class="col-md-12">
                                <h3 class="mt-0 d-inline-block">&nbsp;&nbsp;North Carolina - NC</h3>
                                <a target="_blank" href="report.php?s_id=-1&d_id=-1" style="font-size: 20px;margin-left: 10px;"><i class="fa fa-tasks"></i></a>
                                <a target="_blank" href="agree-report.php?s_id=-1&d_id=-1" style="font-size: 20px;margin-left: 5px;"><i class="fa fa-table"></i></a>
                            </div>

                            <p></p>
                            <div class="table-responsive mb-0">
                                <table id="tbl-school-list" class="table  table-bordered">
                                    <thead>
                                    <tr>
                                        <th>District</th>
                                        <th>School</th>
                                        <th>Report</th>
                                        <th>Total Invitees</th>
                                        <th>Respondents</th>
                                        <th>Respondents %</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
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
<script src="js/script.js"></script>
</body>
</html>