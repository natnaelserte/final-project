<?php include('session.php'); ?>
<?php include('head.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<body>
    <div id="wrapper">

        <!-- Navigation -->
        <?php include('side_bar.php'); ?>

        <!-- Page Content -->
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h3 class="page-header">Import From CSV Files</h3>
                </div>
            </div> <!-- Close the first row -->

            <div class="row">  <!-- Add a new row for the panel -->
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                        </div>
                        <!-- /.panel-heading -->
                        <div class="panel-body">
                            <div class="table-responsive">

                                <div class="containerl" style="margin-top: 30px">
                                    <form class="form-horizontal well" action="import.php" method="post" enctype="multipart/form-data">
                                        <fieldset>
                                            <legend>Import CSV File</legend>

                                            <div class="form-group">
                                                <label for="filename" class="control-label">CSV:</label>
                                                <div class="controls">
                                                    <input type="file" name="filename" id="filename" class="form-control">
                                                </div>
                                            </div>

                                            <br>

                                            <div class="form-group text-center">
                                                <div class="controls">
                                                    <button type="submit" name="submit" ">Upload</button>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </form>
                                </div>

                            </div>
                            <!-- /.table-responsive -->
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div> <!-- Close the col-lg-12 for the panel -->
            </div> <!-- Close the new row for the panel -->
        </div>
        <!-- /#page-wrapper -->
    </div>
    <!-- /#wrapper -->

    <?php include('script.php'); ?>

</body>
</html>