<?php
// Printer connection settings
$printer_ip = "10.10.10.104"; // Replace with your printer's IP address
$printer_port = 9100; // Default port for network printers

// Barcode data
$barcode_data = "123456789012"; // Replace with your barcode data

// Create a socket connection to the printer
$printer = fsockopen($printer_ip, $printer_port);

if (!$printer) {
    die("Unable to connect to printer");
}

// ESC/POS command to initialize the printer
fwrite($printer, "\x1B\x40");

// ESC/POS command to set barcode type
fwrite($printer, "\x1D\x6B\x49"); // Barcode Type: Code 128
// ESC/POS command to print the barcode
fwrite($printer, "\x1D\x6B\x49" . chr(strlen($barcode_data)) . $barcode_data);

// ESC/POS command to cut the paper
fwrite($printer, "\x1D\x56\x00");

// Close the connection
fclose($printer);

echo "Barcode sent to printer";
?>
