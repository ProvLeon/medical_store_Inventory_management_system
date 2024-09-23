$(document).ready(function () {
  let medicineCount = 1;

  // Load medicine options
  function loadMedicineOptions() {
    $.ajax({
      url: 'get_medicines.php',
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        let optionsHtml = '<option value="">Select Medicine</option>';
        data.forEach(medicine => {
          optionsHtml += `<option value="${medicine.id}">${medicine.name}</option>`;
        });
        $('.medicine-select').html(optionsHtml);
      },
      error: function (xhr, status, error) {
        console.error("Error loading medicines:", error);
        alert("Error loading medicines. Please try again.");
      }
    });
  }

  // Add medicine field
  $('#addMedicine').click(function () {
    medicineCount++;
    let newField = `
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="medicineName${medicineCount}">Medicine Name</label>
                    <select class="form-control medicine-select" id="medicineName${medicineCount}" name="medicineName[]" required>
                        <!-- Options will be populated dynamically -->
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="quantity${medicineCount}">Quantity</label>
                    <input type="number" class="form-control" id="quantity${medicineCount}" name="quantity[]" min="1" required>
                </div>
                <div class="form-group col-md-2">
                    <label for="remove${medicineCount}" class="d-block">&nbsp;</label>
                    <button type="button" class="btn btn-danger remove-medicine" id="remove${medicineCount}">Remove</button>
                </div>
            </div>
        `;
    $('#medicineFields').append(newField);
    loadMedicineOptions();
  });

  // Remove medicine field
  $(document).on('click', '.remove-medicine', function () {
    $(this).closest('.form-row').remove();
  });

  // Sell medicine form submission
  $('#sellMedicineForm').submit(function (e) {
    e.preventDefault();
    var formData = $(this).serializeArray();
    $.ajax({
      url: 'sell_medicine.php',
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          alert("Sale completed successfully!\nTransaction ID: " + response.transactionId + "\nTotal Amount: $" + response.totalAmount);
          $('#sellMedicineForm')[0].reset();
          $('#medicineFields').html(`
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
                  `);
          medicineCount = 1;
          loadMedicineOptions();
        } else {
          alert("Error completing sale: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error completing sale:", error);
        alert("Error completing sale. Please try again.");
      }
    });
  });

  // Purchase medicine form submission
  $('#purchaseMedicineForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
      url: 'purchase_medicine.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          alert("Purchase completed successfully!\nTransaction ID: " + response.transactionId + "\nTotal Amount: $" + response.totalAmount);
          $('#purchaseMedicineForm')[0].reset();
          $('#purchaseMedicineModal').modal('hide');
        } else {
          alert("Error completing purchase: " + response.message);
          if (response.message.includes("Employee ID not found")) {
            window.location.href = 'logout.php';
          }
        }
      },
      error: function (xhr, status, error) {
        console.error("Error completing purchase:", error);
        alert("Error completing purchase. Please try again. Details: " + xhr.responseText);
      }
    });
  });

  // Load notifications
  function loadNotifications() {
    $.ajax({
      url: 'get_notifications.php',
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        let notificationsHtml = '';
        let count = 0;
        data.forEach(notification => {
          notificationsHtml += `<a class="dropdown-item" href="#">${notification.message}</a>`;
          count++;
        });
        $('#notificationsList').html(notificationsHtml);
        $('#notificationCount').text(count);
      },
      error: function (xhr, status, error) {
        console.error("Error loading notifications:", error);
      }
    });
  }

  // Initial load
  loadMedicineOptions();
  loadNotifications();

  // Refresh notifications every 5 minutes
  setInterval(loadNotifications, 300000);
});
