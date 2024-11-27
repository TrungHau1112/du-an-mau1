<?php
ob_start();
include_once 'adu.php';
session_start();
include_once 'DBUtil.php';
$dbHelper = new DBUtil();
$uploadDir = 'images/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$categories = $dbHelper->select('SELECT * FROM categories');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        // Kiểm tra xem tên sản phẩm đã tồn tại chưa
        $name = $_POST['name'];
        $existingProduct = $dbHelper->select("SELECT * FROM products WHERE name = :name", ['name' => $name]);

        if (count($existingProduct) > 0) {
            // Nếu tên sản phẩm đã tồn tại, thông báo lỗi
            $_SESSION['message'] = ['text' => 'Sản phẩm với tên này đã tồn tại!', 'type' => 'danger'];
        } else {
            // Nếu chưa tồn tại, tiến hành thêm sản phẩm mới
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => $_POST['price'],
                'category_id' => $_POST['category_id'],
                'quantity' => $_POST['quantity'],
                'size' => $_POST['size'],
                'image' => uploadImage()
            ];
            if ($dbHelper->insert('products', $data)) {
                $_SESSION['message'] = ['text' => 'Sản phẩm đã được thêm thành công!', 'type' => 'success'];
            } else {
                $_SESSION['message'] = ['text' => 'Đã có lỗi xảy ra. Vui lòng thử lại!', 'type' => 'danger'];
            }
        }
        header("Location: product.php");
        exit;
    } elseif (isset($_POST['delete_product'])) {
        // Xóa sản phẩm
        $id = $_POST['id'];
        if ($dbHelper->delete('products', 'id = :id', ['id' => $id])) {
            $_SESSION['message'] = ['text' => 'Sản phẩm đã được xóa thành công!', 'type' => 'success'];
            header("Location: product.php");
            exit;
        }
    }
}

function uploadImage() {
    global $uploadDir;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $filename = basename($_FILES['image']['name']);
        $targetFile = $uploadDir . uniqid() . '_' . $filename;
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
        return $targetFile;
    }
    return null;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;
$products = $dbHelper->select("SELECT * FROM products WHERE name LIKE :search LIMIT $limit OFFSET $offset", ['search' => "%$search%"]);
$totalProducts = $dbHelper->select("SELECT COUNT(*) as count FROM products WHERE name LIKE :search", ['search' => "%$search%"]);
$totalPages = ceil($totalProducts[0]['count'] / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Trị Viên</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: Arial, sans-serif;
            color: #333;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-warning {
            font-size: 16px;
            padding: 10px 20px;
            background-color: #f39c12;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-warning:hover {
            background-color: #e67e22;
        }

        .table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
        }

        .table td {
            background-color: #f9f9f9;
            font-size: 14px;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            transition: transform 0.2s, background-color 0.3s;
        }

        .btn:hover {
            transform: scale(1.05);
            background-color: #45a049;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .form-group input[type="file"] {
            padding: 8px;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }

        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        img {
            max-width: 100px;
            border-radius: 5px;
            margin-top: 5px;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeeba;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
        }

        .pagination {
            justify-content: center;
        }

        .page-item {
            margin: 0 5px;
        }

        .page-item a {
            color: #007bff;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .page-item.active a {
            background-color: #007bff;
            color: white;
        }

        .page-item a:hover {
            background-color: #0056b3;
            color: white;
        }

        h3 {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .btn-danger {
            font-size: 16px;
            padding: 10px 20px;
            background-color: #e74c3c;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-danger:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['message']['text'] ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <div class="text-right mb-3">
            <a href="logout.php" class="btn btn-danger">Đăng Xuất</a>
        </div>

        <h3>Thêm Sản Phẩm</h3>
        <form method="post" enctype="multipart/form-data" class="mb-4">
            <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Tên sản phẩm" required>
            </div>
            <div class="form-group">
                <textarea name="description" class="form-control" placeholder="Mô tả" required></textarea>
            </div>
            <div class="form-group">
                <input type="number" step="0.01" name="price" class="form-control" placeholder="Giá" required>
            </div>
            <div class="form-group">
                <select name="category_id" class="form-control" required>
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="number" name="quantity" class="form-control" placeholder="Số lượng" required>
            </div>
            <div class="form-group">
                <select name="size" class="form-control" required>
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                    <option value="XXL">XXL</option>
                </select>
            </div>
            <div class="form-group">
                <input type="file" name="image" class="form-control" required>
            </div>
            <button type="submit" name="add_product" class="btn btn-primary">Thêm Sản Phẩm</button>
        </form>

        <h3>Danh Sách Sản Phẩm</h3>
        <form method="get" action="" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm" value="<?= htmlspecialchars($search) ?>">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">Tìm kiếm</button>
                </div>
            </div>
        </form>
        
        <?php if (count($products) > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Mô Tả</th>
                    <th>Giá</th>
                    <th>Số Lượng</th>
                    <th>Kích Thước</th>
                    <th>Hình Ảnh</th>
                    <th>Danh Mục</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['description']) ?></td>
                    <td><?= number_format($product['price'], 2) ?> VND</td>
                    <td><?= $product['quantity'] ?></td>
                    <td><?= $product['size'] ?></td>
                    <td>
                        <?php if ($product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" width="100" alt="Hình ảnh sản phẩm">
                        <?php else: ?>
                            <p>Không có hình ảnh</p>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $category = $dbHelper->select("SELECT name FROM categories WHERE id = :id", ['id' => $product['category_id']]);
                        echo htmlspecialchars($category[0]['name']);
                        ?>
                    </td>
                    <td>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" name="delete_product" class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                        <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-warning btn-sm">Sửa</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <nav>
            <ul class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php else: ?>
            <div class='alert alert-warning'>Không có sản phẩm nào phù hợp với tìm kiếm của bạn.</div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
