<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Development Permit</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
        }

        .form-header {
            background: black;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }

        h2, h5 {
            font-weight: bold;
            margin-top: 15px;
        }

        label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 5px;
        }

        .btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>
     <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="../index.php">Build_Right</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
         <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
         <ul class="navbar-nav ms-auto">
           <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Dashboard</a></li>
          
           <li class="nav-item">
             <a class="nav-link" href="#">Welcome, <strong><?php echo htmlspecialchars($admin_name); ?></strong></a>
           </li>   
           <li class="nav-item"><a class="nav-link" href="../application">Apply</a></li>      
           <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
           <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
         </ul>
      </div>
    </div>
  </nav>

<div class="container mt-4">
    <div class="form-header text-center">
        <h2>DEVELOPMENT PERMIT</h2>
    </div>

    <form action="process_permit.php" method="POST">
        <!-- Permit Certification -->
        <div class="mb-3">
            <label class="form-label">Permit Number</label>
            <input type="text" class="form-control" name="permit_number" placeholder="Enter permit number" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Permit certifies that</label>
            <input type="text" class="form-control" name="permit_holder" placeholder="Enter name of permit holder" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Having land at</label>
            <input type="text" class="form-control" name="land_location" placeholder="Enter land location" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Has approval from the North Tongu District Assembly to construct a</label>
            <input type="text" class="form-control" name="construction_type" placeholder="Enter type of construction" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Subject to the attached conditions and in accordance with the attached plan.</label>
        </div>

        <h5 class="text-center">DATED AT THE OFFICE OF THE NORTH TONGU DISTRICT ASSEMBLY</h5>

        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="issue_date" required>
        </div>

        <!-- Signatures Section -->
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Signature (Physical Planning Officer, NTDA)</label>
                <input type="text" class="form-control" name="ppo_signature" placeholder="Signature" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Signature (Works Engineer, NTDA)</label>
                <input type="text" class="form-control" name="we_signature" placeholder="Signature" required>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="ppo_date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date</label>
                <input type="date" class="form-control" name="we_date" required>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label">Name (Physical Planning Officer, NTDA)</label>
                <input type="text" class="form-control" name="ppo_name" placeholder="Enter name" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name (Works Engineer, NTDA)</label>
                <input type="text" class="form-control" name="we_name" placeholder="Enter name" required>
            </div>
        </div>

        <!-- Notes Section -->
        <h5 class="mt-4">NOTE:</h5>
        <ul>
            <li>The set-out and foundation trenches are done under the supervision of the District Physical Planning Officer and the Building Inspector.</li>
            <li>This development permit does not relieve the applicant from compliance with any building regulations.</li>
            <li>The work must be completed within <b>five (5) years</b> from the date of issue.</li>
            <li>This development permit does not confirm the right or title of the applicant to the land.</li>
        </ul>

        <button type="submit" class="btn btn-primary w-100">Submit Permit</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>