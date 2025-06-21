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


// Hiển thị tiêu đề và nội dung
#course-title-content = <?php
    $html = '<span>' . htmlspecialchars($this->service_detail['type'] ?? '') . '</span>'
          . '<h2 class="title">' . htmlspecialchars($this->service_detail['service_title'] ?? '') . '</h2>'
          . '<p>' . htmlspecialchars($this->service_detail['description_title'] ?? '') . '</p>';
    echo trim($html);
?>

// Hiển thị đánh giá sao
#course-rating = <?php
    $html = '';
    if (($this->service_detail['rate'] ?? 0) == 5) {
        $html .= '<span>Bán chạy nhất</span>';
    }
    $rate = (int) ($this->service_detail['rate'] ?? 0);
    $html .= str_repeat('<i class="fas fa-star star text-2xl"></i>', $rate);
    echo trim($html);
?>

// Hiển thị đối tượng
#tab-object = <?php
    $html = '<div class="course-text"><p style="font-size: 20px; line-height: 1.6; color:black;">' 
          . htmlspecialchars($this->service_detail['object'] ?? '') 
          . '</p></div>';
    echo trim($html);
?>

// Hiển thị nội dung
#tab-content = <?php
    $html = '<div class="course-text"><div>' 
          . htmlspecialchars($this->service_detail['content'] ?? $this->jatbi->lang("Không có nội dung")) 
          . '</div></div>';
    echo trim($html);
?>

// Hiển thị hình ảnh
#course-image = <?php
    $image = !empty($this->service_detail['image']) ? htmlspecialchars($this->setting['template'] . '/' . $this->service_detail['image']) : '';
    $html = '<img src="' . $image . '" alt="course">';
    echo trim($html);
?>

// Hiển thị giá cả
#course-price = <?php
    $html = '<div class="title" style="font-size: 27px;">'
          . number_format($this->service_detail['min_price'] ?? 0, 0, ',', '.') . ' - '
          . number_format($this->service_detail['max_price'] ?? 0, 0, ',', '.') 
          . '</div>';
    echo trim($html);
?>

// Hiển thị nút và thông tin giảm giá
#course-buttons = <?php
    $html = '<div class="d-flex align-items-center justify-content-between">'
          . '<div style="text-decoration: line-through; color: #000;">'
          . number_format($this->service_detail['original_min_price'] ?? 0, 0, ',', '.') . ' - '
          . number_format($this->service_detail['original_max_price'] ?? 0, 0, ',', '.') 
          . '</div>'
          . '<div style="color: #000; font-weight: bold;">GIẢM ' . ($this->service_detail['discount'] ?? 0) . '%</div>'
          . '</div>'
          . '<a style="margin-top: 25px;" type="button" class="main-btn" data-action="modal" data-url="/register-post?serviceID=' . htmlspecialchars($this->service_detail['id'] ?? '') . '" data-bs-toggle="modal" data-bs-target=".modal-load"><i class="fal fa-headset"></i> TƯ VẤN NGAY</a>';
    echo trim($html);
?>

// Hiển thị avatar và tên tác giả
#author-avatar = <?php
    $image = !empty($this->service_detail['author_image']) ? htmlspecialchars($this->setting['template'] . '/' . $this->service_detail['author_image']) : '';
    $html = '<img src="' . $image . '" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover;" alt="Avatar">'
          . '<p class="fw-bold text-primary m-0" style="font-size: 20px; line-height: 1.4;">Chuyên gia. ' . htmlspecialchars($this->service_detail['author_name'] ?? '') . '</p>';
    echo trim($html);
?>

// Hiển thị nội dung tác giả
#author-content = <?php
    $html = '<div class="text-dark" style="font-size: 20px; line-height: 1.6;">' . htmlspecialchars($this->service_detail['author_content'] ?? '') . '</div>'
          . '<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center mt-4">'
          . '<a class="bg-primary text-white px-3 px-md-5 py-2 mt-2 mb-2 btn btn-lg me-md-3" style="font-size: 20px; white-space: nowrap;">9.000.000 - 12.000.000</a>'
          . '<a class="bg-danger text-white px-3 px-md-5 py-2 m-2 btn btn-lg" style="font-size: 20px; white-space: nowrap;" class="main-btn" data-action="modal" data-url="/register-post?serviceID=' . htmlspecialchars($this->service_detail['id'] ?? '') . '" data-bs-toggle="modal" data-bs-target=".modal-load">ĐĂNG KÝ NGAY</a>'
          . '</div>';
    echo trim($html);
?>

// Biến giả để khắc phục lỗi Vtpl
#dummy-variable-for-vtpl-fix = $this->dummy