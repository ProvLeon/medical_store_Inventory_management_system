$(document).ready(function () {
  // Function to load inventory
  function loadInventory() {
    $.ajax({
      url: 'get_inventory.php',
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        var tableBody = $('#inventoryTable');
        tableBody.empty();
        $.each(data, function (i, item) {
          var row = $('<tr>');
          row.append($('<td>').text(item.id));
          row.append($('<td>').text(item.name));
          row.append($('<td>').text(item.quantity));
          row.append($('<td>').text(item.cp));
          row.append($('<td>').text(item.sp));
          row.append($('<td>').text(item.expiry_date));
          row.append($('<td>').text(item.chem_amount));
          tableBody.append(row);
        });
      },
      error: function (xhr, status, error) {
        console.error("Error fetching inventory:", error);
        alert("Error loading inventory. Please try again.");
      }
    });
  }

  // Load inventory when the modal is shown
  $('#inventoryModal').on('shown.bs.modal', function (e) {
    loadInventory();
  });

  // Load users data
  function loadUsers() {
    $.ajax({
      url: 'get_users.php',
      type: 'GET',
      dataType: 'json',
      success: function (data) {
        let tableHtml = '';
        data.forEach(user => {
          tableHtml += `<tr>
                        <td>${user.id}</td>
                        <td>${user.username}</td>
                        <td>${user.role}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-user" data-id="${user.id}">Delete</button>
                        </td>
                    </tr>`;
        });
        $('#usersTable').html(tableHtml);
      },
      error: function (xhr, status, error) {
        console.error("Error loading users:", error);
        alert("Error loading users. Please try again.");
      }
    });
  }

  // Add item form submission
  $('#addItemForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
      url: 'add_item.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          alert("Item added successfully!");
          $('#addItemForm')[0].reset();
          loadInventory();
        } else {
          alert("Error adding item: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error adding item:", error);
        alert("Error adding item. Please try again.");
      }
    });
  });

  // Update item form submission
  $('#updateItemForm').submit(function (e) {
    e.preventDefault();

    var formData = $(this).serializeArray();
    // Remove empty fields
    formData = formData.filter(function (item) {
      return item.value !== "";
    });

    $.ajax({
      url: 'update_item.php',
      type: 'POST',
      data: $.param(formData),
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          alert(response.message);
          $('#updateItemForm')[0].reset();
          loadInventory();
        } else {
          alert("Error updating item: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error updating item:", error);
        alert("Error updating item. Please try again.");
      }
    });
  });

  // Add user form submission
  $('#addUserForm').submit(function (e) {
    e.preventDefault();
    $.ajax({
      url: 'add_user.php',
      type: 'POST',
      data: $(this).serialize(),
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          alert("User added successfully!");
          $('#addUserForm')[0].reset();
          loadUsers();
        } else {
          alert("Error adding user: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error adding user:", error);
        alert("Error adding user. Please try again.");
      }
    });
  });

  // Delete user
  $(document).on('click', '.delete-user', function () {
    if (confirm("Are you sure you want to delete this user?")) {
      let userId = $(this).data('id');
      $.ajax({
        url: 'delete_user.php',
        type: 'POST',
        data: { id: userId },
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            alert("User deleted successfully!");
            loadUsers();
          } else {
            alert("Error deleting user: " + response.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("Error deleting user:", error);
          alert("Error deleting user. Please try again.");
        }
      });
    }
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
      error: function () {
        alert('An error occurred while generating the report.');
      }
    });
  });

  // Initial load
  loadInventory();
  loadUsers();
});
