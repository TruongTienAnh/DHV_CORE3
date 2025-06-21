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
#page-title-core = <?php echo htmlspecialchars('CORE'); ?>
#breadcrumb = <?php
    $html = '<li class="breadcrumb-item"><a href="/">Trang chủ</a></li>'
          . '<li class="breadcrumb-item"><a href="/news">Tin tức</a></li>'
          . '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($this->title ?? "Chi tiết tin tức") . '</li>';
    echo $html;
?>

// Hiển thị thông tin bài viết
#post-details-top = <?php
    $html = '<span>' . htmlspecialchars($this->post['category']['name']) . '</span>'
          . '<h2 class="title">' . htmlspecialchars($this->post['title']) . '</h2>'
          . '<ul>'
          . '<li><i class="fal fa-eye"></i> ' . htmlspecialchars($this->post['views']) . ' Views</li>'
          . '<li><i class="fal fa-calendar-alt"></i> ' . htmlspecialchars($this->post['published_at']) . '</li>'
          . '</ul>'
          . '<p class="mt-m2">' . htmlspecialchars($this->post['excerpt']) . '</p>';
    echo $html;
?>

// Hiển thị hình ảnh và nội dung chi tiết
#post-image = <?php
    $html = '<img src="' . htmlspecialchars($this->setting['template'] . '/assets-frontend/images/' . $this->post['image_url']) . '" alt="blog-details">'
          . '<div class="blog-details-text mt-30">' . $this->post['content'] . '</div>';
    echo $html;
?>

// Hiển thị form tìm kiếm
#search-form = <?php
    $html = '<form action="/news" method="GET">'
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