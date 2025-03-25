<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get the user's role safely
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
$dashboard_link = ($user_role === 'admin') ? '../admin/admin_dashboard.php' : 'user_dashboard.php';

// Initialize variables to hold form data
$application_data = [];
$material_data = [];
$details_data = [];
$uploaded_files = [];
$search_message = '';

// Handle search functionality
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    $search_param = "%$search%"; // For LIKE clause

    // Fetch data from permit_applications with broader search
    $sql_permit = "SELECT * FROM permit_applications 
                   WHERE application_id = ? 
                   OR applicant_name LIKE ? 
                   OR applicant_mobile LIKE ?";
    $stmt_permit = $conn->prepare($sql_permit);
    $stmt_permit->bind_param("sss", $search, $search_param, $search_param); // All are strings
    $stmt_permit->execute();
    $result_permit = $stmt_permit->get_result();
    $application_data = $result_permit->fetch_assoc();
    $stmt_permit->close();

    if (!$application_data) {
        $search_message = "No application found for search term: " . htmlspecialchars($search);
    } else {
        $application_id = $application_data['application_id']; // Use the found application_id for subsequent queries

        // Fetch data from material_descriptions
        $sql_material = "SELECT * FROM material_descriptions WHERE application_id = ?";
        $stmt_material = $conn->prepare($sql_material);
        $stmt_material->bind_param("s", $application_id);
        $stmt_material->execute();
        $result_material = $stmt_material->get_result();
        $material_data = $result_material->fetch_assoc();
        $stmt_material->close();

        // Fetch data from application_details
        $sql_details = "SELECT * FROM application_details WHERE application_id = ?";
        $stmt_details = $conn->prepare($sql_details);
        $stmt_details->bind_param("s", $application_id);
        $stmt_details->execute();
        $result_details = $stmt_details->get_result();
        $details_data = $result_details->fetch_assoc();
        $stmt_details->close();

        // Fetch uploaded files
        $sql_files = "SELECT file_type, file_path FROM uploaded_files WHERE application_id = ?";
        $stmt_files = $conn->prepare($sql_files);
        $stmt_files->bind_param("s", $application_id);
        $stmt_files->execute();
        $result_files = $stmt_files->get_result();
        $uploaded_files = $result_files->fetch_all(MYSQLI_ASSOC);
        $stmt_files->close();

        $search_message = "Showing data for Application ID: " . htmlspecialchars($application_id);
    }
}

// Check for error messages from form submission
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permit and Material Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Your existing styles remain unchanged */
        #materialForm { font-family: Arial, sans-serif; background-color: #f9f9f9; text-align: center; }
        #materialForm .container { width: 90%; max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); }
        #materialForm h2, #materialForm h3 { margin-bottom: 10px; }
        #materialForm p { font-size: 14px; color: #555; }
        #materialForm .table-container { width: 100%; overflow-x: auto; }
        #materialForm table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        #materialForm th, #materialForm td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        #materialForm th { background-color: #007BFF; color: white; }
        #materialForm input[type="text"] { width: 100%; padding: 8px; margin: 5px 0; border: 1px solid #ccc; border-radius: 4px; display: block; }
        @media screen and (max-width: 768px) {
            #materialForm table { display: block; overflow-x: auto; }
            #materialForm th, #materialForm td { display: block; width: 100%; text-align: left; }
            #materialForm tr { display: flex; flex-direction: column; border-bottom: 1px solid #ddd; margin-bottom: 10px; }
            #materialForm th { text-align: center; }
            #materialForm input[type="text"] { width: 100%; }
        }
        #materialForm .button-container { display: flex; justify-content: space-between; margin-top: 20px; }
        #materialForm .submit-btn { background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 15px 30px; font-size: 16px; }
        #materialForm .submit-btn:hover { background: #218838; }
        #materialForm .cancel-btn { background: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 10px 20px; }
        #materialForm .cancel-btn:hover { background: #0056b3; }
        #materialForm .next-btn { background: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 15px 30px; font-size: 16px; }
        #materialForm .next-btn:hover { background: #0056b3; }
        .form-section { display: none; }
        .form-section.active { display: block; }
        footer { background-color: black; color: white; text-align: center; padding: 10px 0; margin-top: 20px; }
        #permitForm .next-btn-container { text-align: right; margin-top: 20px; }
        #permitForm .next-btn { background: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 15px 30px; font-size: 16px; }
        #permitForm .next-btn:hover { background: #0056b3; }
        #detailsForm { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        #detailsForm .container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); max-width: 800px; margin: auto; }
        #detailsForm .form-header { background: black; color: white; padding: 10px; border-radius: 5px; text-align: center; }
        #detailsForm h2, #detailsForm h4, #detailsForm h5 { font-weight: bold; margin-top: 15px; }
        #detailsForm label { font-weight: bold; }
        #detailsForm .form-control, #detailsForm .form-select { border-radius: 5px; margin-bottom: 10px; }
        #detailsForm .btn { margin-top: 20px; }
        #detailsForm .button-container { display: flex; justify-content: space-between; margin-top: 20px; }
        #detailsForm .submit-btn { background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 15px 30px; font-size: 16px; }
        #detailsForm .submit-btn:hover { background: #218838; }
        #detailsForm .cancel-btn { background: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer; padding: 10px 20px; }
        #detailsForm .cancel-btn:hover { background: #0056b3; }
        .error-message { color: #dc3545; font-size: 0.9rem; margin-top: 5px; display: none; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Build_Right</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo $dashboard_link; ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Search Field -->
    <div class="container mt-4 d-flex justify-content-end">
        <form method="GET" action="application.php" class="d-flex">
            <input type="text" name="search" class="form-control me-2" 
                   placeholder="Search by ID, Name, or Mobile" 
                   value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                   style="width: 250px; height: 32px; font-size: 14px;"
                   id="searchBox">
            <button type="submit" class="btn btn-primary" style="height: 32px;">Search</button>
        </form>
    </div>

    <!-- Display Messages -->
    <?php if (!empty($error_message)): ?>
        <div class="container mt-3">
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!empty($search_message)): ?>
        <div class="container mt-3">
            <div class="alert <?php echo strpos($search_message, 'No application found') === false ? 'alert-info' : 'alert-warning'; ?>" role="alert">
                <?php echo $search_message; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Single Form Wrapping All Sections -->
    <form id="applicationForm" action="submit_application.php" method="POST" enctype="multipart/form-data">
        <!-- First Form: Permit Application -->
        <div class="container mt-4 form-section active" id="permitForm">
            <h2 class="text-center">Building Permit Application</h2>

            <!-- Applicant Details -->
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">Applicant Details</div>
                <div class="card-body row">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="applicant_name" class="form-control" value="<?php echo htmlspecialchars($application_data['applicant_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="applicant_email" id="applicantEmail" class="form-control" 
                               value="<?php echo htmlspecialchars($application_data['applicant_email'] ?? ''); ?>" required>
                        <div id="emailError" class="error-message">Please enter a valid email address (e.g., example@domain.com).</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" name="applicant_mobile" class="form-control" value="<?php echo htmlspecialchars($application_data['applicant_mobile'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nationality</label>
                        <input type="text" name="applicant_nationality" class="form-control" value="<?php echo htmlspecialchars($application_data['applicant_nationality'] ?? ''); ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Gender</label>
                        <select name="applicant_gender" class="form-control">
                            <option value="Male" <?php echo ($application_data['applicant_gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($application_data['applicant_gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-6 mt-2">
                        <label class="form-label">Permit Type</label>
                        <select name="permit_type" class="form-control">
                            <option value="Residential" <?php echo ($application_data['permit_type'] ?? '') == 'Residential' ? 'selected' : ''; ?>>Residential</option>
                            <option value="Commercial" <?php echo ($application_data['permit_type'] ?? '') == 'Commercial' ? 'selected' : ''; ?>>Commercial</option>
                        </select>
                    </div>
                    <div class="col-6 mt-2">
                        <label class="form-label">Address</label>
                        <input type="text" name="applicant_address" class="form-control" value="<?php echo htmlspecialchars($application_data['applicant_address'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Agent Details -->
            <div class="card mt-3">
                <div class="card-header bg-secondary text-white">Agent Details</div>
                <div class="card-body row">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="agent_name" class="form-control" value="<?php echo htmlspecialchars($application_data['agent_name'] ?? ''); ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Gender</label>
                        <select name="agent_gender" class="form-control">
                            <option value="Male" <?php echo ($application_data['agent_gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($application_data['agent_gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="agent_address" class="form-control" value="<?php echo htmlspecialchars($application_data['agent_address'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Document Upload -->
            <div class="card mt-3">
                <div class="card-header bg-dark text-white">Upload Documents (Max 100MB each)</div>
                <div class="card-body">
                    <?php
                    $documents = [
                        "architectural_drawings" => "Architectural Drawings",
                        "structure_reports" => "Structure Reports",
                        "fire_permit" => "Fire Permit",
                        "epa_report" => "EPA Report",
                        "geo_technical_report" => "Geo-Technical Report",
                        "traffic_impact_assessment" => "Traffic Impact Assessment"
                    ];
                    foreach ($documents as $name => $label) {
                        $file_path = '';
                        foreach ($uploaded_files as $file) {
                            if ($file['file_type'] == $name) {
                                $file_path = $file['file_path'];
                                break;
                            }
                        }
                        echo '<div class="mb-2">
                                <label class="form-label">' . $label . '</label>';
                        if ($file_path) {
                            echo '<p>Uploaded: <a href="' . htmlspecialchars($file_path) . '" download>' . htmlspecialchars(basename($file_path)) . '</a></p>';
                        }
                        echo '<input type="file" name="' . $name . '" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            </div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Next Button -->
            <div class="next-btn-container">
                <button type="button" class="next-btn" onclick="validateEmailAndProceed('materialForm')">Next</button>
            </div>
        </div>

        <!-- Second Form: Material Description -->
        <div class="form-section" id="materialForm">
            <div class="container">
                <h2>Description of Materials, etc., to be used in the Work</h2>
                <p>All Plans must be fully dimensioned</p>
                <h3>DESCRIPTION</h3>

                <div class="table-container">
                    <table>
                        <tr>
                            <th>Category</th>
                            <th>Details</th>
                        </tr>
                        <tr>
                            <td>FOUNDATIONS</td>
                            <td>
                                <input type="text" name="foundations_materials" placeholder="Materials" value="<?php echo htmlspecialchars($material_data['foundations_materials'] ?? ''); ?>">
                                <input type="text" name="foundations_proportions" placeholder="Proportions" value="<?php echo htmlspecialchars($material_data['foundations_proportions'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>WALLS</td>
                            <td>
                                <input type="text" name="walls_materials" placeholder="Materials" value="<?php echo htmlspecialchars($material_data['walls_materials'] ?? ''); ?>">
                                <input type="text" name="walls_proportions" placeholder="Proportions" value="<?php echo htmlspecialchars($material_data['walls_proportions'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>FLOORS</td>
                            <td>
                                <input type="text" name="floors_materials" placeholder="Materials" value="<?php echo htmlspecialchars($material_data['floors_materials'] ?? ''); ?>">
                                <input type="text" name="floors_proportions" placeholder="Proportions" value="<?php echo htmlspecialchars($material_data['floors_proportions'] ?? ''); ?>">
                                <input type="text" name="floors_joint_dimension" placeholder="Joint Dimension" value="<?php echo htmlspecialchars($material_data['floors_joint_dimension'] ?? ''); ?>">
                                <input type="text" name="floors_covering_thickness" placeholder="Covering - Thickness" value="<?php echo htmlspecialchars($material_data['floors_covering_thickness'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>Windows</td>
                            <td>
                                <input type="text" name="windows_types" placeholder="Types" value="<?php echo htmlspecialchars($material_data['windows_types'] ?? ''); ?>">
                                <input type="text" name="windows_dimension" placeholder="Dimension" value="<?php echo htmlspecialchars($material_data['windows_dimension'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>DOORS</td>
                            <td>
                                <input type="text" name="doors_types" placeholder="Types" value="<?php echo htmlspecialchars($material_data['doors_types'] ?? ''); ?>">
                                <input type="text" name="doors_dimension" placeholder="Dimension" value="<?php echo htmlspecialchars($material_data['doors_dimension'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>ROOF</td>
                            <td>
                                <input type="text" name="roof_types" placeholder="Types" value="<?php echo htmlspecialchars($material_data['roof_types'] ?? ''); ?>">
                                <input type="text" name="roof_covering" placeholder="Covering" value="<?php echo htmlspecialchars($material_data['roof_covering'] ?? ''); ?>">
                                <input type="text" name="roof_spacing_trusses" placeholder="Spacing Trusses" value="<?php echo htmlspecialchars($material_data['roof_spacing_trusses'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>STEPS AND STAIRS</td>
                            <td>
                                <input type="text" name="steps_materials" placeholder="Materials" value="<?php echo htmlspecialchars($material_data['steps_materials'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>VERANDAH</td>
                            <td>
                                <input type="text" name="verandah_materials" placeholder="Materials" value="<?php echo htmlspecialchars($material_data['verandah_materials'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>FENCING</td>
                            <td>
                                <input type="text" name="fencing_materials" placeholder="Materials" value="<?php echo htmlspecialchars($material_data['fencing_materials'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>YARDS</td>
                            <td>
                                <input type="text" name="yards_details" placeholder="Details" value="<?php echo htmlspecialchars($material_data['yards_details'] ?? ''); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>OUT-BUILDING</td>
                            <td>
                                <input type="text" name="outbuilding_details" placeholder="Details" value="<?php echo htmlspecialchars($material_data['outbuilding_details'] ?? ''); ?>">
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Next and Previous Buttons -->
                <div class="button-container">
                    <button type="button" class="cancel-btn" onclick="showNextForm('permitForm')">Previous</button>
                    <button type="button" class="next-btn" onclick="showNextForm('detailsForm')">Next</button>
                </div>
            </div>
        </div>

        <!-- Third Form: Details of Application -->
        <div class="form-section" id="detailsForm">
            <div class="container">
                <div class="form-header">
                    <h2>DETAILS OF APPLICATION</h2>
                </div>

                <!-- Applicant Details -->
                <div class="mb-3">
                    <label class="form-label">I/We</label>
                    <input type="text" name="owner_name" class="form-control" placeholder="Enter name(s)" value="<?php echo htmlspecialchars($details_data['owner_name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Of</label>
                    <input type="text" name="owner_address" class="form-control" placeholder="Address" value="<?php echo htmlspecialchars($details_data['owner_address'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Being the Owner of the Land Situated at</label>
                    <input type="text" name="land_location" class="form-control" placeholder="Provide location of land" value="<?php echo htmlspecialchars($details_data['land_location'] ?? ''); ?>">
                </div>

                <h4 class="text-center">APPLY TO THE DISTRICT SPATIAL PLANNING COMMITTEE FOR</h4>

                <!-- Construction Purpose -->
                <div class="mb-3">
                    <label class="form-label">Construct a Building at</label>
                    <input type="text" name="construction_location" class="form-control" placeholder="Enter location" value="<?php echo htmlspecialchars($details_data['construction_location'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">For the Purpose of</label>
                    <select name="purpose" class="form-select">
                        <option>Select Purpose</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Demolition' ? 'selected' : ''; ?>>Demolition</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Extension' ? 'selected' : ''; ?>>Extension</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Alteration' ? 'selected' : ''; ?>>Alteration</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Regularization' ? 'selected' : ''; ?>>Regularization</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Regularize existing structure' ? 'selected' : ''; ?>>Regularize existing structure</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Install property' ? 'selected' : ''; ?>>Install property</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Execute site and engineering works' ? 'selected' : ''; ?>>Execute site and engineering works</option>
                        <option <?php echo ($details_data['purpose'] ?? '') == 'Relocate' ? 'selected' : ''; ?>>Relocate</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">The building at</label>
                    <input type="text" name="building_details" class="form-control" placeholder="Enter building details" value="<?php echo htmlspecialchars($details_data['building_details'] ?? ''); ?>">
                </div>

                <!-- Attached Documents Section -->
                <h5 class="mt-4">Attached Documents:</h5>
                <?php
                $attached_documents = explode(',', $details_data['attached_documents'] ?? '');
                ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="site_plan" id="sitePlan" <?php echo in_array('site_plan', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="sitePlan">Site Plan (1:1250 or 1:2500)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="block_plan" id="blockPlan" <?php echo in_array('block_plan', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="blockPlan">Block Plan (1:500 or 1:200)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="architectural_drawings" id="architecturalDrawings" <?php echo in_array('architectural_drawings', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="architecturalDrawings">Architectural Drawings (1:100 or 1:40)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="structural_drawings" id="structuralDrawings" <?php echo in_array('structural_drawings', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="structuralDrawings">Structural Drawings (1:200 or 1:100)</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="land_title" id="landTitle" <?php echo in_array('land_title', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="landTitle">Land Title Certificate</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="permits" id="permits" <?php echo in_array('permits', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="permits">Relevant Permits and Licenses</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="stakeholder" id="stakeholder" <?php echo in_array('stakeholder', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="stakeholder">Stakeholder Consultations</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="authorization" id="authorization" <?php echo in_array('authorization', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="authorization">Right or Authorization to Use the Land</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="documents[]" value="other_reports" id="otherReports" <?php echo in_array('other_reports', $attached_documents) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="otherReports">Other Relevant Reports</label>
                </div>

                <!-- Date and Signatures -->
                <div class="mb-3 mt-4">
                    <label class="form-label">Date of Application</label>
                    <input type="date" name="date_of_application" class="form-control" value="<?php echo htmlspecialchars($details_data['date_of_application'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Signature of Applicant</label>
                    <input type="text" name="applicant_signature" class="form-control" placeholder="Enter signature" value="<?php echo htmlspecialchars($details_data['applicant_signature'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Witness (if applicant is illiterate)</label>
                    <input type="text" name="witness_signature" class="form-control" placeholder="Enter witness signature" value="<?php echo htmlspecialchars($details_data['witness_signature'] ?? ''); ?>">
                </div>

                <!-- Submit and Previous Buttons -->
                <div class="button-container">
                    <button type="button" class="cancel-btn" onclick="showNextForm('materialForm')">Previous</button>
                    <button type="submit" class="submit-btn" onclick="return validateEmailBeforeSubmit()">Submit</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        © <?php echo date("Y"); ?> Development Permit System. Designed by KabTech Consulting. All Rights Reserved.
    </footer>

    <script>
        // Refresh page when search box is cleared
        document.getElementById("searchBox").addEventListener("input", function() {
            if (this.value.trim() === "") {
                window.location.href = "application.php";
            }
        });

        // Show next form section
        function showNextForm(formId) {
            document.querySelectorAll('.form-section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(formId).classList.add('active');
        }

        // Email validation function
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Validate email before proceeding to the next section
        function validateEmailAndProceed(formId) {
            const emailInput = document.getElementById('applicantEmail');
            const emailError = document.getElementById('emailError');
            const email = emailInput.value.trim();

            if (!validateEmail(email)) {
                emailError.style.display = 'block';
                emailInput.focus();
                return false;
            }

            emailError.style.display = 'none';
            showNextForm(formId);
        }

        // Validate email before form submission
        function validateEmailBeforeSubmit() {
            const emailInput = document.getElementById('applicantEmail');
            const emailError = document.getElementById('emailError');
            const email = emailInput.value.trim();

            if (!validateEmail(email)) {
                emailError.style.display = 'block';
                emailInput.focus();
                return false;
            }

            emailError.style.display = 'none';
            return true;
        }
    </script>
</body>
</html>