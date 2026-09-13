<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "config.php";

try {

    // السماح بطلبات POST فقط
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);

        echo json_encode([
            "success" => false,
            "message" => "طريقة الطلب غير مسموحة."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // قراءة البيانات
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $message = trim($_POST["message"] ?? "");

    // التحقق من الحقول
    if ($name === "" || $email === "" || $message === "") {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "يرجى ملء جميع الحقول."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // التحقق من البريد الإلكتروني
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);

        echo json_encode([
            "success" => false,
            "message" => "يرجى إدخال بريد إلكتروني صحيح."
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | حفظ الرسالة في قاعدة البيانات
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO contact_messages
        (name, email, message, is_read)
        VALUES
        (?, ?, ?, 0)
    ");

    $stmt->execute([
        $name,
        $email,
        $message
    ]);

    /*
    |--------------------------------------------------------------------------
    | إرسال الرسالة إلى Telegram
    |--------------------------------------------------------------------------
    */

    $botToken = getenv("TELEGRAM_BOT_TOKEN");
    $chatId = getenv("TELEGRAM_CHAT_ID");

    /*
    |--------------------------------------------------------------------------
    | إذا كانت بيانات Telegram موجودة، أرسل الرسالة
    |--------------------------------------------------------------------------
    */

    if ($botToken && $chatId) {

        $telegramMessage =
"📩 رسالة جديدة من موقع براء عبد السلام

👤 الاسم:
{$name}

📧 البريد الإلكتروني:
{$email}

💬 الرسالة:
{$message}

━━━━━━━━━━━━━━

🌿 موقع براء عبد السلام";

        $telegramResponse = @file_get_contents(
            "https://api.telegram.org/bot" .
            urlencode($botToken) .
            "/sendMessage?" .
            http_build_query([
                "chat_id" => $chatId,
                "text" => $telegramMessage
            ])
        );
    }

    /*
    |--------------------------------------------------------------------------
    | نجاح العملية
    |--------------------------------------------------------------------------
    */

    http_response_code(200);

    echo json_encode([
        "success" => true,
        "message" => "تم إرسال رسالتك بنجاح."
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "حدث خطأ أثناء حفظ الرسالة."
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "حدث خطأ أثناء إرسال الرسالة."
    ], JSON_UNESCAPED_UNICODE);
}
?>