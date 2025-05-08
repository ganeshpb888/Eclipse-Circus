<?php
include 'database/db_connect.php';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assuming the payment ID is passed via GET request after successful payment
$payment_id = $_GET['payment_id'];

// Query the booking details using the payment ID
$sql = "SELECT id, name, email, show_date, show_time, quantity, totalamount, address FROM bookings WHERE payment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the booking details
if ($result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    $ticket_id = $booking['id'];
    $name = $booking['name'];
    $email = $booking['email']; // Fetch email address
    $show_date_db = $booking['show_date'];
    $show_time_db = $booking['show_time'];
    $totalamount = $booking['totalamount'];
    $quantity = $booking['quantity'];
    $address = $booking['address'];
    $date_from_db = $show_date_db; // Date from the database
    $show_date = date('d M Y', strtotime($date_from_db));
    $time_from_db= $show_time_db; // Time from the database
    $show_time = date('h:i A', strtotime($time_from_db));
} else {
    echo "No booking found! Please contact customer care if payment is deducted.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="https://Eclipsecircus.co.in/wp-content/uploads/2024/09/cropped-BC-Logo-PNG.png">
    <title>Eclipse Circus - Your Ticket</title>
    <style>
        /* Circular loading animation */
        .loading {
            border: 5px solid #f3f3f3;
            /* Light grey */
            border-top: 5px solid #3498db;
            /* Blue */
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            position: fixed;
            top: 45%;
            left: 45%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            display: none;
            /* Hidden by default */
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f1f1f1;
            padding: 5px;
            padding-top: 40px;
            transition: filter 0.3s ease;
            /* Smooth transition for the blur effect */
        }

        /* Blur effect */
        .blur {
            filter: blur(5px);
        }

        .ticket-container {
            min-width: 60%;
            max-width: 70%;
            margin: 0 auto;
            padding: 20px;
            border: 2px dashed #333;
            border-radius: 15px;
            background-color: white;
            position: relative;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }

        h1 {
            color: #ff5722;
            font-size: 32px;
            text-transform: uppercase;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .sent {
            color: red;
        }

        .ticket-info {
            text-align: left;
            font-size: 18px;
            line-height: 1.6;
            flex-grow: 1;
        }

        .ticket-info strong {
            color: #ff5722;
        }

        .ticket-info p {
            margin: 5px 0;
        }

        .qr-code {
            margin-left: 10px;
            padding: 40px;
            flex-shrink: 0;
            width: 200px;
            height: 200px;
            display: flex;
            justify-content: center;
        }

        .back-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-transform: uppercase;
            display: inline-block;
        }

        .download-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
            text-transform: uppercase;
            display: inline-block;
        }

        .download-btn:hover,
        .back-btn:hover {
            opacity: 0.9;
        }

        /* Adding realistic ticket style with a notch */
        .notch {
            width: 50px;
            height: 50px;
            background-color: white;
            border-radius: 50%;
            border: 2px dashed #333;
            position: absolute;
            top: -25px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Add a logo inside the notch */
        .notch img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Media query for mobile view */
        @media screen and (max-width: 600px) {
            body {
                padding: 5px;
                padding-top: 30px;
            }

            .ticket-container {
                flex-direction: column;
                align-items: left;
                min-width: 90%;
                margin: 0 auto;
                padding: 20px;
                border: 2px dashed #333;
                border-radius: 15px;
                background-color: white;
                position: relative;
            }

            .qr-code {
                margin-left: -10px;
                margin-top: 10px;
            }
        }

        /* Overlay to blur background */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.7);
           
            backdrop-filter: blur(5px);
            
            z-index: 999;
           
            display: none;
           
        }
    </style>
</head>

<body>
    <!-- Overlay for background blur -->
    <div class="overlay" id="overlay"></div>

    <!-- Circular loading animation -->
    <div class="loading" id="loading-animation"></div>

    <div class="ticket-container" id="ticket-section">
        <div class="notch">
            <img src="https://Eclipsecircus.co.in/wp-content/uploads/2024/09/cropped-BC-Logo-PNG.png" alt="Eclipse">
        </div>
        <div class="ticket-info">
            <h1>Eclipse Circus</h1>
            <h2>Your Ticket</h2>
            <p><strong>Ticket ID:</strong> <?php echo $ticket_id; ?></p>
            <p><strong>Name:</strong> <?php echo $name; ?></p>
            <p><strong>Show Date:</strong> <?php echo $show_date; ?></p>
            <p><strong>Show Time:</strong> <?php echo $show_time; ?></p>
            <p><strong>Location:</strong> <?php echo $address; ?></p>
            <p><strong>Quantity:</strong> <?php echo $quantity; ?></p>
            <p><strong>Total Price:</strong> ₹<?php echo $totalamount; ?></p>
        </div>

        <!-- QR Code container -->
        <div id="qrcode" class="qr-code"></div>
    </div>

    <!-- Buttons -->
    <p class="sent">Ticket Will Be Sent to Your Given Email Address Shortly</p>
    <button id="download-btn" class="download-btn">Download Ticket</button>
    <button id="back-btn" class="back-btn" onclick="window.location.href='index.html';">Back to Home</button>

    <!-- Include html2canvas for capturing the ticket as an image -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <!-- QRCode.js for generating QR codes -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Show circular loading animation and blur background
        function showLoading() {
            document.getElementById("loading-animation").style.display = "block";
            document.getElementById("overlay").style.display = "block"; // Show the blur overlay
        }

        // Hide circular loading animation and remove blur
        function hideLoading() {
            document.getElementById("loading-animation").style.display = "none";
            document.getElementById("overlay").style.display = "none"; // Hide the blur overlay
        }

        // Generate QR code based on the Ticket ID
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $ticket_id; ?>", // Using ticket ID as QR code data
            width: 200,
            height: 200,
        });

        // Add click event to the download button
        document.getElementById("download-btn").addEventListener("click", function () {
            html2canvas(document.getElementById("ticket-section")).then(function (canvas) {
                var link = document.createElement('a');
                link.href = canvas.toDataURL("image/png");
                link.download = "ticket_<?php echo $ticket_id; ?>.png";
                link.click();
            });
        });

        // Asynchronously send the ticket via email using AJAX
        function sendEmail() {
            $.ajax({
                url: 'send_ticket_email.php', // Separate PHP file for sending email
                type: 'POST',
                data: {
                    ticket_id: "<?php echo $ticket_id; ?>",
                    name: "<?php echo $name; ?>",
                    email: "<?php echo $email; ?>",
                    show_date: "<?php echo $show_date; ?>",
                    show_time: "<?php echo $show_time; ?>",
                    totalamount: "<?php echo $totalamount; ?>",
                    quantity: "<?php echo $quantity; ?>",
                    address: "<?php echo $address; ?>"
                },
                beforeSend: function () {
                    showLoading(); // Show loading animation
                },
                success: function (response) {
                    $('.sent').html(response); // Update the message after email is sent
                },
                complete: function () {
                    hideLoading(); // Hide loading animation
                }
            });
        }

        // Trigger the loading animation and email sending process
        $(document).ready(function () {
            sendEmail();
        });
    </script>
</body>

</html>