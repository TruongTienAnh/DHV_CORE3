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


// Điền tiêu đề tài liệu
.post-header-box h3|innerText = <?php echo '<span class="fw-bold mb-4" style="color: black; font-weight: bold;">' . htmlspecialchars($this->document['title'] ?? '') . '</span>'; ?>

// Điền ngày tạo
.post-meta em|innerText = <?php echo htmlspecialchars(substr($this->document['created_at'] ?? '', 0, 10)); ?>

// Điền hình ảnh tài liệu
.blog-side-about img|src = <?php echo htmlspecialchars($this->setting['template'] ?? 'templates') . '/' . htmlspecialchars($this->document['img_url'] ?? ''); ?>
.blog-side-about img|style = <?php echo "width: 100%; height: 100%;"; ?>

// Điền nút tải tài liệu
.btn-download-doc|data-url = <?php echo htmlspecialchars("library-add/" . ($this->document['slug'] ?? '')); ?>
.btn-download-doc|innerText = <?php echo 'Tải tài liệu miễn phí'; ?>
.btn-download-doc|class = <?php echo "btn btn-download-doc px-5 py-3 fw-bold"; ?>
.btn-download-doc|data-action = <?php echo "modal"; ?>
.btn-download-doc|data-bs-toggle = <?php echo "modal"; ?>
.btn-download-doc|data-bs-target = <?php echo ".modal-load"; ?>

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