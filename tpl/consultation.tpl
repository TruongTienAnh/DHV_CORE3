// Chèn header và footer vào body của trang
body|prepend = <?php
$router = explode('/', $_SERVER['REQUEST_URI']);

if($router[1] !== $this->admin_path)
echo $this->header;
?>
body|append  = <?php
if($router[1] !== $this->admin_path)
echo $this->footer;
?>

// Xóa tất cả các option trong dropdown, chỉ giữ lại option đầu tiên làm mẫu
select[name="service_package"] option|deleteAllButFirst

// Bắt đầu vòng lặp, duyệt qua mảng các gói dịch vụ
select[name="service_package"] option|before = <?php foreach ($this->service_packages as $item): ?>

    // Gán giá trị và nội dung cho từng option được tạo ra
    option|value = <?php echo htmlspecialchars($item['id']); ?>
    option|innerText = <?php echo htmlspecialchars($item['title'] . ' (' . $item['type'] . ')'); ?>

// Kết thúc vòng lặp
select[name="service_package"] option|after = <?php endforeach; ?>

// Gán thuộc tính "action" cho form
#consultationForm|action = $_SERVER['REQUEST_URI']


// Thêm biến giả để tránh lỗi vtpl nếu trang không có dữ liệu
#dummy-variable-for-vtpl-fix = $this->dummy