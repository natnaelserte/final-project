<div class="modal fade" id="delete_user<?php echo $candidate_id ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="delete_candidate.php">
                <div class="modal-header">
                    <h4 class="modal-title" id="myModalLabel">
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <center>Delete Candidate</center>
                            </div>
                        </div>
                    </h4>
                </div>

                <div class="modal-body">
                    Are you sure you want to delete this candidate?
                    <!-- Pass the candidate ID securely -->
                    <input type="hidden" name="candidate_id" value="<?php echo htmlspecialchars($candidate_id); ?>">
                </div>

                <div class="modal-footer">
                    <button type="submit" name="delete" class="btn btn-danger">
                        <i class="icon-check"></i>&nbsp;Yes
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">
                        <i class="icon-remove icon-large"></i>&nbsp;Close
                    </button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
