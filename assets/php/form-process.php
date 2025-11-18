<?php
// Simple form processor for Diamond Signature Cleaning Service
// Expects POST fields: name, email, msg_subject, phone_number, message, gridCheck

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Invalid request method.';
    exit;
}

function sanitize_text($str) {
    $str = is_string($str) ? $str : '';
    // FILTER_SANITIZE_STRING is deprecated in newer PHP; fallback to strip_tags
    $str = strip_tags($str);
    return trim($str);
}

function sanitize_email_field($email) {
    $email = is_string($email) ? $email : '';
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    // prevent header injection
    $email = str_replace(["\r", "\n", "%0a", "%0d"], '', $email);
    return $email;
}

$name        = sanitize_text($_POST['name'] ?? '');
$email       = sanitize_email_field($_POST['email'] ?? '');
$subject_in  = sanitize_text($_POST['msg_subject'] ?? '');
$phone       = sanitize_text($_POST['phone_number'] ?? '');
$message_in  = trim((string)($_POST['message'] ?? ''));
$consent     = isset($_POST['gridCheck']) ? 'Yes' : 'No';

if ($name === '' || $subject_in === '' || $message_in === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo 'Please complete all required fields with a valid email.';
    exit;
}

// Recipient email — update if needed
$to = 'helendiamond@yahoo.com';

$subject = 'New Website Enquiry: ' . $subject_in;

$body_lines = [
    'You have a new message from Diamond Signature Cleaning Service website.',
    '',
    'Name: ' . $name,
    'Email: ' . $email,
    'Phone: ' . ($phone ?: 'N/A'),
    'Consent: ' . $consent,
    '',
    'Message:',
    $message_in,
    '',
    'IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
    'Time: ' . date('Y-m-d H:i:s'),
];
$body = implode("\n", $body_lines);

$from_domain = $_SERVER['SERVER_NAME'] ?? 'localhost';
$from = 'no-reply@' . $from_domain;

$headers = [];
$headers[] = 'From: ' . $from;
$headers[] = 'Reply-To: ' . $email;
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$headers_str = implode("\r\n", $headers);

$sent = @mail($to, $subject, $body, $headers_str);

if ($sent) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Unable to send email at this time.';
}
