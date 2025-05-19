<?php
require_once 'admin/dbcon.php';
require_once 'admin2/AES/aes_config.php';

// Encrypt function (base64-encoded, same as used in vote submission)
function encrypt_candidate_id($candidate_id) {
    $candidate_id = (int)$candidate_id; // Ensure it's an integer
    return openssl_encrypt(
        $candidate_id,
        'aes-256-cbc',
        AES_KEY,
        0, // same as used during insert
        AES_IV
    );
}

try {
    // Step 1: Fetch all candidates and positions
    $stmt = $pdo->query("SELECT 
                            c.candidate_id, 
                            c.firstname, 
                            c.lastname, 
                            c.img, 
                            p.position_name 
                         FROM candidate c 
                         JOIN position p ON c.position = p.position_id");
    $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Step 2: Build candidate map with encrypted ID and initialize vote count
    $candidateMap = [];
    foreach ($candidates as $row) {
        $encryptedId = encrypt_candidate_id($row['candidate_id']);
        $candidateMap[$encryptedId] = [
            'candidate_id' => $row['candidate_id'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'img' => $row['img'],
            'position_name' => $row['position_name'],
            'vote_count' => 0
        ];
    }

    // Step 3: Fetch all votes and count them by matching encrypted ID
    $stmt = $pdo->query("SELECT candidate_id FROM votes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $encryptedId = $row['candidate_id'];
        if (isset($candidateMap[$encryptedId])) {
            $candidateMap[$encryptedId]['vote_count']++;
        }
    }

    // Step 4: Convert to list and sort by position name, then vote count descending
    $result = array_values($candidateMap);
    usort($result, function ($a, $b) {
        return $a['position_name'] <=> $b['position_name'] ?: $b['vote_count'] <=> $a['vote_count'];
    });

    // Output JSON
    header('Content-Type: application/json');
    echo json_encode($result);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    error_log('PDOException: ' . $e->getMessage());
}
?>
