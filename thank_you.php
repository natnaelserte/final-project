<!-- thank_you.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMU Online Voting</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome for the checkmark icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
	background: linear-gradient(to top, rgba(26, 29, 38), rgba(30, 110, 157));
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh; /* Make sure it covers the whole viewport */
            color: white; /* Set text color to white for better visibility on the background */
        }

        .thank-you-container {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
        }

        .success-icon {
            font-size: 5em;
            color: #28a745; /* Green color for success */
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
    </style>
</head>
<body>
    <div class="thank-you-container">
        <i class="fas fa-check-circle success-icon"></i>
        <h1>Thank You for Voting!</h1>
        <p>Your vote has been successfully submitted.</p>
        <a href="index.php" class="btn btn-success">Return to Home</a>
    </div>

    <!-- Bootstrap JS (optional, but needed for some features) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>