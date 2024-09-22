$(document).ready(function () {
  // Load inventory data
  function loadInventory() {
    $.ajax({
      url: 'get_inventory.php',
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        let tableHtml = '';
        data.forEach(item => {
          tableHtml += `<tr>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>${item.expiry_date}</td>
                        <td>${item.price}</td>
                    </tr>`;
        });
        $('#inventoryTable').html(tableHtml);
      },
      error: function (xhr, status, error) {
        console.error("Error loading inventory:", error);
        alert("Error loading inventory. Please try again.");
      }
    });
  }

  // Load transactions data
  function loadTransactions(startDate = null, endDate = null) {
    let data = {};
    if (startDate && endDate) {
      data = { startDate: startDate, endDate: endDate };
    }
    $.ajax({
      url: 'get_transactions.php',
      type: 'GET',
      data: data,
      dataType: 'json',
      success: function (data) {
        let tableHtml = '';
        data.forEach(transaction => {
          tableHtml += `<tr>
                        <td>${transaction.date}</td>
                        <td>${transaction.type}</td>
                        <td>${transaction.amount}</td>
                        <td><button class="btn btn-sm btn-info view-details" data-id="${transaction.id}">View Details</button></td>
                    </tr>`;
        });
        $('#transactionsTable').html(tableHtml);
      },
      error: function (xhr, status, error) {
        console.error("Error loading transactions:", error);
        alert("Error loading transactions. Please try again.");
      }
    });
  }

  // Transaction filter form submission
  $('#transactionFilterForm').submit(function (e) {
    e.preventDefault();
    let startDate = $('#startDate').val();
    let endDate = $('#endDate').val();
    loadTransactions(startDate, endDate);
  });

  // View transaction details
  $(document).on('click', '.view-details', function () {
    let transactionId = $(this).data('id');
    $.ajax({
      url: 'get_transaction_details.php',
      type: 'GET',
      data: { id: transactionId },
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          let transaction = response.transaction;
          let detailsHtml = `
                      <h5>Transaction Details</h5>
                      <p><strong>Date:</strong> ${transaction.date}</p>
                      <p><strong>Type:</strong> ${transaction.type}</p>
                      <p><strong>Amount:</strong> ${transaction.amount}</p>
                      <p><strong>Customer/Supplier:</strong> ${transaction.person}</p>
                      <p><strong>Address:</strong> ${transaction.person_address}</p>
                      <h6>Items:</h6>
                      <ul>
                  `;
          transaction.items.forEach(item => {
            detailsHtml += `<li>${item.name} - Quantity: ${item.quantity}, Price: ${item.price}</li>`;
          });
          detailsHtml += '</ul>';
          $('#viewTransactionsModal .modal-body').html(detailsHtml);
        } else {
          alert("Error: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error loading transaction details:", error);
        alert("Error loading transaction details. Please try again.");
      }
    });
  });

  // Generate report form submission
  $('#generateReportForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
      url: 'generate_report.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          window.open(response.reportUrl, '_blank');
        } else {
          alert("Error generating report: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error generating report:", error);
        alert("Error generating report. Please try again.");
      }
    });
  });

  // Initial load
  loadInventory();
  loadTransactions();
});
