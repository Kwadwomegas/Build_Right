<?php
session_start();
include '../config/db.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Validate email format
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to generate the next application_id
function generateApplicationId($conn) {
    $max_attempts = 5;
    $attempt = 0;

    while ($attempt < $max_attempts) {
        // Start a transaction to ensure atomicity
        $conn->begin_transaction();

        try {
            // Lock the table to prevent concurrent writes
            $conn->query("LOCK TABLES permit_applications WRITE");

            // Fetch the last application_id
            $sql = "SELECT application_id FROM permit_applications ORDER BY application_id DESC LIMIT 1";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $last_id = $row['application_id'];
                $numeric_part = (int)substr($last_id, -4);
                $numeric_part++;
                $new_numeric_part = str_pad($numeric_part, 4, '0', STR_PAD_LEFT);
                $new_id = "X/BAT/25/" . $new_numeric_part;
            } else {
                $new_id = "X/BAT/25/0001";
            }

            // Unlock the table
            $conn->query("UNLOCK TABLES");

            // Commit the transaction
            $conn->commit();

            return $new_id;
        } catch (Exception $e) {
            // Roll back the transaction on error
            $conn->rollback();
            $conn->query("UNLOCK TABLES");
            $attempt++;

            if ($attempt == $max_attempts) {
                throw new Exception("Failed to generate application ID after $max_attempts attempts: " . $e->getMessage());
            }

            // Wait briefly before retrying to avoid race conditions
            usleep(100000); // 0.1 seconds
        }
    }

    throw new Exception("Failed to generate application ID after $max_attempts attempts");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $applicant_email = trim($_POST['applicant_email'] ?? '');

    // Validate email
    if (empty($applicant_email) || !validateEmail($applicant_email)) {
        header("Location: application.php?error=Invalid email format. Please enter a valid email address (e.g., example@domain.com).");
        exit();
    }

    // Proceed with saving the form data to the database
    $applicant_name = trim($_POST['applicant_name'] ?? '');
    $applicant_mobile = trim($_POST['applicant_mobile'] ?? '');
    $applicant_nationality = trim($_POST['applicant_nationality'] ?? '');
    $applicant_gender = trim($_POST['applicant_gender'] ?? '');
    $permit_type = trim($_POST['permit_type'] ?? '');
    $applicant_address = trim($_POST['applicant_address'] ?? '');
    $agent_name = trim($_POST['agent_name'] ?? '');
    $agent_gender = trim($_POST['agent_gender'] ?? '');
    $agent_address = trim($_POST['agent_address'] ?? '');

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Generate the application_id
        $application_id = generateApplicationId($conn);

        // Insert into permit_applications (include application_id in the query)
        $sql_permit = "INSERT INTO permit_applications (application_id, applicant_name, applicant_email, applicant_mobile, applicant_nationality, applicant_gender, permit_type, applicant_address, agent_name, agent_gender, agent_address, status, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt_permit = $conn->prepare($sql_permit);
        $stmt_permit->bind_param("sssssssssss", $application_id, $applicant_name, $applicant_email, $applicant_mobile, $applicant_nationality, $applicant_gender, $permit_type, $applicant_address, $agent_name, $agent_gender, $agent_address);

        if (!$stmt_permit->execute()) {
            throw new Exception("Failed to submit application: " . $stmt_permit->error);
        }

        // Insert into material_descriptions
        $foundations_materials = trim($_POST['foundations_materials'] ?? '');
        $foundations_proportions = trim($_POST['foundations_proportions'] ?? '');
        $walls_materials = trim($_POST['walls_materials'] ?? '');
        $walls_proportions = trim($_POST['walls_proportions'] ?? '');
        $floors_materials = trim($_POST['floors_materials'] ?? '');
        $floors_proportions = trim($_POST['floors_proportions'] ?? '');
        $floors_joint_dimension = trim($_POST['floors_joint_dimension'] ?? '');
        $floors_covering_thickness = trim($_POST['floors_covering_thickness'] ?? '');
        $windows_types = trim($_POST['windows_types'] ?? '');
        $windows_dimension = trim($_POST['windows_dimension'] ?? '');
        $doors_types = trim($_POST['doors_types'] ?? '');
        $doors_dimension = trim($_POST['doors_dimension'] ?? '');
        $roof_types = trim($_POST['roof_types'] ?? '');
        $roof_covering = trim($_POST['roof_covering'] ?? '');
        $roof_spacing_trusses = trim($_POST['roof_spacing_trusses'] ?? '');
        $steps_materials = trim($_POST['steps_materials'] ?? '');
        $verandah_materials = trim($_POST['verandah_materials'] ?? '');
        $fencing_materials = trim($_POST['fencing_materials'] ?? '');
        $yards_details = trim($_POST['yards_details'] ?? '');
        $outbuilding_details = trim($_POST['outbuilding_details'] ?? '');

        $sql_material = "INSERT INTO material_descriptions (application_id, foundations_materials, foundations_proportions, walls_materials, walls_proportions, floors_materials, floors_proportions, floors_joint_dimension, floors_covering_thickness, windows_types, windows_dimension, doors_types, doors_dimension, roof_types, roof_covering, roof_spacing_trusses, steps_materials, verandah_materials, fencing_materials, yards_details, outbuilding_details) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_material = $conn->prepare($sql_material);
        $stmt_material->bind_param("sssssssssssssssssssss", $application_id, $foundations_materials, $foundations_proportions, $walls_materials, $walls_proportions, $floors_materials, $floors_proportions, $floors_joint_dimension, $floors_covering_thickness, $windows_types, $windows_dimension, $doors_types, $doors_dimension, $roof_types, $roof_covering, $roof_spacing_trusses, $steps_materials, $verandah_materials, $fencing_materials, $yards_details, $outbuilding_details);

        if (!$stmt_material->execute()) {
            throw new Exception("Failed to insert material descriptions: " . $stmt_material->error);
        }

        // Insert into application_details
        $owner_name = trim($_POST['owner_name'] ?? '');
        $owner_address = trim($_POST['owner_address'] ?? '');
        $land_location = trim($_POST['land_location'] ?? '');
        $construction_location = trim($_POST['construction_location'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        $building_details = trim($_POST['building_details'] ?? '');
        $attached_documents = isset($_POST['documents']) ? implode(',', $_POST['documents']) : '';
        $date_of_application = trim($_POST['date_of_application'] ?? '');
        $applicant_signature = trim($_POST['applicant_signature'] ?? '');
        $witness_signature = trim($_POST['witness_signature'] ?? '');

        $sql_details = "INSERT INTO application_details (application_id, owner_name, owner_address, land_location, construction_location, purpose, building_details, attached_documents, date_of_application, applicant_signature, witness_signature) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_details = $conn->prepare($sql_details);
        $stmt_details->bind_param("sssssssssss", $application_id, $owner_name, $owner_address, $land_location, $construction_location, $purpose, $building_details, $attached_documents, $date_of_application, $applicant_signature, $witness_signature);

        if (!$stmt_details->execute()) {
            throw new Exception("Failed to insert application details: " . $stmt_details->error);
        }

        // Commit the transaction for the database inserts
        $conn->commit();

        // Handle file uploads (outside transaction since file system operations can't be rolled back)
        $upload_dir = realpath(__DIR__ . "/../uploads") . "/";
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0755, true)) {
                throw new Exception("Failed to create upload directory: $upload_dir");
            }
        }

        // Check if the directory is writable
        if (!is_writable($upload_dir)) {
            throw new Exception("Upload directory is not writable: $upload_dir. Check permissions.");
        }

        $documents = [
            "architectural_drawings",
            "structure_reports",
            "fire_permit",
            "epa_report",
            "geo_technical_report",
            "traffic_impact_assessment"
        ];

        $max_file_size = 100 * 1024 * 1024; // 100MB in bytes
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];

        foreach ($documents as $doc) {
            if (isset($_FILES[$doc]) && $_FILES[$doc]['error'] === UPLOAD_ERR_OK) {
                $file_tmp = $_FILES[$doc]['tmp_name'];
                $file_name = basename($_FILES[$doc]['name']);
                $file_size = $_FILES[$doc]['size'];
                $file_type = mime_content_type($file_tmp);

                // Validate file size
                if ($file_size > $max_file_size) {
                    throw new Exception("File $doc exceeds the maximum size of 100MB.");
                }

                // Validate file type
                if (!in_array($file_type, $allowed_types)) {
                    throw new Exception("File $doc has an unsupported type. Allowed types: PDF, DOC, DOCX, JPG, PNG.");
                }

                // Sanitize file name to avoid issues with special characters
                $safe_file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "_", $file_name);
                // Replace slashes in application_id to avoid directory issues
                $safe_application_id = str_replace("/", "-", $application_id);
                $file_path = $upload_dir . $safe_application_id . "_" . $doc . "_" . time() . "_" . $safe_file_name;

                // Log file upload details for debugging
                error_log("Attempting to upload file $doc to $file_path");

                // Move the uploaded file
                if (!move_uploaded_file($file_tmp, $file_path)) {
                    throw new Exception("Failed to upload file $doc to $file_path. Check directory permissions and path.");
                }

                // Ensure the file was actually moved
                if (!file_exists($file_path)) {
                    throw new Exception("File $doc was not found at $file_path after upload.");
                }

                // Insert file record into the database
                $sql_file = "INSERT INTO uploaded_files (application_id, file_type, file_path) VALUES (?, ?, ?)";
                $stmt_file = $conn->prepare($sql_file);
                if (!$stmt_file) {
                    throw new Exception("Failed to prepare statement for file $doc: " . $conn->error);
                }
                $stmt_file->bind_param("sss", $application_id, $doc, $file_path);

                if (!$stmt_file->execute()) {
                    throw new Exception("Failed to insert file record for $doc: " . $stmt_file->error);
                }
                $stmt_file->close();
            } elseif (isset($_FILES[$doc]) && $_FILES[$doc]['error'] !== UPLOAD_ERR_NO_FILE) {
                // Handle file upload errors
                throw new Exception("Error uploading file $doc: " . $_FILES[$doc]['error']);
            }
        }

        // Redirect to success.php with application_id
        header("Location: success.php?application_id=" . urlencode($application_id));
        exit();
    } catch (Exception $e) {
        // Roll back the transaction on error
        $conn->rollback();
        header("Location: application.php?error=" . urlencode($e->getMessage()));
        exit();
    }

    $stmt_permit->close();
    if (isset($stmt_material)) $stmt_material->close();
    if (isset($stmt_details)) $stmt_details->close();
    $conn->close();
} else {
    header("Location: application.php?error=Invalid request");
    exit();
}
?>