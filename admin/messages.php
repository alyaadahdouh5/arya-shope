<?php
$page_title = 'إدارة الرسائل - Arya Shope';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error = '';
$success = '';

if (isset($_GET['read'])) {
    $message_id = (int)$_GET['read'];
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $success = '✅ تم تحديد الرسالة كمقروءة';
    } catch(PDOException $e) {
        $error = '❌ حدث خطأ أثناء التحديث';
    }
}

if (isset($_GET['delete'])) {
    $message_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE message_id = ?");
        $stmt->execute([$message_id]);
        $success = '✅ تم حذف الرسالة بنجاح';
    } catch(PDOException $e) {
        $error = '❌ حدث خطأ أثناء الحذف';
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY submitted_at DESC");
    $messages = $stmt->fetchAll();
} catch(PDOException $e) {
    $messages = [];
}

require_once '../includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h1 style="color: var(--primary);">✉️ إدارة الرسائل</h1>
        <a href="dashboard.php" class="btn btn-outline" style="padding: 8px 20px;">← العودة للوحة التحكم</a>
    </header>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($messages)): ?>
        <div class="alert alert-info">لا توجد رسائل</div>
    <?php else: ?>
        <section style="display: grid; gap: 15px;">
            <?php foreach ($messages as $message): ?>
                <article style="background: <?= $message['is_read'] ? 'white' : '#fff3e0' ?>; padding: 20px; border-radius: var(--radius); border: 1px solid <?= $message['is_read'] ? 'var(--border-color)' : 'var(--primary)' ?>; box-shadow: var(--shadow);">
                    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h2 style="margin-bottom: 5px; font-size: 1.2em;">
                                <?= htmlspecialchars($message['subject']) ?>
                                <?php if (!$message['is_read']): ?>
                                    <span style="background: #d32f2f; color: white; padding: 2px 10px; border-radius: 20px; font-size: 11px; margin-right: 10px;">جديد</span>
                                <?php endif; ?>
                            </h2>
                            <p style="color: var(--gray); font-size: 14px;">
                                👤 <?= htmlspecialchars($message['name']) ?> | 
                                📧 <?= htmlspecialchars($message['email']) ?> | 
                                🕐 <?= date('Y-m-d H:i', strtotime($message['submitted_at'])) ?>
                            </p>
                        </div>
                        <nav style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <?php if (!$message['is_read']): ?>
                                <a href="?read=<?= $message['message_id'] ?>" class="btn btn-secondary" style="padding: 6px 15px; font-size: 13px;">📖 قرأت</a>
                            <?php endif; ?>
                            <a href="?delete=<?= $message['message_id'] ?>" class="btn btn-danger" style="padding: 6px 15px; font-size: 13px;" onclick="return confirm('هل أنت متأكد؟')">🗑️ حذف</a>
                        </nav>
                    </div>
                    <div style="margin-top: 10px; padding: 15px; background: var(--primary-bg); border-radius: 8px;">
                        <p style="margin: 0; line-height: 1.8;"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>

<?php require_once '../includes/footer.php'; ?>