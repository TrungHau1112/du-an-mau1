<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php"); // Chuyển hướng nếu không phải là admin
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang quản trị viên</title>
</head>
<body>
    <h2>Chào mừng, Quản trị viên <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
    <a href="logout.php">Đăng xuất</a>
    <!-- Thêm các chức năng quản trị tại đây -->
</body>
</html>
