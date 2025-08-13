<?php
/**
 * Script to apply modern theme to all admin pages
 * This script will add the modern CSS link to all PHP files in the admin directory
 */

// List of admin pages to update
$adminPages = [
    'activate_accounts.php',
    'activate_faculty.php', 
    'activate_voter.php',
    'add_student.php',
    'add_user_id.php',
    'current_students.php',
    'current_students2.php',
    'deactivate_accounts.php',
    'deactivate_faculty.php',
    'deactivate_voter.php',
    'voted.php',
    'unvoted.php'
];

// Function to add modern CSS link to a file
function addModernCSS($filePath) {
    if (!file_exists($filePath)) {
        echo "File not found: $filePath\n";
        return false;
    }
    
    $content = file_get_contents($filePath);
    
    // Check if modern CSS is already included
    if (strpos($content, 'modern-admin.css') !== false) {
        echo "Modern CSS already included in: $filePath\n";
        return true;
    }
    
    // Look for head.php include and add CSS after it
    if (strpos($content, "include('head.php')") !== false || strpos($content, 'include("head.php")') !== false) {
        // Add CSS link after head.php include
        $cssLink = "\n    <!-- Modern Admin Theme CSS -->\n    <link rel=\"stylesheet\" href=\"css/modern-admin.css\">\n";
        
        // Find the position after head.php include
        $headPos = strpos($content, "include('head.php')");
        if ($headPos === false) {
            $headPos = strpos($content, 'include("head.php")');
        }
        
        if ($headPos !== false) {
            // Find the end of the line
            $lineEnd = strpos($content, "\n", $headPos);
            if ($lineEnd !== false) {
                $content = substr_replace($content, $cssLink, $lineEnd, 0);
                file_put_contents($filePath, $content);
                echo "Added modern CSS to: $filePath\n";
                return true;
            }
        }
    }
    
    // Alternative: Look for </head> tag and add before it
    if (strpos($content, '</head>') !== false) {
        $cssLink = "    <!-- Modern Admin Theme CSS -->\n    <link rel=\"stylesheet\" href=\"css/modern-admin.css\">\n</head>";
        $content = str_replace('</head>', $cssLink, $content);
        file_put_contents($filePath, $content);
        echo "Added modern CSS to: $filePath\n";
        return true;
    }
    
    echo "Could not add modern CSS to: $filePath (no suitable insertion point found)\n";
    return false;
}

// Function to update page headers
function updatePageHeader($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    
    // Replace old page headers with modern ones
    $patterns = [
        '/<h1 class="page-header">(.*?)<\/h1>/s' => '<div class="modern-page-header"><h1>$1</h1></div>',
        '/<h2 class="page-header">(.*?)<\/h2>/s' => '<div class="modern-page-header"><h2>$1</h2></div>',
        '/<h3 class="page-header">(.*?)<\/h3>/s' => '<div class="modern-page-header"><h3>$1</h3></div>',
    ];
    
    $updated = false;
    foreach ($patterns as $pattern => $replacement) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
            $updated = true;
        }
    }
    
    if ($updated) {
        file_put_contents($filePath, $content);
        echo "Updated page header in: $filePath\n";
        return true;
    }
    
    return false;
}

// Function to update panels to modern styling
function updatePanels($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $content = file_get_contents($filePath);
    
    // Replace panel classes with modern equivalents
    $replacements = [
        'panel panel-primary' => 'panel modern-table-panel',
        'panel panel-default' => 'panel modern-chart-panel',
        'class="table table-striped table-bordered table-hover"' => 'class="table table-striped table-bordered table-hover modern-table"',
    ];
    
    $updated = false;
    foreach ($replacements as $search => $replace) {
        if (strpos($content, $search) !== false) {
            $content = str_replace($search, $replace, $content);
            $updated = true;
        }
    }
    
    if ($updated) {
        file_put_contents($filePath, $content);
        echo "Updated panels in: $filePath\n";
        return true;
    }
    
    return false;
}

// Main execution
echo "Starting modern theme application...\n\n";

foreach ($adminPages as $page) {
    $filePath = __DIR__ . '/' . $page;
    echo "Processing: $page\n";
    
    addModernCSS($filePath);
    updatePageHeader($filePath);
    updatePanels($filePath);
    
    echo "Completed: $page\n\n";
}

echo "Modern theme application completed!\n";
echo "\nNote: You may need to manually adjust some specific styling in individual files.\n";
echo "The following files have been updated with:\n";
echo "- Modern CSS link\n";
echo "- Modern page headers\n";
echo "- Modern panel classes\n";
?>
