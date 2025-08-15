<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Перевірка методу запиту
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Отримання даних з POST запиту
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Валідація обов'язкових полів
$required_fields = ['name', 'email', 'message'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
        exit;
    }
}

// Отримання даних з форми
$name = trim($input['name']);
$email = trim($input['email']);
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$company = isset($input['company']) ? trim($input['company']) : '';
$message = trim($input['message']);

// Валідація email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Захист від спаму - перевірка honeypot поля
if (!empty($input['honeypot'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Spam detected']);
    exit;
}

// Захист від спаму - перевірка часу заповнення форми
if (isset($input['timestamp'])) {
    $time_diff = time() - intval($input['timestamp']);
    if ($time_diff < 3) { // Форма заповнена занадто швидко
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Form submitted too quickly']);
        exit;
    }
}

// Захист від спаму - перевірка кількості символів
if (strlen($message) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message is too short']);
    exit;
}

if (strlen($message) > 2000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message is too long']);
    exit;
}

// Захист від спаму - перевірка на підозрілі слова
$spam_words = ['casino', 'viagra', 'loan', 'credit', 'buy now', 'click here', 'free money'];
$message_lower = strtolower($message);
foreach ($spam_words as $word) {
    if (strpos($message_lower, $word) !== false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Message contains spam content']);
        exit;
    }
}

// Підготовка email повідомлення
$to = 'info@coprime.it';
$subject = 'Нова заявка з сайту Coprime IT - Business Central';

$email_body = "Нова заявка з сайту:\n\n";
$email_body .= "Ім'я: " . htmlspecialchars($name) . "\n";
$email_body .= "Email: " . htmlspecialchars($email) . "\n";
if ($phone) {
    $email_body .= "Телефон: " . htmlspecialchars($phone) . "\n";
}
if ($company) {
    $email_body .= "Компанія: " . htmlspecialchars($company) . "\n";
}
$email_body .= "Повідомлення:\n" . htmlspecialchars($message) . "\n\n";
$email_body .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$email_body .= "IP: " . $_SERVER['REMOTE_ADDR'] . "\n";

$headers = [
    'From: noreply@coprime.it',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion()
];

// Відправка email
$mail_sent = mail($to, $subject, $email_body, implode("\r\n", $headers));

if ($mail_sent) {
    // Логування успішних заявок
    $log_entry = date('Y-m-d H:i:s') . " - SUCCESS - Name: $name, Email: $email, IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents('contact_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Дякуємо за ваше повідомлення! Ми зв\'яжемося з вами найближчим часом.'
    ]);
} else {
    // Логування помилок
    $log_entry = date('Y-m-d H:i:s') . " - ERROR - Name: $name, Email: $email, IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents('contact_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Помилка відправки повідомлення. Спробуйте ще раз або зв\'яжіться з нами по телефону.'
    ]);
}
?>
