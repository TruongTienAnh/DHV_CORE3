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


// Điền tên danh mục hiện tại với style và class
h4.category-title|innerText = <?php echo htmlspecialchars($this->current_category['name'] ?? 'Danh mục không xác định'); ?>

// Tạo danh sách tài liệu
div.document-list|innerText = <?php
    if (!empty($this->documents)) {
        foreach ($this->documents as $doc) {
            if (!empty($doc['id'])) {
                echo '<a href="/library-detail/' . htmlspecialchars($doc['slug']) . '" class="legal-item d-block text-decoration-none text-dark">'
                    . $doc['title'] .
                    '</a>';
            } else {
                echo '<p class="text-danger">Tài liệu không hợp lệ (thiếu ID).</p>';
            }
        }
    } else {
        echo '<p>Không có tài liệu nào.</p>';
    }
?>

// Tạo phân trang
ul.my-pagination|innerText = <?php
    $html = '';
    for ($i = 1; $i <= $this->total_pages; $i++) {
        $activeClass = ($i === $this->current_page) ? 'active' : '';
        $html .= '<li class="my-page-item ' . $activeClass . '"><a class="my-page-link" href="?page=' . $i . '">' . $i . '</a></li>';
    }
    echo $html;
?>

// Thiết lập action và giá trị cho form tìm kiếm
#searchForm|action = <?php echo "/library" . (!empty($this->current_category['slug']) ? '/' . htmlspecialchars($this->current_category['slug']) : ''); ?>
#searchInput|value = <?php echo htmlspecialchars($this->search_query ?? ''); ?>
#searchInput|placeholder = <?php echo htmlspecialchars(_('Tìm kiếm bài viết...')); ?>

// Tạo danh sách danh mục ở sidebar
ul.category-list|innerText = <?php
    $html = '';
    if (!empty($this->categories)) {
        foreach ($this->categories as $cat) {
            $activeClass = (($this->current_category['id'] ?? 0) == $cat['id']) ? 'active' : '';
            $html .= '<li><a href="/library/' . htmlspecialchars($cat['slug']) . '" class="' . $activeClass . '">' . htmlspecialchars($cat['name']) . '<span>' . htmlspecialchars($cat['total']) . '</span></a></li>';
        }
    }
    echo $html;
?>

#dummy-variable-for-vtpl-fix = $this->dummy