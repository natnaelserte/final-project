<?php
// This file is intended to be included where $all_positions_for_dropdown is available.
// If not, you might need to pass it or query it again, but it's better if the parent script provides it.
// For simplicity, we assume $all_positions_for_dropdown is in scope from the parent.
?>
<!-- UNIVERSAL EDIT CANDIDATE MODAL -->
<div class="modal fade" id="universalEditCandidateModal" tabindex="-1" role="dialog" aria-labelledby="editCandidateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="editCandidateModalLabel">Edit Candidate</h4>
            </div>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" id="editCandidateForm">
                <div class="modal-body">
                    <input type="hidden" name="candidate_id" id="edit_candidate_id_field">
                    <input type="hidden" name="current_image_path" id="edit_current_image_path_field">

                    <div class="form-group">
                        <label for="edit_position_field">Position <span class="text-danger">*</span></label>
                        <select class="form-control" name="position" id="edit_position_field" required>
                            <option value="" disabled>Select Position</option>
                            <?php
                            // Ensure $all_positions_for_dropdown is available from the including script
                            if (!empty($all_positions_for_dropdown)) {
                                foreach ($all_positions_for_dropdown as $pos_item) {
                                    echo "<option value='" . htmlspecialchars($pos_item['position_id']) . "'>" . htmlspecialchars($pos_item['position_name']) . "</option>";
                                }
                            } else {
                                echo "<option value=''>No positions available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_slogan_field">Slogan <span class="text-danger">*</span></label>
                        <input class="form-control" name="slogan" id="edit_slogan_field" type="text" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_firstname_field">Firstname <span class="text-danger">*</span></label>
                        <input class="form-control" name="firstname" id="edit_firstname_field" type="text" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_lastname_field">Lastname <span class="text-danger">*</span></label>
                        <input class="form-control" name="lastname" id="edit_lastname_field" type="text" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_year_level_field">Year Level <span class="text-danger">*</span></label>
                        <select class="form-control" name="year_level" id="edit_year_level_field" required>
                            <option value="">Select Year Level</option>
                            <option value="1st Year">1st Year</option>
                            <option value="2nd Year">2nd Year</option>
                            <option value="3rd Year">3rd Year</option>
                            <option value="4th Year">4th Year</option>
                            <option value="5th Year">5th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_gender_field">Gender <span class="text-danger">*</span></label>
                        <select class="form-control" name="gender" id="edit_gender_field" required>
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Current Image:</label><br>
                        <img src="path/to/default/image.png" id="edit_current_image_preview" width="100" alt="Current Candidate Image" class="img-thumbnail" style="margin-bottom:10px; display:none;"><br>
                        <label for="edit_new_image_field">New Image (Optional - Max 2MB)</label>
                        <input type="file" name="image" id="edit_new_image_field" class="form-control-file" accept="image/jpeg,image/png,image/gif">
                        <small class="form-text text-muted">Leave blank to keep current image. Allowed: JPG, PNG, GIF.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" name="update_candidate_submit" class="btn btn-primary">Update Candidate</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END: UNIVERSAL EDIT CANDIDATE MODAL -->