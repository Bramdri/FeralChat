<?php
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error_log.txt');
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'secrets.php';

\Stripe\Stripe::setApiKey($stripeSecretKey);

// Webhook Secret
$endpoint_secret = 'whsec_6E3Pvd7PgqH6Klij6SppgxRI4xxXOd7p';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

$logFile = __DIR__ . '/webhook_log.txt';
$logMessage = "Received webhook at " . date('Y-m-d H:i:s') . "\n";

try {
    $logMessage .= "Payload: " . print_r($payload, true) . "\n";
    
    // Verify the signature
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    $logMessage .= '⚠️  Webhook error while parsing basic request: ' . $e->getMessage() . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    $logMessage .= '⚠️  Webhook error while validating signature: ' . $e->getMessage() . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    http_response_code(400);
    exit();
}

// Handle the event
switch ($event->type) {
    case 'checkout.session.completed':

        $session = $event->data->object;
        $checkoutSessionId = $session->id;
        $folderPath = __DIR__ . "/$checkoutSessionId";

        // Check if the folder exists, if not, create it
        if (!file_exists($folderPath)) {
            if (mkdir($folderPath, 0777, true)) {
                $logMessage .= "Created folder: $folderPath\n";
            } else {
                $logMessage .= "Failed to create folder: $folderPath\n";
            }
        }

        $sourceFiles = [
            'this.php',
        ];

        // Copy the files in the created folder
        foreach ($sourceFiles as $sourceFile) {

            $sourceFilePath = __DIR__ . '/' . $sourceFile;
            $destinationFilePath = $folderPath . '/' . $sourceFile;

            $renamedDestinationFilePath = $folderPath . '/' . 'index.php';
            if (file_exists($sourceFilePath)) {
                if (copy($sourceFilePath, $renamedDestinationFilePath)) {
                    $logMessage .= "Renamed and copied $sourceFile to $renamedDestinationFilePath\n";
                } else {
                    $logMessage .= "Failed to rename and copy $sourceFile to $renamedDestinationFilePath\n";
                }
            }
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);

        break;

    default:
        error_log('Received unknown event type');
}

// Respond with a success status
http_response_code(200);
?>