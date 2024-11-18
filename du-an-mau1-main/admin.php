<?php
session_start();
include_once 'DBUtil.php';

$dbHelper = new DBUtil();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php"); // Redirect if not admin
    exit;
}

$uploadDir = 'images/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$categories = $dbHelper->select('SELECT * FROM categories');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        // Thêm danh mục
        $data = ['name' => $_POST['category_name']];
        if ($dbHelper->insert('categories', $data)) {
            header("Location: admin.php");
            exit;
        }
    } elseif (isset($_POST['edit_category'])) {
        // Cập nhật danh mục
        $id = $_POST['category_id'];
        $data = ['name' => $_POST['category_name']];
        if ($dbHelper->update('categories', $data, 'id = :id', ['id' => $id])) {
            header("Location: admin.php");
            exit;
        }
    } elseif (isset($_POST['delete_category'])) {
        // Xóa danh mục
        $id = $_POST['category_id'];
        if ($dbHelper->delete('categories', 'id = :id', ['id' => $id])) {
            header("Location: admin.php");
            exit;
        }
    } elseif (isset($_POST['add_product'])) {
        // Thêm sản phẩm
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'],
            'price' => $_POST['price'],
            'category_id' => $_POST['category_id'],
            'image' => uploadImage()
        ];
        if ($dbHelper->insert('products', $data)) {
            header("Location: admin.php");
            exit;
        }
    } elseif (isset($_POST['delete_product'])) {
        // Xóa sản phẩm
        $id = $_POST['id'];
        if ($dbHelper->delete('products', 'id = :id', ['id' => $id])) {
            header("Location: admin.php");
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
        .table th, .table td {
            transition: background-color 0.3s ease;
        }
        .table tr:hover {
            background-color: #f1f1f1;
        }
        .btn {
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: scale(1.05);
        }
    </style>
    <script>
        function validateCategoryForm() {
            const categoryName = document.querySelector('input[name="category_name"]');
            if (categoryName.value.trim() === "") {
                alert("Tên danh mục không được để trống.");
                categoryName.focus();
                return false;
            }
            return true;
        }

        function validateProductForm() {
            const name = document.querySelector('input[name="name"]');
            const description = document.querySelector('textarea[name="description"]');
            const price = document.querySelector('input[name="price"]');
            const categoryId = document.querySelector('select[name="category_id"]');
            const image = document.querySelector('input[name="image"]');

            if (name.value.trim() === "") {
                alert("Tên sản phẩm không được để trống.");
                name.focus();
                return false;
            }
            if (description.value.trim() === "") {
                alert("Mô tả không được để trống.");
                description.focus();
                return false;
            }
            if (price.value.trim() === "" || isNaN(price.value) || Number(price.value) <= 0) {
                alert("Giá phải là một số dương.");
                price.focus();
                return false;
            }
            if (categoryId.value === "") {
                alert("Vui lòng chọn danh mục.");
                categoryId.focus();
                return false;
            }
            if (image.files.length === 0) {
                alert("Vui lòng chọn hình ảnh.");
                image.focus();
                return false;
            }
            return true;
        }

        function validateEditCategoryForm(modal) {
            const categoryName = modal.querySelector('input[name="category_name"]');
            if (categoryName.value.trim() === "") {
                alert("Tên danh mục không được để trống.");
                categoryName.focus();
                return false;
            }
            return true;
        }

        window.addEventListener('DOMContentLoaded', (event) => {
            document.querySelector('form[action=""]').onsubmit = validateCategoryForm;
            document.querySelector('form[enctype="multipart/form-data"]').onsubmit = validateProductForm;

            const editModals = document.querySelectorAll('.modal');
            editModals.forEach(modal => {
                const form = modal.querySelector('form');
                form.onsubmit = function() {
                    return validateEditCategoryForm(modal);
                };
            });
        });
    </script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Trang Quản Trị Viên</h1>
        <h2 class="text-center">Chào mừng, Quản Trị Viên <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h2>
        <div class="text-right mb-3">
            <a href="logout.php" class="btn btn-danger">Đăng Xuất</a>
        </div>

        <h3>Thêm Danh Mục</h3>
        <form method="post" class="mb-4">
            <div class="form-group">
                <input type="text" name="category_name" class="form-control" placeholder="Tên danh mục">
            </div>
            <button type="submit" name="add_category" class="btn btn-primary">Thêm Danh Mục</button>
        </form>

        <h3>Danh Sách Danh Mục</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tên Danh Mục</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= htmlspecialchars($category['name']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal<?= $category['id'] ?>">Sửa</button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                            <button type="submit" name="delete_category" class="btn btn-danger btn-sm">Xóa</button>
                        </form>
                    </td>
                </tr>

                <!-- Modal sửa danh mục -->
                <div class="modal fade" id="editModal<?= $category['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="editModalLabel<?= $category['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel<?= $category['id'] ?>">Sửa Danh Mục</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                    <div class="form-group">
                                        <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                    <button type="submit" name="edit_category" class="btn btn-primary">Cập Nhật</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Thêm Sản Phẩm</h3>
        <form method="post" enctype="multipart/form-data" class="mb-4">
            <div class="form-group">
                <input type="text" name="name" class="form-control" placeholder="Tên sản phẩm">
            </div>
            <div class="form-group">
                <textarea name="description" class="form-control" placeholder="Mô tả"></textarea>
            </div>
            <div class="form-group">
                <input type="number" step="0.01" name="price" class="form-control" placeholder="Giá">
            </div>
            <div class="form-group">
                <select name="category_id" class="form-control">
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <input type="file" name="image" class="form-control">
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>