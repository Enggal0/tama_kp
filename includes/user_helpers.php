<?php
/**
 * Helper functions for user profile display
 */

function getUserInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2);
}

function displayUserAvatar($userDetails, $userInitials, $size = '40px', $additionalClasses = 'user-avatar me-2') {
    if ($userDetails['profile_photo'] && file_exists("../uploads/profile_photos/" . $userDetails['profile_photo'])) {
        return '<img src="../uploads/profile_photos/' . htmlspecialchars($userDetails['profile_photo']) . '" alt="Profile" class="' . $additionalClasses . '" style="width: ' . $size . '; height: ' . $size . '; border-radius: 50%; object-fit: cover;">';
    } else {
        return '<div class="' . $additionalClasses . ' bg-primary">' . $userInitials . '</div>';
    }
}

function getUserDetails($conn, $userId) {
    $userQuery = "SELECT name, profile_photo FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    return $userResult->fetch_assoc();
}
?>
