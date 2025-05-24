<?php

?>
<style>
   

    #edit_user<?php echo $user_id; ?> .modal-dialog {
        width: 50vw; /* 50% of viewport width */
        max-width: 50vw; /* Override Bootstrap's default max-width */
        margin-top: 5vh; /* Some top margin */
        margin-bottom: 5vh;
    }

    /* Your .mod class, applied to .modal-content */
    #edit_user<?php echo $user_id; ?> .mod { /* Be more specific to avoid unintended side-effects */
        width: 100%; /* Fill the .modal-dialog */
        margin: 0; /* Centering is handled by .modal-dialog */
        /* position: absolute; << REMOVE THIS - it breaks layout */
        overflow: hidden; /* Prevent .modal-content itself from scrolling, let .modal-body scroll */
        /* align-items: center; << This is for flex/grid children, not usually for .modal-content itself */
        
        /* For managing height and internal scrolling */
        max-height: 85vh; /* Limit the modal's total height */
        display: flex;
        flex-direction: column;
    }

    /* Make the modal-body scrollable if content is too tall */
    #edit_user<?php echo $user_id; ?> .modal-body {
        overflow-y: auto;
        flex-grow: 1; /* Allows modal-body to take available vertical space */
    }

    /* Your .form style - adjust as needed */
    #edit_user<?php echo $user_id; ?> .form {
        display: flex;
        flex-direction: column; /* To stack form groups vertically */
        gap: 15px; /* Adds space between form groups */
    }
    /* Ensure form-groups take full width inside the flex container */
    #edit_user<?php echo $user_id; ?> .form .form-group {
        width: 100%;
        margin-bottom: 0; /* Remove default margin if using gap */
    }

</style>

<div class="modal fade" id="edit_user<?php echo $user_id; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel_<?php echo $user_id; ?>" aria-hidden="true">
    <div class="modal-dialog"> 
        <div class="modal-content mod"> 
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel_<?php echo $user_id; ?>">
                    <div class="panel panel-primary" style="margin-bottom: 0;"> 
                        <div class="panel-heading">
                            <center>Edit User Details </center>
                        </div>
                    </div>
                </h4>
            </div>
            <div class="modal-body">
                <form class="form" action="update_user.php?user_id=<?php echo htmlspecialchars($user_id); ?>" method="post">
                    <input type="hidden" name="user_id_hidden_field" value="<?php echo htmlspecialchars(isset($row['user_id']) ? $row['user_id'] : $user_id); ?>">

                    <div class="form-group">
                        <label>Username</label>
                        <input class="form-control" type="text" name="username" value="<?php echo htmlspecialchars(isset($row['username']) ? $row['username'] : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input class="form-control" type="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="form-group">
                        <label>Firstname</label>
                        <input class="form-control" type="text" name="firstname" value="<?php echo htmlspecialchars(isset($row['firstname']) ? $row['firstname'] : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Lastname</label>
                        <input class="form-control"  type="text" name="lastname" value="<?php echo htmlspecialchars(isset($row['lastname']) ? $row['lastname'] : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input class="form-control"  type="text" name="Phone" value="<?php echo htmlspecialchars(isset($row['phone']) ? $row['phone'] : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input class="form-control"  type="email" name="email" value="<?php echo htmlspecialchars(isset($row['email']) ? $row['email'] : ''); ?>">
                    </div>

                    <button name="change" type="submit" class="btn btn-primary">Save Data</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>