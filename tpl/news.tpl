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

// Hiển thị tiêu đề và breadcrumb
#page-title-core|innerText = <?php echo htmlspecialchars('CORE'); ?>
#breadcrumb = <?php
    $html = '<li class="breadcrumb-item"><a href="/">Trang chủ</a></li>'
          . '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($this->title) . '</li>';
    echo $html;
?>

// Hiển thị danh mục kết quả
#category-result = <?php
    if (!empty($this->category_slug)) {
        $current_category = array_filter($this->all_categories ?? [], fn($cat) => $cat['slug'] === $this->category_slug);
        $current_category = reset($current_category);
        if ($current_category) {
            echo '<h4>Danh mục: "' . htmlspecialchars($current_category['name']) . '"</h4>';
        }
    }
?>

// Hiển thị kết quả tìm kiếm
#search-result = <?php
    if (!empty($this->search_query)) {
        echo '<h4>Kết quả tìm kiếm cho: "' . htmlspecialchars($this->search_query) . '"</h4>';
    }
?>

// Hiển thị thông báo không có kết quả
#no-results= <?php
    if (empty($this->category_posts) && !empty($this->search_query)) {
        echo '<p>Không tìm thấy bài viết nào phù hợp với từ khóa "' . htmlspecialchars($this->search_query) . '".</p>';
    }
?>

// Hiển thị danh sách bài viết
#post-list = <?php
    if (!empty($this->category_posts) || empty($this->search_query)) {
        $html = '';
        foreach ($this->category_posts as $data) {
            $html .= '<div class="col-lg-6 col-md-6 col-sm-9">'
                   . '<div class="single-blog-grid mt-40">'
                   . '<div class="blog-thumb">'
                   . '<img src="' . htmlspecialchars($this->setting['template'] . '/uploads/news/' . $data['post']['image_url']) . '" alt="blog">'
                   . '</div>'
                   . '<div class="blog-content">'
                   . '<span>' . htmlspecialchars($data['category']['name']) . '</span>'
                   . '<a href="/news-detail/' . htmlspecialchars($data['post']['slug']) . '"><h4 class="title">' . htmlspecialchars($data['post']['title']) . '</h4></a>'
                   . '<ul>'
                   . '<li><i class="fal fa-eye"></i> ' . htmlspecialchars($data['post']['views']) . ' Lượt xem</li>'
                   . '<li><i class="fal fa-calendar-alt"></i> ' . htmlspecialchars($data['post']['published_at']) . '</li>'
                   . '</ul>'
                   . '<p>' . htmlspecialchars($data['post']['excerpt']) . '</p>'
                   . '</div>'
                   . '</div>'
                   . '</div>';
        }
        echo $html;
    }
?>

// Hiển thị phân trang
#pagination= <?php
    if ($this->total_pages > 1) {
        $html = '<ul class="my-pagination justify-content-center">';
        if ($this->current_page > 1) {
            $html .= '<li class="page-item">'
                   . '<a class="page-link" href="/news' . (!empty($this->category_slug) ? '/' . htmlspecialchars($this->category_slug) : '') . '?page=' . ($this->current_page - 1) . '&search=' . urlencode($this->search_query) . '" aria-label="Previous">'
                   . '<span aria-hidden="true"><i class="fal fa-angle-double-left"></i></span>'
                   . '</a>'
                   . '</li>';
        }
        for ($i = 1; $i <= $this->total_pages; $i++) {
            $html .= '<li class="page-item">'
                   . '<a class="page-link ' . ($i === $this->current_page ? 'active' : '') . '" href="/news' . (!empty($this->category_slug) ? '/' . htmlspecialchars($this->category_slug) : '') . '?page=' . $i . '&search=' . urlencode($this->search_query) . '">' . $i . '</a>'
                   . '</li>';
        }
        if ($this->current_page < $this->total_pages) {
            $html .= '<li class="page-item">'
                   . '<a class="page-link" href="/news' . (!empty($this->category_slug) ? '/' . htmlspecialchars($this->category_slug) : '') . '?page=' . ($this->current_page + 1) . '&search=' . urlencode($this->search_query) . '" aria-label="Next">'
                   . '<span aria-hidden="true"><i class="fal fa-angle-double-right"></i></span>'
                   . '</a>'
                   . '</li>';
        }
        $html .= '</ul>';
        echo $html;
    }
?>

// Hiển thị form tìm kiếm
#search-form = <?php
    $html = '<form action="/news' . (!empty($this->category_slug) ? '/' . htmlspecialchars($this->category_slug) : '') . '" method="GET">'
          . '<div class="input-box">'
          . '<input type="text" name="search" placeholder="Tìm kiếm bài viết..." value="' . htmlspecialchars($this->search_query) . '">'
          . '<button type="submit"><i class="fas fa-search"></i></button>'
          . '</div>'
          . '</form>';
    echo $html;
?>

// Hiển thị danh sách danh mục
#categories-list = <?php
    $html = '<ul>';
    foreach ($this->all_categories as $category) {
        $html .= '<li><a href="/news/' . htmlspecialchars($category['slug']) . '" class="' . ($this->category_slug === $category['slug'] ? 'active' : '') . '">' . htmlspecialchars($category['name']) . ' <span>' . htmlspecialchars($category['count']) . '</span></a></li>';
    }
    $html .= '</ul>';
    echo $html;
?>

// Biến giả để khắc phục lỗi Vtpl
#dummy-variable-for-vtpl-fix = $this->dummy