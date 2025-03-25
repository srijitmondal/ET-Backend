<?php
header("Access-Control-Allow-Origin: http://localhost:8081");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Accept, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
session_start();
// if (!isset($_SESSION['userid'])) {
//     echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
//     exit();
// }

// Database connection settings
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "geoma7i3_geomaticx_et_dms"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Get the user ID from the session
$user_id = $_SESSION['userid'];

// SQL query to fetch all leaves for the user
$sql = "SELECT * FROM leave_track_details WHERE leave_track_created_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$jsonData = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Map leave_ground values to human-readable text
        $leaveType = "";
        switch ($row['leave_ground']) {
            case 0:
                $leaveType = "Casual Leave";
                break;
            case 1:
                $leaveType = "Medical Leave";
                break;
            case 2:
                $leaveType = "Half Day Leave";
                break;
            default:
                $leaveType = "Unknown";
        }

        // Map leave_track_status values to human-readable text
        $status = "";
        switch ($row['leave_track_status']) {
            case null:
                $status = "Unattended";
                break;
            case 0:
                $status = "Rejected";
                break;
            case 1:
                $status = "Approved";
                break;
            case 2:
                $status = "Suspended";
                break;
            default:
                $status = "Unknown";
        }

        // Format the data for JSON response
        $jsonData[] = array(
            'leave_id' => $row['leave_id'],
            'leave_title' => $row['leave_title'],
            'leave_ground' => $row['leave_ground'],
            'leave_ground_text' => $leaveType,
            'leave_from_date' => $row['leave_from_date'],
            'leave_to_date' => $row['leave_to_date'],
            'leave_comment' => $row['leave_comment'],
            'leave_acpt_rql_remarks' => $row['leave_acpt_rql_remarks'],
            'leave_track_status' => $row['leave_track_status'],
            'leave_track_status_text' => $status,
            'leave_track_created_by' => $row['leave_track_created_by'],
            'leave_track_created_at' => $row['leave_track_created_at'],
            'leave_track_updated_at' => $row['leave_track_updated_at'],
            'leave_track_submitted_to' => $row['leave_track_submitted_to'],
            'leave_track_approved_rejected_by' => $row['leave_track_approved_rejected_by'],
            'leave_track_approved_rejected_at' => $row['leave_track_approved_rejected_at']
        );
    }
} else {
    $jsonData = ['status' => 'error', 'message' => 'No leave records found'];
}

// Close connection
$conn->close();

// Send JSON response
echo json_encode($jsonData);
?>