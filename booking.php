<?php
// booking.php
require_once 'auth_helpers.php';
// Check if user is logged in for booking page
if (!isLoggedIn()) {
    header('Location: login_required.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SalonSync - Book Appointment</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #fdf2f8;
      font-family: Arial, sans-serif;
    }
    h2 {
      color: #ec4899;
      text-align: center;
      margin: 20px 0;
    }
    #calendar {
      max-width: 1000px;
      margin: 20px auto;
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
    }
    .fc-toolbar-title {
      color: #ec4899;
      font-weight: bold;
    }
  </style>
</head>
<body>

  <!-- Navigation bar -->
  <nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold text-pink" href="index.php">SALONSYNC</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
          <li class="nav-item"><a class="nav-link active text-pink" href="booking.php">Booking</a></li>
          <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
          <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="terms.php">Terms</a></li>
          <?php if (isLoggedIn()): ?>
            <li class="nav-item">
              <form action="logout.php" method="post">
                <button type="submit" class="nav-link" style="background:none;border:none;cursor:pointer;">Logout</button>
              </form>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Page Title -->
  <h2>SalonSync - Book Appointment</h2>

  <!-- Calendar -->
  <div id="calendar"></div>

  <!-- FullCalendar + Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      selectable: true,
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek'
      },

      // Load booked slots from backend
      events: 'load_bookings.php',

      select: function(info) {
        const date = info.startStr.substring(0,10);

        // Let user choose Morning or Afternoon slot
        const slot = prompt("Choose a slot: 'morning' or 'afternoon'").toLowerCase();

        if (slot !== "morning" && slot !== "afternoon") {
          alert("Invalid choice. Please type 'morning' or 'afternoon'.");
          return;
        }

        // Check if slot already exists
        const events = calendar.getEvents();
        const exists = events.some(e =>
          e.startStr.substring(0,10) === date &&
          e.title.toLowerCase().includes(slot)
        );

        if (exists) {
          alert("Sorry, this slot is already booked!");
          return;
        }

        //  Redirect to booking form
        if (confirm(`Book ${slot} on ${date}?`)) {
          window.location.href = `booking_form.php?date=${date}&slot=${slot}`;
        }
      },

      eventDidMount: function(info) {
        // Bootstrap tooltip for better UX
        new bootstrap.Tooltip(info.el, {
          title: info.event.title,
          placement: 'top',
          trigger: 'hover'
        });
      }
    });

    calendar.render();
  });
  </script>
</body>
</html>


