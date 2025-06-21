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
          . '<li class="breadcrumb-item active" aria-current="page">' . htmlspecialchars($this->title ?? "Dự án") . '</li>';
    echo $html;
?>

// Hiển thị filter và form tìm kiếm
#filter-section = <?php
    $html = '<div class="course-filter d-block align-items-center d-sm-flex">'
          . '<form action="/projects" method="GET">'
          . '<select name="industry" onchange="this.form.submit()">'
          . '<option value="">Tất cả ngành</option>';
    foreach ($this->industries as $industry) {
        $selected = $this->industry_filter === $industry ? ' selected' : '';
        $html .= '<option value="' . htmlspecialchars($industry) . '"' . $selected . '>' . htmlspecialchars($industry) . '</option>';
    }
    $html .= '</select></form>'
          . '<form action="/projects" method="GET">'
          . '<input type="hidden" name="industry" value="' . htmlspecialchars($this->industry_filter) . '">'
          . '<div class="input-box">'
          . '<i class="fal fa-search"></i>'
          . '<input type="text" placeholder="Tìm kiếm" name="search" value="' . htmlspecialchars($this->search_query) . '">'
          . '</div></form>'
          . '</div>';
    echo $html;
?>

// Hiển thị danh sách dự án
#project-list = <?php
    if (empty($this->projects)) {
        $message = 'Không tìm thấy dự án nào';
        $message .= !empty($this->search_query) ? ' phù hợp với từ khóa "' . htmlspecialchars($this->search_query) . '"' : '';
        $message .= !empty($this->industry_filter) ? ' trong ngành "' . htmlspecialchars($this->industry_filter) . '"' : '';
        echo '<div class="col-lg-12 text-center"><p>' . $message . '</p></div>';
    } else {
        $html = '';
        foreach ($this->projects as $project) {
            $html .= '<div class="col-lg-12">'
                   . '<div class="single-course-list white-bg mt-30 d-flex align-items-center flex-wrap">'
                   . '<div class="course-list-thumb">'
                   . '<img src="' . htmlspecialchars($this->setting['template'] . '/uploads/projects/' . $project['image_url']) . '" alt="Ảnh dự án ' . htmlspecialchars($project['title']) . '">'
                   . '</div>'
                   . '<div class="courses-content">'
                   . '<a href="/project-detail/' . htmlspecialchars($project['slug']) . '">'
                   . '<h4 class="title">' . htmlspecialchars($project['title']) . ' ' . (!empty($project['industry']) ? '(ngành ' . htmlspecialchars($project['industry']) . ')' : '') . '</h4>'
                   . '</a>'
                   . '<p>' . htmlspecialchars($project['excerpt']) . '</p>'
                   . '<div class="courses-info d-flex justify-content-between">'
                   . '<div class="item"><p>' . htmlspecialchars($project['client_name']) . '</p></div>'
                   . '</div>'
                   . '<ul><li><i class="fal fa-calendar-alt"></i> Tháng ' . htmlspecialchars($project['start_date']) . ' - ' . htmlspecialchars($project['end_date']) . '</li></ul>'
                   . '</div>'
                   . '</div>'
                   . '</div>';
        }
        echo $html;
    }
?>

// Hiển thị phân trang
#pagination = <?php
    if ($this->total_pages > 1) {
        $html = '<div class="pagination-item d-flex justify-content-center mt-50">'
              . '<nav aria-label="Page navigation example">'
              . '<ul class="pagination">';
        if ($this->current_page > 1) {
            $html .= '<li class="page-item">'
                   . '<a class="page-link" href="/projects?page=' . ($this->current_page - 1) . '&search=' . urlencode($this->search_query) . '&industry=' . urlencode($this->industry_filter) . '" aria-label="Previous">'
                   . '<span aria-hidden="true"><i class="fal fa-angle-double-left"></i></span>'
                   . '</a>'
                   . '</li>';
        }
        for ($i = max(1, $this->current_page - 2); $i <= min($this->total_pages, $this->current_page + 2); $i++) {
            $html .= '<li class="page-item">'
                   . '<a class="page-link ' . ($i === $this->current_page ? 'active' : '') . '" href="/projects?page=' . $i . '&search=' . urlencode($this->search_query) . '&industry=' . urlencode($this->industry_filter) . '">' . $i . '</a>'
                   . '</li>';
        }
        if ($this->current_page < $this->total_pages - 2) {
            $html .= '<li class="page-item"><a class="page-link" href="#">...</a></li>'
                   . '<li class="page-item"><a class="page-link" href="/projects?page=' . $this->total_pages . '&search=' . urlencode($this->search_query) . '&industry=' . urlencode($this->industry_filter) . '">' . $this->total_pages . '</a></li>';
        }
        if ($this->current_page < $this->total_pages) {
            $html .= '<li class="page-item">'
                   . '<a class="page-link" href="/projects?page=' . ($this->current_page + 1) . '&search=' . urlencode($this->search_query) . '&industry=' . urlencode($this->industry_filter) . '" aria-label="Next">'
                   . '<span aria-hidden="true"><i class="fal fa-angle-double-right"></i></span>'
                   . '</a>'
                   . '</li>';
        }
        $html .= '</ul></nav></div>';
        echo $html;
    }
?>

// Biến giả để khắc phục lỗi Vtpl
#dummy-variable-for-vtpl-fix = $this->dummy