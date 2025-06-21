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

// Xóa tất cả các item dịch vụ trong file HTML, chỉ giữ lại item đầu tiên làm mẫu
.swiper-wrapper .col-lg-4|deleteAllButFirst

// Bắt đầu vòng lặp, duyệt qua mảng $this->services
.swiper-wrapper .col-lg-4|before = <?php foreach($this->services as $service): ?>

img.service-image|src = <?php echo "/templates/" . htmlspecialchars($service['service_image'] ?? ''); ?>
span.service-rate|innerText = <?php echo htmlspecialchars($service['rate'] ?? ''); ?>
span.category-name|innerText = <?php echo htmlspecialchars($service['category_name'] ?? ''); ?>
a.service-link|href = <?php echo "/services-detail/" . htmlspecialchars($service['service_slug'] ?? ''); ?>
h4.service-title|innerText = <?php echo htmlspecialchars($service['service_title'] ?? ''); ?>
img.author-image|src = <?php echo "/templates/" . htmlspecialchars($service['author_image'] ?? ''); ?>
p.author-name|innerText = <?php echo htmlspecialchars($service['author_name'] ?? ''); ?>

.swiper-wrapper .col-lg-4|after = <?php endforeach; ?>
