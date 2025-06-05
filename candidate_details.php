<?php
require_once 'admin/dbcon.php';
$is_modal = isset($_GET['modal']) && $_GET['modal'] == 1;

if (isset($_GET['id'])) {
    $candidate_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($candidate_id !== false && $candidate_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT firstname, lastname, primary_evidence_path FROM candidate WHERE candidate_id = ?");
            $stmt->execute([$candidate_id]);
            $candidate = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($candidate) {
                if (!empty($candidate['primary_evidence_path'])) {
                    $db_evidence_path = $candidate['primary_evidence_path'];

                    // === CRITICAL PATH CONSTRUCTION - ADJUST AS NEEDED ===
                    $server_file_system_path = 'admin2/' . $db_evidence_path;
                    $web_accessible_path = 'admin2/' . $db_evidence_path;
                    // ======================================================

                    if (file_exists($server_file_system_path)) {
                        $file_extension = strtolower(pathinfo($server_file_system_path, PATHINFO_EXTENSION));
                        $safe_web_path = htmlspecialchars($web_accessible_path);
                        $candidate_name = htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']);
                        $file_basename = htmlspecialchars(basename($server_file_system_path));

                        // Always prepare the download link
                        $download_link_html = '<a href="' . $safe_web_path . '" download="' . $file_basename . '" class="btn btn-success btn-sm evidence-download-btn-footer"><i class="fa fa-download"></i> Download File (' . strtoupper($file_extension) . ')</a>';

                        $output = '<div class="evidence-display-container p-0">'; // Main container

                        // Add a general title
                        $output .= '<h5 class="text-center mb-2 p-2">Evidence for: ' . $candidate_name . '</h5>';

                        // Attempt to embed the file
                        $output .= '<div class="embed-responsive embed-responsive-1by1" style="height: 70vh; min-height: 450px; background-color: #f0f0f0; border: 1px solid #ccc;">'; // Added background for non-renderable content
                        
                        // Special handling for images to use <img> for better control and alt text
                        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                             $output .= '<img src="' . $safe_web_path . '?t=' . time() . '" alt="Evidence: ' . $candidate_name . '" class="embed-responsive-item" style="object-fit: contain; max-height: 100%; max-width: 100%;">';
                        } else {
                            // For PDF and other types, use <embed>
                            // The 'type' attribute can be dynamically set based on extension for better hints to browser, but often not strictly necessary
                            $mime_type = ''; // You could implement a function to get MIME type from extension if needed
                            switch($file_extension) {
                                case 'pdf': $mime_type = 'application/pdf'; break;
                                case 'txt': $mime_type = 'text/plain'; break;
                                // Add more common MIME types if you want to be specific
                            }
                            $output .= '<embed class="embed-responsive-item" src="' . $safe_web_path . ($file_extension == 'pdf' ? '#toolbar=0&navpanes=0&scrollbar=0' : '') . '"' . ($mime_type ? ' type="' . $mime_type . '"' : '') . ' />';
                        }
                        $output .= '</div>';

                        // Add a note if embedding might be problematic
                        if (!in_array($file_extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'txt'])) { // txt might embed, but often better to download or read content
                            $output .= '<p class="text-center mt-2 mb-0 alert alert-info p-2">If the file does not display above, please use the download button. Direct browser display for <strong>.' . strtoupper($file_extension) . '</strong> files can be unreliable.</p>';
                        } elseif ($file_extension == 'txt' && !in_array($file_extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif'])) {
                            $output .= '<p class="text-center mt-2 mb-0 alert alert-info p-2">If the file does not display above, please use the download button. Direct browser display for <strong>.' . strtoupper($file_extension) . '</strong> files can be unreliable.</p>';
                        }
                        $output .= '</div>'; // close evidence-display-container

                    } else {
                        $download_link_html = '';
                        $output = '<div class="alert alert-warning m-3 p-3"><strong>File Not Found:</strong> The evidence file for ' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']) . ' was not found on the server. <br>Attempted path: <code>' . htmlspecialchars($server_file_system_path) . '</code></div>';
                    }
                } else {
                    $download_link_html = '';
                    $output = '<div class="alert alert-info m-3 p-3">No primary evidence has been submitted for ' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']) . '.</div>';
                }
            } else {
                $download_link_html = '';
                $output = '<div class="alert alert-warning m-3 p-3">Candidate details not found for the provided ID.</div>';
            }
        } catch (PDOException $e) {
            $download_link_html = '';
            // error_log("Error fetching candidate evidence (ID: $candidate_id): " . $e->getMessage());
            $output = '<div class="alert alert-danger m-3 p-3"><strong>Database Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $download_link_html = '';
         $output = '<div class="alert alert-danger m-3 p-3"><strong>Invalid ID:</strong> Candidate ID received is invalid or not positive. Value: <code>' . htmlspecialchars(var_export($candidate_id, true)) . '</code></div>';
    }
}

// Pass both the main content and the download link HTML (if available) back to AJAX
if (isset($_GET['modal']) && $_GET['modal'] == 1) {
    echo '<div id="ajaxEvidenceContentWrapper" data-download-link="' . htmlspecialchars($download_link_html) . '">' . $output . '</div>';
} else {
    // Fallback for direct access
    header("Content-Type: text/html");
    echo '<!DOCTYPE html><html lang="en"><head><title>Evidence View (Direct)</title><link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css"></head><body><div class="container mt-4"><div class="card"><div class="card-body">' . $output . '</div>';
    if ($download_link_html) {
        echo '<div class="card-footer text-right">' . $download_link_html . '</div>';
    }
    echo '</div><br><a href="javascript:history.back()" class="btn btn-light mt-3">Go Back</a></div></body></html>';
}
?>
