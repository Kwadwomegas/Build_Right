

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Description Form</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            text-align: center;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            margin-bottom: 10px;
        }

        p {
            font-size: 14px;
            color: #555;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            display: block;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
            
            th, td {
                display: block;
                width: 100%;
                text-align: left;
            }
            
            tr {
                display: flex;
                flex-direction: column;
                border-bottom: 1px solid #ddd;
                margin-bottom: 10px;
            }
            
            th {
                text-align: center;
            }
            
            input[type="text"] {
                width: 100%;
            }
        }

        .button-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .submit-btn {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            padding: 10px 20px;
        }

        .submit-btn:hover {
            background: #218838;
        }

        .cancel-btn {
            background: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100px;
            padding: 10px 20px;
            align-self: flex-start;
        }

        .cancel-btn:hover {
            background: #0056b3;
        }

        /* Footer Styles */
        footer {
            background-color: black;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Description of Materials, etc., to be used in the Work</h2>
        <p>All Plans must be fully dimensioned</p>
        <h3>DESCRIPTION</h3>

        <form>
            <div class="table-container">
                <table>
                    <tr>
                        <th>Category</th>
                        <th>Details</th>
                    </tr>
                    <tr>
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

                    </tr>
                </table>
            </div>
            <div class="button-container">
                <button type="submit" class="submit-btn">Submit</button>
                <button type="button" class="cancel-btn" onclick="window.location.href='applyall.php'">Previous</button>

            </div>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; <script>document.write(new Date().getFullYear());</script> Build_Right. All rights reserved.</p>
    </footer>
</body>
</html>