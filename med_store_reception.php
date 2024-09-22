<?php
session_start();
if(!isset($_SESSION['receptionist'])) {
    header("Location: index.html");
    exit();
}
require_once 'config.php';
require_once 'db_connection.php';
$dbconn = Connect();

require_once 'notifications.php';
$notificationsManager = new Notifications($dbconn);
$notifications = $notificationsManager->getNotifications();

// Fetch all medicines
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
    <title>Medical Store Management - Reception</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <link href="css/reception-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Med Store Reception</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Notifications <span class="badge badge-light" id="notificationCount"></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown" id="notificationsList">
                        <!-- Notifications will be dynamically added here -->
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-8">
                <h2>Available Medicines</h2>
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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($medicines as $medicine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                <td><?php echo $medicine['quantity']; ?></td>
                                <td><?php echo $medicine['sp']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info view-medicine" data-id="<?php echo $medicine['id']; ?>">View</button>
                                    <button class="btn btn-sm btn-primary sell-medicine" data-id="<?php echo $medicine['id']; ?>">Sell</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <button class="btn btn-primary btn-block mb-2" data-toggle="modal" data-target="#sellMedicineModal">Sell Multiple Medicines</button>
                        <button class="btn btn-secondary btn-block" data-toggle="modal" data-target="#purchaseMedicineModal">Purchase Medicine</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sell Medicine Modal -->
    <div class="modal fade" id="sellMedicineModal" tabindex="-1" role="dialog" aria-labelledby="sellMedicineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sellMedicineModalLabel">Sell Medicine</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="sellMedicineForm">
                        <div id="medicineFields">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="medicineId1">Medicine Name</label>
                                    <select class="form-control medicine-select" id="medicineId1" name="medicineId[]" required>
                                        <!-- Options will be populated dynamically -->
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="quantity1">Quantity</label>
                                    <input type="number" class="form-control" id="quantity1" name="quantity[]" min="1" required>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="remove1" class="d-block">&nbsp;</label>
                                    <button type="button" class="btn btn-danger remove-medicine" id="remove1">Remove</button>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" id="addMedicine">Add Another Medicine</button>
                        <div class="form-group mt-3">
                            <label for="customerName">Customer Name</label>
                            <input type="text" class="form-control" id="customerName" name="customerName" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Complete Sale</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Medicine Modal -->
    <div class="modal fade" id="purchaseMedicineModal" tabindex="-1" role="dialog" aria-labelledby="purchaseMedicineModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseMedicineModalLabel">Purchase Medicine</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="purchaseMedicineForm">
                        <div class="form-group">
                            <label for="medicineName">Medicine Name</label>
                            <input type="text" class="form-control" id="medicineName" name="medicineName" required>
                        </div>
                        <div class="form-group">
                            <label for="expiryDate">Expiry Date</label>
                            <input type="date" class="form-control" id="expiryDate" name="expiryDate" required>
                        </div>
                        <div class="form-group">
                            <label for="chemicalAmount">Chemical Amount</label>
                            <input type="text" class="form-control" id="chemicalAmount" name="chemicalAmount" required>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="costPrice">Cost Price</label>
                            <input type="number" step="0.01" class="form-control" id="costPrice" name="costPrice" required>
                        </div>
                        <div class="form-group">
                            <label for="sellingPrice">Selling Price</label>
                            <input type="number" step="0.01" class="form-control" id="sellingPrice" name="sellingPrice" required>
                        </div>
                        <div class="form-group">
                            <label for="supplierName">Supplier Name</label>
                            <input type="text" class="form-control" id="supplierName" name="supplierName" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Complete Purchase</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Medicine Modal -->
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

    <!-- Sell Single Medicine Modal -->
    <div class="modal fade" id="sellSingleMedicineModal" tabindex="-1" role="dialog" aria-labelledby="sellSingleMedicineModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sellSingleMedicineModalLabel">Sell Medicine</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="sellSingleMedicineForm">
                        <input type="hidden" id="singleMedicineId" name="medicineId[]">
                        <div class="form-group">
                            <label for="singleMedicineQuantity">Quantity</label>
                            <input type="number" class="form-control" id="singleMedicineQuantity" name="quantity[]" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="singleMedicineCustomerName">Customer Name</label>
                            <input type="text" class="form-control" id="singleMedicineCustomerName" name="customerName" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Complete Sale</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="js/reception-scripts.js"></script>
    <script>
    $(document).ready(function() {
        // Function to update notifications
        function updateNotifications() {
            $.ajax({
                url: 'get_notifications.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    var notificationsList = $('#notificationsList');
                    var notificationCount = $('#notificationCount');
                    notificationsList.empty();
                    notificationCount.text(data.length);

                    $.each(data, function(index, notification) {
                        notificationsList.append(
                            $('<a>').addClass('dropdown-item')
                                    .text(notification.message)
                        );
                    });
                }
            });
        }

        // Update notifications on page load and every 5 minutes
        updateNotifications();
        setInterval(updateNotifications, 300000);

        // Medicine search functionality
        $("#medicineSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#medicineTable tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // View medicine details
        $(document).on('click', '.view-medicine', function() {
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
                error: function() {
                    alert("Error fetching medicine details");
                }
            });
        });

        // Sell single medicine
        $(document).on('click', '.sell-medicine', function() {
            var medicineId = $(this).data("id");
            $("#singleMedicineId").val(medicineId);
            $("#sellSingleMedicineModal").modal('show');
        });

        // Handle single medicine sale
        $("#sellSingleMedicineForm").submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'sell_medicine.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert("Sale completed successfully!\nTransaction ID: " + response.transactionId + "\nTotal Amount: $" + response.totalAmount);
                        $("#sellSingleMedicineModal").modal('hide');
                        location.reload(); // Refresh the page to update medicine quantities
                    } else {
                        alert("Error completing sale: " + response.message);
                    }
                },
                error: function() {
                    alert("Error completing sale. Please try again.");
                }
            });
        });

    });
    </script>
</body>
</html>
