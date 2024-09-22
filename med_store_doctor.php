<?php
session_start();
if(!isset($_SESSION['doctor'])) {
    header("Location: index.html");
    exit();
}
require_once 'config.php';
require_once 'db_connection.php';
$dbconn = Connect();

// Fetch all medicines for the inventory view
$query = "SELECT * FROM " . DB_TABLE_MEDICINE . " ORDER BY name";
$result = mysqli_query($dbconn, $query);
$medicines = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);

mysqli_close($dbconn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Store Management - Doctor</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="css/doctor-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Med Store Doctor</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <h2>Doctor Dashboard</h2>
        <div class="row">
            <div class="col-md-8">
                <h3>Inventory Overview</h3>
                <div class="form-group">
                    <input type="text" class="form-control" id="medicineSearch" placeholder="Search medicines...">
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="medicineTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Selling Price</th>
                                <th>Expiry Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicines as $medicine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                <td><?php echo $medicine['quantity']; ?></td>
                                <td><?php echo $medicine['sp']; ?></td>
                                <td><?php echo $medicine['expiry_date']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-medicine" data-id="<?php echo $medicine['id']; ?>">View Details</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <button class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#viewTransactionsModal">View Transactions</button>
                        <button class="btn btn-secondary btn-block" data-toggle="modal" data-target="#generateReportsModal">Generate Reports</button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Statistics</h5>
                        <p>Total Medicines: <span id="totalMedicines"><?php echo count($medicines); ?></span></p>
                        <p>Low Stock Items: <span id="lowStockItems">0</span></p>
                        <p>Expiring Soon: <span id="expiringSoon">0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Transactions Modal -->
    <div class="modal fade" id="viewTransactionsModal" tabindex="-1" role="dialog" aria-labelledby="viewTransactionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewTransactionsModalLabel">View Transactions</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="transactionFilterForm" class="mb-3">
                        <div class="form-row">
                            <div class="col-md-4">
                                <input type="date" class="form-control" id="startDate" name="startDate">
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" id="endDate" name="endDate">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsTable">
                                <!-- This will be populated by AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Reports Modal -->
    <div class="modal fade" id="generateReportsModal" tabindex="-1" role="dialog" aria-labelledby="generateReportsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="generateReportsModalLabel">Generate Reports</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="generateReportForm">
                        <div class="form-group">
                            <label for="reportType">Report Type</label>
                            <select class="form-control" id="reportType" name="reportType" required>
                                <option value="sales">Sales Report</option>
                                <option value="inventory">Inventory Report</option>
                                <option value="profitLoss">Profit/Loss Report</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reportStartDate">Start Date</label>
                            <input type="date" class="form-control" id="reportStartDate" name="startDate" required>
                        </div>
                        <div class="form-group">
                            <label for="reportEndDate">End Date</label>
                            <input type="date" class="form-control" id="reportEndDate" name="endDate" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Medicine Details Modal -->
    <div class="modal fade" id="viewMedicineModal" tabindex="-1" role="dialog" aria-labelledby="viewMedicineModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMedicineModalLabel">Medicine Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewMedicineBody">
                    <!-- Medicine details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/doctor-scripts.js"></script>
    <script>
    $(document).ready(function() {
        // Medicine search functionality
        $("#medicineSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#medicineTable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // View medicine details
        $(".view-medicine").click(function() {
            var medicineId = $(this).data("id");
            $.ajax({
                url: 'get_medicine_details.php',
                type: 'GET',
                data: { id: medicineId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var medicine = response.medicine;
                        var detailsHtml = `
                            <p><strong>Name:</strong> ${medicine.name}</p>
                            <p><strong>Quantity:</strong> ${medicine.quantity}</p>
                            <p><strong>Cost Price:</strong> ${medicine.cp}</p>
                            <p><strong>Selling Price:</strong> ${medicine.sp}</p>
                            <p><strong>Expiry Date:</strong> ${medicine.expiry_date}</p>
                            <p><strong>Chemical Amount:</strong> ${medicine.chem_amount}</p>
                        `;
                        $("#viewMedicineBody").html(detailsHtml);
                        $("#viewMedicineModal").modal('show');
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching medicine details:", error);
                    alert("Error fetching medicine details. Please try again.");
                }
            });
        });

        // Load statistics
        function loadStatistics() {
            $.ajax({
                url: 'get_statistics.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    $("#lowStockItems").text(response.lowStockItems);
                    $("#expiringSoon").text(response.expiringSoon);
                },
                error: function() {
                    console.error("Error loading statistics");
                }
            });
        }

        loadStatistics();
    });
    </script>
</body>
</html>
