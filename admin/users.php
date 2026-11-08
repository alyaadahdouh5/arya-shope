<?php
$page_title = 'إدارة المستخدمين - Arya Shope';
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('❌ خطأ أمني (CSRF)');
    }

    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'] ?? 'customer';
    
    $allowed_roles = ['customer', 'admin'];
    if (!in_array($role, $allowed_roles)) {
        $role = 'customer';
    }
    
    if ($user_id == $_SESSION['user_id']) {
        $error = '❌ لا يمكن تغيير دورك الخاص';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $stmt->execute([$role, $user_id]);
            $success = '✅ تم تحديث دور المستخدم';
        } catch(PDOException $e) {
            $error = '❌ حدث خطأ أثناء التحديث';
        }
    }
}

if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    if ($user_id == $_SESSION['user_id']) {
        $error = '❌ لا يمكن حذف حسابك الخاص';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $has_orders = $stmt->fetch()['count'] > 0;
            
            if ($has_orders) {
                $error = '❌ لا يمكن حذف المستخدم لأنه لديه طلبات';
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $success = '✅ تم حذف المستخدم بنجاح';
            }
        } catch(PDOException $e) {
            $error = '❌ حدث خطأ أثناء الحذف';
        }
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $users = [];
}

require_once '../includes/header.php';
?>

<main class="container" style="padding: 30px 0;">
    <header style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h1 style="color: var(--primary);">👤 إدارة المستخدمين</h1>
        <a href="dashboard.php" class="btn btn-outline" style="padding: 8px 20px;">← العودة للوحة التحكم</a>
    </header>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <section style="overflow-x: auto; background: white; border-radius: var(--radius); border: 1px solid var(--border-color); padding: 20px;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid #f0f0f0;">
                    <th style="padding: 10px; text-align: right;">#</th>
                    <th style="padding: 10px; text-align: right;">الاسم</th>
                    <th style="padding: 10px; text-align: right;">البريد الإلكتروني</th>
                    <th style="padding: 10px; text-align: right;">الهاتف</th>
                    <th style="padding: 10px; text-align: right;">الدور</th>
                    <th style="padding: 10px; text-align: right;">تاريخ التسجيل</th>
                    <th style="padding: 10px; text-align: center;">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr style="border-bottom: 1px solid #f0f0f0;">
                        <td style="padding: 10px;"><?= $user['user_id'] ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($user['full_name']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($user['email']) ?></td>
                        <td style="padding: 10px;"><?= htmlspecialchars($user['phone'] ?? '-') ?></td>
                        <td style="padding: 10px;">
                            <form method="POST" action="" style="display: flex; gap: 5px; align-items: center;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <select name="role" style="padding: 5px 10px; border-radius: 6px; border: 1px solid #ddd; <?= $user['user_id'] == $_SESSION['user_id'] ? 'opacity: 0.5;' : '' ?>" <?= $user['user_id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                    <option value="customer" <?= $user['role'] == 'customer' ? 'selected' : '' ?>>🛒 عميل</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>🛡️ مدير</option>
                                </select>
                                <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                    <button type="submit" name="update_role" class="btn btn-secondary" style="padding: 4px 12px; font-size: 12px;">تحديث</button>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td style="padding: 10px; font-size: 12px;"><?= date('Y-m-d', strtotime($user['created_at'])) ?></td>
                        <td style="padding: 10px; text-align: center;">
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <nav>
                                    <a href="?delete=<?= $user['user_id'] ?>" class="btn btn-danger" style="padding: 4px 10px; font-size: 12px;" onclick="return confirm('هل أنت متأكد من حذف هذا المستخدم؟')">🗑️</a>
                                </nav>
                            <?php else: ?>
                                <span style="color: var(--gray); font-size: 12px;">أنت</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<?php require_once '../includes/footer.php'; ?>