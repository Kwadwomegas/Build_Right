<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Permit and Material Application</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Styles for the second form only */
    #materialForm {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      text-align: center;
    }

    #materialForm .container {
      width: 90%;
      max-width: 900px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    }

    #materialForm h2, #materialForm h3 {
      margin-bottom: 10px;
    }

    #materialForm p {
      font-size: 14px;
      color: #555;
    }

    #materialForm .table-container {
      width: 100%;
      overflow-x: auto;
    }

    #materialForm table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    #materialForm th, #materialForm td {
      border: 1px solid #ddd;
      padding: 10px;
      text-align: left;
    }

    #materialForm th {
      background-color: #007BFF;
      color: white;
    }

    #materialForm input[type="text"] {
      width: 100%;
      padding: 8px;
      margin: 5px 0;
      border: 1px solid #ccc;
      border-radius: 4px;
      display: block;
    }

    /* Responsive Design for the second form */
    @media screen and (max-width: 768px) {
      #materialForm table {
        display: block;
        overflow-x: auto;
      }
      
      #materialForm th, #materialForm td {
        display: block;
        width: 100%;
        text-align: left;
      }
      
      #materialForm tr {
        display: flex;
        flex-direction: column;
        border-bottom: 1px solid #ddd;
        margin-bottom: 10px;
      }
      
      #materialForm th {
        text-align: center;
      }
      
      #materialForm input[type="text"] {
        width: 100%;
      }
    }

    #materialForm .button-container {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }

    #materialForm .submit-btn {
      background: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      padding: 15px 30px;
      font-size: 16px;
    }

    #materialForm .submit-btn:hover {
      background: #218838;
    }

    #materialForm .cancel-btn {
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      padding: 10px 20px;
    }

    #materialForm .cancel-btn:hover {
      background: #0056b3;
    }

    /* Style the Next button on the second form */
    #materialForm .next-btn {
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      padding: 15px 30px;
      font-size: 16px;
    }

    #materialForm .next-btn:hover {
      background: #0056b3;
    }

    /* Form Section Styles */
    .form-section {
      display: none; /* Hide the second form initially */
    }

    .form-section.active {
      display: block; /* Show the active form */
    }

    /* Footer Styles */
    footer {
      background-color: black;
      color: white;
      text-align: center;
      padding: 10px 0;
      margin-top: 20px;
    }

    /* Next button on the first form */
    #permitForm .next-btn-container {
      text-align: right;
      margin-top: 20px;
    }

    #permitForm .next-btn {
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      padding: 15px 30px;
      font-size: 16px;
    }

    #permitForm .next-btn:hover {
      background: #0056b3;
    }

    /* Styles for the third form only */
    #detailsForm {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
    }

    #detailsForm .container {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
      max-width: 800px;
      margin: auto;
    }

    #detailsForm .form-header {
      background: black;
      color: white;
      padding: 10px;
      border-radius: 5px;
      text-align: center;
    }

    #detailsForm h2, #detailsForm h4, #detailsForm h5 {
      font-weight: bold;
      margin-top: 15px;
    }

    #detailsForm label {
      font-weight: bold;
    }

    #detailsForm .form-control, #detailsForm .form-select {
      border-radius: 5px;
      margin-bottom: 10px;
    }

    #detailsForm .btn {
      margin-top: 20px;
    }

    #detailsForm .button-container {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
    }

    #detailsForm .submit-btn {
      background: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      padding: 15px 30px;
      font-size: 16px;
    }

    #detailsForm .submit-btn:hover {
      background: #218838;
    }

    #detailsForm .cancel-btn {
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      padding: 10px 20px;
    }

    #detailsForm .cancel-btn:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="../index.php">Build_Right</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- First Form: Permit Application -->
  <div class="container mt-4 form-section active" id="permitForm">
    <h2 class="text-center">Building Permit Application</h2>

    <?php if (isset($success_message)): ?>
      <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
      <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="submit_permit.php" method="POST" enctype="multipart/form-data">
      <!-- Applicant Details -->
      <div class="card mt-3">
        <div class="card-header bg-primary text-white">Applicant Details</div>
        <div class="card-body row">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="applicant_name" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="applicant_email" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Mobile Number</label>
            <input type="text" name="applicant_mobile" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Nationality</label>
            <input type="text" name="applicant_nationality" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Gender</label>
            <select name="applicant_gender" class="form-control">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="col-md-6 mt-2">
            <label class="form-label">Permit Type</label>
            <select name="permit_type" class="form-control">
              <option value="Residential">Residential</option>
              <option value="Commercial">Commercial</option>
            </select>
          </div>
          <div class="col-6 mt-2">
            <label class="form-label">Address</label>
            <input type="text" name="applicant_address" class="form-control">
          </div>
        </div>
      </div>

      <!-- Agent Details -->
      <div class="card mt-3">
        <div class="card-header bg-secondary text-white">Agent Details</div>
        <div class="card-body row">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="agent_name" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Gender</label>
            <select name="agent_gender" class="form-control">
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Address</label>
            <input type="text" name="agent_address" class="form-control">
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
            echo '<div class="mb-2">
                    <label class="form-label">' . $label . '</label>
                    <input type="file" name="' . $name . '" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" max-file-size="104857600">
                  </div>';
          }
          ?>
        </div>
      </div>

      <!-- Next Button -->
      <div class="next-btn-container">
        <button type="button" class="next-btn" onclick="validateAndShowNextForm('materialForm')">Next</button>
      </div>
    </form>
  </div>

  <!-- Second Form: Material Description -->
  <div class="form-section" id="materialForm">
    <div class="container">
      <h2>Description of Materials, etc., to be used in the Work</h2>
      <p>All Plans must be fully dimensioned</p>
      <h3>DESCRIPTION</h3>

      <form action="submit_materials.php" method="POST">
        <div class="table-container">
          <table>
            <tr>
              <th>Category</th>
              <th>Details</th>
            </tr>
            <tr>
              <td>FOUNDATIONS</td>
              <td>
                <input type="text" name="foundations_materials" placeholder="Materials">
                <input type="text" name="foundations_proportions" placeholder="Proportions">
              </td>
            </tr>
            <tr>
              <td>WALLS</td>
              <td>
                <input type="text" name="walls_materials" placeholder="Materials">
                <input type="text" name="walls_proportions" placeholder="Proportions">
              </td>
            </tr>
            <tr>
              <td>FLOORS</td>
              <td>
                <input type="text" name="floors_materials" placeholder="Materials">
                <input type="text" name="floors_proportions" placeholder="Proportions">
                <input type="text" name="floors_joint_dimension" placeholder="Joint Dimension">
                <input type="text" name="floors_covering_thickness" placeholder="Covering - Thickness">
              </td>
            </tr>
            <tr>
              <td>Windows</td>
              <td>
                <input type="text" name="windows_types" placeholder="Types">
                <input type="text" name="windows_dimension" placeholder="Dimension">
              </td>
            </tr>
            <tr>
              <td>DOORS</td>
              <td>
                <input type="text" name="doors_types" placeholder="Types">
                <input type="text" name="doors_dimension" placeholder="Dimension">
              </td>
            </tr>
            <tr>
              <td>ROOF</td>
              <td>
                <input type="text" name="roof_types" placeholder="Types">
                <input type="text" name="roof_covering" placeholder="Covering">
                <input type="text" name="roof_spacing_trusses" placeholder="Spacing Trusses">
              </td>
            </tr>
            <tr>
              <td>STEPS AND STAIRS</td>
              <td>
                <input type="text" name="steps_materials" placeholder="Materials">
              </td>
            </tr>
            <tr>
              <td>VERANDAH</td>
              <td>
                <input type="text" name="verandah_materials" placeholder="Materials">
              </td>
            </tr>
            <tr>
              <td>FENCING</td>
              <td>
                <input type="text" name="fencing_materials" placeholder="Materials">
              </td>
            </tr>
            <tr>
              <td>YARDS</td>
              <td>
                <input type="text" name="yards_details" placeholder="Details">
              </td>
            </tr>
            <tr>
              <td>OUT-BUILDING</td>
              <td>
                <input type="text" name="outbuilding_details" placeholder="Details">
              </td>
            </tr>
          </table>
        </div>

        <!-- Next and Previous Buttons -->
        <div class="button-container">
          <button type="button" class="cancel-btn" onclick="showNextForm('permitForm')">Previous</button>
          <button type="button" class="next-btn" onclick="validateAndShowNextForm('detailsForm')">Next</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Third Form: Details of Application -->
  <div class="form-section" id="detailsForm">
    <div class="container">
      <div class="form-header">
        <h2>DETAILS OF APPLICATION</h2>
      </div>
      <form>
        <!-- Applicant Details -->
        <div class="mb-3">
          <label class="form-label">I/We</label>
          <input type="text" class="form-control" placeholder="Enter name(s)">
        </div>

        <div class="mb-3">
          <label class="form-label">Of</label>
          <input type="text" class="form-control" placeholder="Address">
        </div>

        <div class="mb-3">
          <label class="form-label">Being the Owner of the Land Situated at</label>
          <input type="text" class="form-control" placeholder="Provide location of land">
        </div>

        <h4 class="text-center">APPLY TO THE DISTRICT SPATIAL PLANNING COMMITTEE FOR</h4>

        <!-- Construction Purpose -->
        <div class="mb-3">
          <label class="form-label">Construct a Building at</label>
          <input type="text" class="form-control" placeholder="Enter location">
        </div>

        <div class="mb-3">
          <label class="form-label">For the Purpose of</label>
          <select class="form-select">
            <option>Select Purpose</option>
            <option>Demolition</option>
            <option>Extension</option>
            <option>Alteration</option>
            <option>Regularization</option>
            <option>Regularize existing structure</option>
            <option>Install property</option>
            <option>Execute site and engineering works</option>
            <option>Relocate</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">The building at</label>
          <input type="text" class="form-control" placeholder="Enter building details">
        </div>

        <!-- Attached Documents Section -->
        <h5 class="mt-4">Attached Documents:</h5>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="sitePlan">
          <label class="form-check-label" for="sitePlan">
            Site Plan (1:1250 or 1:2500)
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="blockPlan">
          <label class="form-check-label" for="blockPlan">
            Block Plan (1:500 or 1:200)
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="architecturalDrawings">
          <label class="form-check-label" for="architecturalDrawings">
            Architectural Drawings (1:100 or 1:40)
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="structuralDrawings">
          <label class="form-check-label" for="structuralDrawings">
            Structural Drawings (1:200 or 1:100)
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="landTitle">
          <label class="form-check-label" for="landTitle">
            Land Title Certificate
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="permits">
          <label class="form-check-label" for="permits">
            Relevant Permits and Licenses
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="stakeholder">
          <label class="form-check-label" for="stakeholder">
            Stakeholder Consultations
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="authorization">
          <label class="form-check-label" for="authorization">
            Right or Authorization to Use the Land
          </label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" value="" id="otherReports">
          <label class="form-check-label" for="otherReports">
            Other Relevant Reports
          </label>
        </div>

        <!-- Date and Signatures -->
        <div class="mb-3 mt-4">
          <label class="form-label">Date of Application</label>
          <input type="date" class="form-control">
        </div>

        <div class="mb-3">
          <label class="form-label">Signature of Applicant</label>
          <input type="text" class="form-control" placeholder="Enter signature">
        </div>

        <div class="mb-3">
          <label class="form-label">Witness (if applicant is illiterate)</label>
          <input type="text" class="form-control" placeholder="Enter witness signature">
        </div>

        <!-- Submit and Previous Buttons -->
        <div class="button-container">
          <button type="button" class="cancel-btn" onclick="showNextForm('materialForm')">Previous</button>
          <button type="submit" class="submit-btn">Submit</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <p>&copy; <span id="currentYear"></span> Build_Right. All rights reserved.</p>
  </footer>

  <script>
    function showNextForm(formId) {
      // Hide all form sections
      document.querySelectorAll('.form-section').forEach(section => {
        section.classList.remove('active');
      });

      // Show the selected form section
      document.getElementById(formId).classList.add('active');
    }

    function validateAndShowNextForm(formId) {
      // No validation logic since required attributes are removed
      showNextForm(formId);
    }

    // Update the footer year dynamically
    document.getElementById('currentYear').textContent = new Date().getFullYear();
  </script>
</body>
</html>