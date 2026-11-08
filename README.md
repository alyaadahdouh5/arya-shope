# Arya Shope 🛍️ | متجر إلكتروني متكامل

تطبيق ويب متكامل (Full-Stack) لبيع الملابس والإكسسوارات، مبني من الصفر باستخدام **PHP** و **MySQL**. يهدف المشروع إلى تطبيق أفضل معايير الأمان (Security Best Practices) وتقديم واجهة مستخدم عصرية ومتجاوبة (Responsive UI).

A complete, secure, and fully responsive E-commerce web application for clothing and accessories. Built from scratch using Vanilla PHP and MySQL, this project demonstrates full-stack development capabilities, secure coding practices, and modern UI/UX design.

---

## ✨ المميزات الرئيسية | Key Features

### 👤 للمستخدم (User Side)
- تصفح المنتجات والبحث فيها مع إمكانية الفلترة والترتيب.
- سلة تسوق تفاعلية (إضافة، تعديل الكمية، حذف).
- إتمام الطلب (Checkout) بأمان مع تحديث المخزون تلقائياً.
- تسجيل حساب جديد وتسجيل دخول مشفر.
- إدارة الملف الشخصي وسجل الطلبات.

### 🛡️ للمدير (Admin Dashboard)
- لوحة تحكم شاملة تعرض إحصائيات المتجر (الطلبات، المنتجات، المستخدمين، الرسائل).
- إدارة المنتجات (إضافة، تعديل، حذف، ورفع صور بشكل آمن).
- إدارة الطلبات (تحديث الحالة، حذف).
- إدارة المستخدمين (تغيير الصلاحيات، حذف).
- استقبال رسائل التواصل من العملاء.

---

## 🔐 الأمان | Security Implementations (OWASP Best Practices)
تم تطبيق أعلى معايير الحماية لمنع الثغرات الشائعة:
- **منع حقن SQL (SQL Injection):** استخدام `PDO Prepared Statements` في جميع الاستعلامات.
- **منع XSS:** تأمين جميع المخرجات باستخدام `htmlspecialchars`.
- **منع CSRF:** توليد والتحقق من توكن أمان (CSRF Token) في جميع النماذج وطلبات الـ AJAX.
- **أمان الجلسات:** إعادة توليد رقم الجلسة (Session ID) عند تسجيل الدخول لمنع اختطاف الجلسات.
- **تشفير كلمات المرور:** استخدام `password_hash` و `password_verify`.

---

## 🛠️ التقنيات المستخدمة | Tech Stack & Tools
- **Backend:** PHP (Vanilla)
- **Database:** MySQL (PDO)
- **Frontend:** HTML5, CSS3 (Responsive Design), JavaScript (ES6)
- **Server:** Apache (XAMPP)
- **🤖 AI-Assisted Development:** تم استخدام أدوات الذكاء الاصطناعي (AI tools) لتحسين جودة الكود، إجراء تدقيق أمني (Security Auditing)، حل الأخطاء، وضمان كتابة كود نظيف وجاهز للإنتاج بجودة عالية.

---

## 📂 هيكلية المشروع | Project Structure
- `admin/`: لوحة التحكم وملفات الإدارة.
- `assets/`: ملفات التصميم (CSS)، الجافاسكريبت (JS)، والصور.
- `includes/`: الملفات المشتركة (الهيدر، الفوتر، إعدادات الاتصال).
- الملفات الرئيسية: صفحات المستخدم (الرئيسية، المنتجات، السلة، إلخ).
- `ecommerce.sql`: ملف قاعدة البيانات (الجداول والبيانات الأولية).

---

## 🚀 طريقة التشغيل محلياً | How to Run Locally
1. قم بتثبيت **XAMPP** وتشغيل Apache و MySQL.
2. ضع مجلد المشروع داخل `C:\xampp\htdocs\arya-shope`.
3. افتح **phpMyAdmin** وقم باستيراد (Import) ملف `ecommerce.sql` داخل قاعدة بيانات باسم `ecommerce_db`.
4. افتح المتصفح على الرابط: `http://localhost/arya-shope/`
5. **بيانات دخول المدير (Admin Login):** 
   - الإيميل: `admin@shop.com`
   - الباسورد: `password`

---

## 👩‍💻 المطورة | Developer
**علياء يونس الدحدوح (Alyaa Younis Aldahdouh)**
- طالبة هندسة حاسوب | مطورة Full-Stack
- **GitHub:** [alyaadaldahdouh5](https://github.com/alyaadaldahdouh5)
- **Live Demo:** *(سيتم إضافته بعد الرفع على الاستضافة)*
