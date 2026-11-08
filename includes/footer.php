</main>
    <footer>
        <div class="container">
            <div class="footer-content">
                
                <section class="footer-section">
                    <h3>🛍️ Arya Shope</h3>
                    <p>متجرك الموثوق للملابس والإكسسوارات</p>
                    <p style="margin-top: 10px; color: #f1c40f;">
                        ★ ★ ★ ★ ★
                    </p>
                </section>

                <nav class="footer-section">
                    <h4>روابط سريعة</h4>
                    <a href="<?php echo SITE_URL; ?>products.php">👕 جميع المنتجات</a>
                    <a href="<?php echo SITE_URL; ?>cart.php">🛒 سلة التسوق</a>
                    <a href="<?php echo SITE_URL; ?>contact.php">📞 اتصل بنا</a>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo SITE_URL; ?>admin/dashboard.php">📊 لوحة التحكم</a>
                    <?php endif; ?>
                </nav>

                <address class="footer-section">
                    <h4>معلومات الاتصال</h4>
                    <p>📞 <a href="tel:+972599759959" style="color: inherit; text-decoration: none;">+972 599759959</a></p>
                    <p>📧 <a href="mailto:alyaadahdouh52005@gmail.com" style="color: inherit; text-decoration: none;">alyaadahdouh52005@gmail.com</a></p>
                    <p>📍 فلسطين - غزة</p>
                </address>

                <section class="footer-section">
                    <h4>ساعات العمل</h4>
                    <p>🕐 السبت - الخميس: 9ص - 9م</p>
                    <p>🕐 الجمعة: مغلق</p>
                </section>

            </div>

            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> Arya Shope - جميع الحقوق محفوظة</p>
                <p style="font-size: 14px; color: #aaa; margin-top: 8px;">
                    👩‍💻 تصميم وتطوير: <strong style="color: #FFA500;">علياء يونس الدحدوح</strong>
                </p>
                <p style="font-size: 13px; color: #888; margin-top: 3px;">
                    🎓 الرقم الجامعي: <strong style="color: #4CAF50;">20240650</strong>
                </p>
            </div>
        </div>
    </footer>

    <script src="<?php echo SITE_URL; ?>assets/js/main.js"></script>
</body>
</html>