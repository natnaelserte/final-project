<!DOCTYPE html>
<html>
<head>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
    }

    body {
      display: flex;
      flex-direction: column;
      min-height: 100%;
    }

    footer {
      background-color: #0d1b2a;
      color: #f8f9fa;
      height: 130px;
      font-size: 14px;
      position: relative;
      margin-top: auto; /* Ensures the footer is pushed to the bottom */
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }

    .row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .col-xs-6 {
      flex: 0 0 48%;
    }

    a {
      color: #dee2e6;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <!-- Content of your page goes here -->

  <!-- Footer -->
  <footer>
    <div class="container">
      <div class="row">
        
        <!-- Contact Info -->
        <div class="col-xs-6 text-left">
          <strong>Contact:</strong><br>
          📞 <a href="tel:+251996783577">+251996783577</a><br>
          📧 <a href="mailto:absirmulugeta12@gmail.com">absirmulugeta12@gmail.com</a><br>
          💬 <a href="https://t.me/@absir10">Telegram</a>
        </div>

        <!-- FAQ & Copy -->
        <div class="col-xs-6 text-right">
          <a href="faq.php">FAQ</a><br>
          <span>© 2025 Developed by CS Students</span>
        </div>

      </div>
    </div>
  </footer>

</body>
</html>
