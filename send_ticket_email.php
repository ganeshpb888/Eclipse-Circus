<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Include PHPMailer library

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the ticket details from the POST request
    $ticket_id = $_POST['ticket_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $show_date_db = $_POST['show_date'];
    $show_time_db = $_POST['show_time'];
    $totalamount = $_POST['totalamount'];
    $quantity = $_POST['quantity'];
    $address = $_POST['address'];

    $date_from_db = $show_date_db; // Date from the database
    $show_date = date('d M Y', strtotime($date_from_db));
    $time_from_db= $show_time_db; // Time from the database
    $show_time = date('h:i A', strtotime($time_from_db));

    $qrCodeData = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($ticket_id) . '&size=200x200';

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'Eclipsecircusindia@gmail.com'; 
        $mail->Password = 'jizm izyi pohi byif'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('accidenttdetected@gmail.com', 'Eclipse Circus');
        $mail->addAddress($email, $name); // Send to the customer's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Eclipse Circus Ticket';
        $mail->Body = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Bingo Circus - Your Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: white;
            padding: 20px;
        }

        .ticket-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            border: 2px solid black;
            margin: 0 auto;
            text-align: center;
        }

        h1 {
            color: #ff5722;
            font-size: 24px;
            margin-bottom: 10px;
        }

        h2 {
            color: #000;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .ticket-info {
            font-size: 18px;
            margin-bottom: 20px;
            text-align: left;
        }

        .ticket-info p {
            margin-left:15px;
            margin-bottom:8px;
            font-size: 18px;
        }

        .ticket-info strong {
            color: #ff5722;
        }

        .qr-code {
            margin: 20px auto;
            width: 60%;
            height: 60%;
        }

        .qr-code img {
            width: 100%;
            height: 100%;
        }

        .price {
            background-color: #ff5722;
            color: white;
            font-size: 15px;
            padding: 0.1px;
            border-radius: 0px 0px 10px 10px;
            margin-top: 20px;
        }

        .ticket-header img {
            margin-top: 10px;
            width: 80px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class='ticket-container'>
        <h1>EclipseCircus</h1>
        <div class='ticket-header'>
            <img src='https://Eclipsecircus.co.in/wp-content/uploads/2024/09/cropped-BC-Logo-PNG.png' alt='Eclipse Circus'>
        </div>
        <h2>Your Ticket</h2>
        <div class='ticket-info'>
            <p><strong>Ticket ID:</strong> $ticket_id</p>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Show Date:</strong> $show_date</p>
            <p><strong>Show Time:</strong> $show_time</p>
            <p><strong>Location:</strong> $address</p>
            <p><strong>Quantity:</strong> $quantity</p>
        </div>
        <div class='qr-code'>
            <img src='$qrCodeData' alt='QR Code' />
        </div>
        <div class='price'>
            <p><strong>Total Price:</strong> $totalamount Rs</p>
        </div>
    </div>
</body>
</html>
";

      
        $mail->send();
        echo "Ticket sent successfully to $email.";
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Invalid request method.";
}
?>