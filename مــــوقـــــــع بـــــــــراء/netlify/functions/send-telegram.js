export default async (request) => {

    // السماح فقط بطلبات POST
    if (request.method !== "POST") {

        return new Response(
            JSON.stringify({
                success: false,
                message: "طريقة الطلب غير مسموحة."
            }),
            {
                status: 405,
                headers: {
                    "Content-Type": "application/json; charset=UTF-8"
                }
            }
        );

    }


    try {

        // قراءة البيانات القادمة من الموقع
        const data = await request.json();

        const name =
            String(data.name || "").trim();

        const email =
            String(data.email || "").trim();

        const message =
            String(data.message || "").trim();


        // التحقق من الحقول
        if (!name || !email || !message) {

            return new Response(
                JSON.stringify({
                    success: false,
                    message: "يرجى ملء جميع الحقول."
                }),
                {
                    status: 400,
                    headers: {
                        "Content-Type":
                            "application/json; charset=UTF-8"
                    }
                }
            );

        }


        // التحقق من البريد
        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {

            return new Response(
                JSON.stringify({
                    success: false,
                    message:
                        "يرجى إدخال بريد إلكتروني صحيح."
                }),
                {
                    status: 400,
                    headers: {
                        "Content-Type":
                            "application/json; charset=UTF-8"
                    }
                }
            );

        }


        // قراءة بيانات Telegram من Netlify
        const botToken =
            process.env.TELEGRAM_BOT_TOKEN;

        const chatId =
            process.env.TELEGRAM_CHAT_ID;


        // التأكد من وجود الإعدادات
        if (!botToken || !chatId) {

            console.error(
                "Telegram environment variables are missing."
            );

            return new Response(
                JSON.stringify({
                    success: false,
                    message:
                        "إعدادات الرسائل غير مكتملة."
                }),
                {
                    status: 500,
                    headers: {
                        "Content-Type":
                            "application/json; charset=UTF-8"
                    }
                }
            );

        }


        // تجهيز الرسالة
        const telegramMessage =
`📩 رسالة جديدة من موقع براء عبد السلام

👤 الاسم:
${name}

📧 البريد الإلكتروني:
${email}

💬 الرسالة:
${message}

━━━━━━━━━━━━━━

🌿 موقع براء عبد السلام`;


        // إرسال الرسالة إلى Telegram
        const telegramResponse =
            await fetch(
                `https://api.telegram.org/bot${botToken}/sendMessage`,
                {
                    method: "POST",

                    headers: {
                        "Content-Type":
                            "application/json"
                    },

                    body: JSON.stringify({

                        chat_id: chatId,

                        text: telegramMessage

                    })

                }
            );


        const telegramResult =
            await telegramResponse.json();


        // التحقق من نجاح Telegram
        if (
            !telegramResponse.ok ||
            !telegramResult.ok
        ) {

            console.error(
                "Telegram error:",
                telegramResult
            );

            return new Response(
                JSON.stringify({
                    success: false,
                    message:
                        "تعذر إرسال الرسالة إلى Telegram."
                }),
                {
                    status: 500,
                    headers: {
                        "Content-Type":
                            "application/json; charset=UTF-8"
                    }
                }
            );

        }


        // نجاح
        return new Response(
            JSON.stringify({
                success: true,
                message:
                    "تم إرسال رسالتك بنجاح."
            }),
            {
                status: 200,
                headers: {
                    "Content-Type":
                        "application/json; charset=UTF-8"
                }
            }
        );


    } catch (error) {

        console.error(
            "Send Telegram Error:",
            error
        );


        return new Response(
            JSON.stringify({
                success: false,
                message:
                    "حدث خطأ أثناء إرسال الرسالة."
            }),
            {
                status: 500,
                headers: {
                    "Content-Type":
                        "application/json; charset=UTF-8"
                }
            }
        );

    }

};