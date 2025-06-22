<?php
if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');
$view = $app->getValueData('view');

// Hàm chung để lấy dự án, phân trang và xử lý tìm kiếm
$projectHandler = function($vars) use ($app, $jatbi, $setting, $view) {
    // Tiêu đề trang
    $view->title = $jatbi->lang('Dự án');
    $view->admin_path = explode("/", $setting['backend'])[1];

    // Số dự án tối đa mỗi trang
    $perPage = 3;
    $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    // Lấy từ khóa tìm kiếm
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    $view->search_query = $searchQuery;

    // Lấy ngành từ filter
    $industryFilter = isset($_GET['industry']) ? trim($_GET['industry']) : '';
    $view->industry_filter = $industryFilter;

    // Lấy danh sách ngành cho filter
    $industries = $app->select("projects", ["industry"], [
        "status" => 'A',
        "deleted" => 0,
        "GROUP" => "industry",
        "ORDER" => ["industry" => "ASC"]
    ]);
    $view->industries = array_column($industries, 'industry');

    // Điều kiện truy vấn
    $conditions = [
        "status" => 'A',
        "deleted" => 0
    ];
    if (!empty($searchQuery)) {
        $conditions["OR"] = [
            "title[~]" => "%{$searchQuery}%",
            "client_name[~]" => "%{$searchQuery}%",
            "industry[~]" => "%{$searchQuery}%"
        ];
    }
    if (!empty($industryFilter)) {
        $conditions["industry"] = $industryFilter;
    }

    // Lấy tổng số dự án
    $totalProjects = $app->count("projects", $conditions);
    $totalPages = ceil($totalProjects / $perPage);

    // Lấy danh sách dự án với phân trang
    $offset = ($currentPage - 1) * $perPage;
    $projects = $app->select("projects", [
        "id",
        "title",
        "slug",
        "excerpt",
        "client_name",
        "description",
        "start_date",
        "end_date",
        "image_url",
        "industry"
    ], array_merge($conditions, [
        "ORDER" => ["start_date" => "DESC"],
        "LIMIT" => [$offset, $perPage]
    ]));

    // Chuẩn hóa dữ liệu dự án
    foreach ($projects as &$project) {
        $project['title'] = mb_strtoupper($project['title'] ?? '', 'UTF-8');
        $project['image_url'] = $project['image_url'] ?: '5.2.Dự án/2.jpg';
        $project['excerpt'] = substr(strip_tags($project['excerpt'] ?? ''), 0, 100) . '...';
        $project['start_date'] = date('m/Y', strtotime($project['start_date'] ?? 'now'));
        $project['end_date'] = date('m/Y', strtotime($project['end_date'] ?? 'now'));
        $project['client_name'] = $project['client_name'] ?? 'Chưa xác định';
        $project['industry'] = $project['industry'] ?? 'Chưa phân loại';
        $project['description'] = htmlspecialchars_decode($project['description'] ?? '', ENT_QUOTES);
    }
    unset($project);

    // Truyền dữ liệu vào view
    $view->projects = $projects;
    $view->current_page = $currentPage;
    $view->total_pages = $totalPages;
    $view->setting = $setting;
    $view->header = $app->component('header_frontend');
    $view->footer = $app->component('footer_frontend');
    $view->jatbi = $jatbi; // Thêm $jatbi để sử dụng lang()

    // Render template
    $view->render('project.html');
};

// Hàm xử lý chi tiết dự án
$projectDetailHandler = function($vars) use ($app, $jatbi, $setting, $view) {
    // Lấy slug từ URL
    $slug = $vars['slug'] ?? '';

    // Nếu không có slug, trả về 404
    if (empty($slug)) {
        http_response_code(404);
        echo "Không tìm thấy dự án.";
        return;
    }

    // Lấy thông tin dự án theo slug
    $project = $app->get("projects",[
        "[>]author_boxes" => ["author_box" => "id"]
    ], [
        "projects.id",
        "projects.title",
        "projects.slug",
        "projects.client_name",
        "projects.description",
        "projects.excerpt",
        "projects.start_date",
        "projects.end_date",
        "projects.image_url",
        "projects.industry",
        "author_boxes.name(author_name)",
        "author_boxes.image_url(author_image)",
    ], [
        "projects.slug" => $slug,
        "projects.status" => 'A',
        "projects.deleted" => 0
    ]);

    // Nếu không tìm thấy dự án, trả về 404
    if (!$project) {
        http_response_code(404);
        echo "Dự án không tồn tại.";
        return;
    }

    // Chuẩn hóa dữ liệu dự án
    $project['title'] = mb_strtoupper($project['title'] ?? '', 'UTF-8');
    $project['image_url'] = $project['image_url'] ?: '5.2.Dự án/2.jpg';
    $project['start_date'] = date('m/Y', strtotime($project['start_date'] ?? 'now'));
    $project['end_date'] = date('m/Y', strtotime($project['end_date'] ?? 'now'));
    $project['description'] = $project['description'] ?: 'Chưa có thông tin chi tiết.';
    $project['client_name'] = $project['client_name'] ?? 'Chưa xác định';
    $project['industry'] = $project['industry'] ?? 'Chưa phân loại';
    $project['description'] = htmlspecialchars_decode($project['description'] ?? '', ENT_QUOTES);
    $project['excerpt'] = $project['excerpt'] ?? '';
    $project['author_name'] = 'Chuyên gia. ' . $project['author_name'] ?? 'Chuyên gia. A';

    // Lấy danh sách hình ảnh của dự án
    $projectImages = $app->select("project_images", [
        "image_url",
        "caption"
    ], [
        "project_id" => $project['id'],
        "status" => 'A',
    ]);
    $view->admin_path = explode("/", $setting['backend'])[1];

    // Truyền dữ liệu vào view
    $view->title = $project['title'];
    $view->project = $project;
    $view->project_images = $projectImages;
    $view->setting = $setting;
    $view->header = $app->component('header_frontend');
    $view->footer = $app->component('footer_frontend');
    $view->jatbi = $jatbi; // Thêm $jatbi để sử dụng lang()

    // Render template
    $view->render('project-detail.html');
};

// Đăng ký route
$app->router("/projects", 'GET', $projectHandler);
$app->router("/project-detail/{slug}", 'GET', $projectDetailHandler);