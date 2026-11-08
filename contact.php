<?php
$page_title = 'اتصل بنا - Arya Shope';
require_once 'includes/config.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <header>
        <h1 style="color: var(--primary); margin-bottom: 30px;">📞 اتصل بنا</h1>
    </header>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">✅ تم إرسال رسالتك بنجاح! شكراً لتواصلك معنا.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] == 1): ?>
            <div class="alert alert-error">❌ الرجاء ملء جميع الحقول.</div>
        <?php elseif ($_GET['error'] == 2): ?>
            <div class="alert alert-error">❌ البريد الإلكتروني غير صحيح.</div>
        <?php elseif ($_GET['error'] == 3): ?>
            <div class="alert alert-error">❌ حدث خطأ أثناء إرسال الرسالة. حاول مرة أخرى.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        <section
            style="background: white; padding: 30px; border-radius: var(--radius); border: 1px solid var(--border-color);">
            <h2 style="margin-bottom: 20px; font-size: 1.17em;">✉️ أرسل رسالة</h2>

            <form action="contact_process.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="name">👤 الاسم</label>
                    <input type="text" id="name" name="name" required placeholder="أدخل اسمك الكامل">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="email">📧 البريد الإلكتروني</label>
                    <input type="email" id="email" name="email" required placeholder="example@email.com">
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="subject">📌 الموضوع</label>
                    <select id="subject" name="subject" required>
                        <option value="">اختر الموضوع</option>
                        <option value="استفسار">📝 استفسار</option>
                        <option value="شكوى">⚠️ شكوى</option>
                        <option value="اقتراح">💡 اقتراح</option>
                        <option value="طلب شراء">🛍️ طلب شراء</option>
                    </select>
                </fieldset>

                <fieldset class="form-group" style="border: none; padding: 0; margin: 0 0 18px 0;">
                    <label for="message">💬 الرسالة</label>
                    <textarea id="message" name="message" rows="5" required placeholder="اكتب رسالتك هنا..."></textarea>
                </fieldset>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    📤 إرسال الرسالة
                </button>
            </form>
        </section>

        <aside>
            <address
                style="background: white; padding: 30px; border-radius: var(--radius); border: 1px solid var(--border-color); margin-bottom: 20px; font-style: normal;">
                <h2 style="margin-bottom: 20px; font-size: 1.17em;">📍 معلومات الاتصال</h2>

                <div style="margin-bottom: 15px;">
                    <span style="font-size: 24px; color: var(--secondary);">📍</span>
                    <p style="margin-top: 5px;"><strong>العنوان:</strong> فلسطين - غزة</p>
                </div>

                <div style="margin-bottom: 15px;">
                    <span style="font-size: 24px; color: var(--secondary);">📞</span>
                    <p style="margin-top: 5px;"><strong>الهاتف:</strong> <a href="tel:+970599123456"
                            style="color: inherit; text-decoration: none;">+970 59 912 3456</a></p>
                </div>

                <div style="margin-bottom: 15px;">
                    <span style="font-size: 24px; color: var(--secondary);">📧</span>
                    <p style="margin-top: 5px;"><strong>البريد الإلكتروني:</strong> <a href="mailto:info@aryashope.com"
                            style="color: inherit; text-decoration: none;">info@aryashope.com</a></p>
                </div>
            </address>

            <section
                style="background: white; padding: 30px; border-radius: var(--radius); border: 1px solid var(--border-color);">
                <h2 style="margin-bottom: 20px; font-size: 1.17em;">🕐 ساعات العمل</h2>
                <p style="margin-bottom: 8px;">🟢 السبت - الخميس: 9:00 ص - 9:00 م</p>
                <p>🔴 الجمعة: مغلق</p>
            </section>

            <footer
                style="background: #fef9e7; padding: 20px; border-radius: var(--radius); border: 1px solid #f9e79f; margin-top: 20px; text-align: center;">
                <p style="font-size: 14px; color: #7d6608;">
                    👩‍💻 تصميم وتطوير: <strong>علياء يونس الدحدوح</strong>
                </p>
                <p style="font-size: 13px; color: #7d6608;">
                    🎓 الرقم الجامعي: <strong>20240650</strong>
                </p>
            </footer>
        </aside>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>