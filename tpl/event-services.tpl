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


// Cập nhật form tìm kiếm
form.dream-course-search|action = <?php echo htmlspecialchars('/event-services'); ?>
input[name="search"]|value = <?php echo htmlspecialchars($this->search_query ?? ''); ?>
input[name="search"]|placeholder = <?php echo htmlspecialchars(_('Search Your Course')); ?>
select[name="category"]|innerText = <?php
    $html = '<option value="" data-display="Danh mục">Danh mục</option>';
    foreach ($this->categories ?? [] as $cat) {
        $selected = ($this->category_filter == $cat['id']) ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($cat['id']) . '"' . $selected . '>' . htmlspecialchars($cat['name']) . '</option>';
    }
    echo $html;
?>

// Hiển thị danh sách dịch vụ
#serviceList|innerText = <?php
    if (!empty($this->services_data)) {
        foreach ($this->services_data as $data) {
            echo '<div class="col-lg-4 col-md-6 col-sm-8">'
                . '<div class="card">'
                . '<div class="single-courses">'
                . '<div class="courses-thumb">'
                . '<img src="' . htmlspecialchars($this->setting['template'] . '/' . $data['image']) . '" alt="courses">'
                . '<div class="corses-thumb-title">'
                . '<span>' . htmlspecialchars($data['category_name']) . '</span>'
                . '</div></div></div>'
                . '<div class="card-body">'
                . '<h5>' . htmlspecialchars($data['title']) . '</h5>'
                . '<ol>';
            foreach ($data['description_items'] as $item) {
                if (!empty($item)) {
                    echo '<li>' . htmlspecialchars($item) . '</li>';
                }
            }
            echo '</ol>'
                . '<a href="/services-detail/' . htmlspecialchars($data['slug']) . '" class="btn">XEM THÊM</a>'
                . '</div></div></div>';
        }
    } else {
        echo '<div class="col-12 text-center"><p>' . htmlspecialchars($this->content ?? 'Không có dịch vụ nào để hiển thị.') . '</p></div>';
    }
?>

// Hiển thị phân trang
ul.my-pagination|innerText = <?php
    $html = '';
    for ($i = 1; $i <= $this->total_pages; $i++) {
        $activeClass = ($i === $this->current_page) ? 'active' : '';
        $html .= '<li class="my-page-item ' . $activeClass . '"><a class="my-page-link" href="?page=' . $i . '">' . $i . '</a></li>';
    }
    echo $html;
?>

// Biến giả để khắc phục lỗi Vtpl
#dummy-variable-for-vtpl-fix = $this->dummy