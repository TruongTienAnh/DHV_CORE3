<?php
if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');
$view = $app->getValueData('view');

$servicesHandler = function($vars) use ($app, $jatbi, $setting, $view) {
    $type = $vars['type'] ?? '';
    $view->admin_path = explode("/", $setting['backend'])[1];

    if (empty($type)) {
        http_response_code(400);
        echo "Thiếu loại dịch vụ.";
        return;
    }

    // Xác định tiêu đề và template dựa trên loại dịch vụ
    if ($type === 'business') {
        $view->title = $jatbi->lang('Dịch vụ doanh nghiệp');
        $template = 'business-services.html';
        $serviceType = 'Doanh nghiệp';
    } elseif ($type === 'event') {
        $view->title = $jatbi->lang('Dịch vụ tổ chức sự kiện');
        $template = 'event-services.html';
        $serviceType = 'Tổ chức sự kiện';
    } else {
        http_response_code(400);
        echo "Loại dịch vụ không hợp lệ.";
        return;
    }

    // Phân trang
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 6;
    $offset = ($page - 1) * $limit;

    // Lấy từ khóa tìm kiếm
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    $view->search_query = $searchQuery;

    // Lấy danh mục từ filter
    $categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
    $view->category_filter = $categoryFilter;

    // Lấy danh sách danh mục cho filter
    $categories = $app->select("categories", [
        "[>]services" => ["id" => "category_id"]
    ], [
        "categories.id",
        "categories.name",
        "categories.slug",
        "total" => Medoo\Medoo::raw("COUNT(services.id)")
    ], [
        "GROUP" => [
            "categories.id",
            "categories.name",
            "categories.slug"
        ],
        "ORDER" => "categories.name"
    ]);
    $view->categories = $categories ?? [];

    // Điều kiện truy vấn
    $conditions = [
        "services.type" => $serviceType,
        "services.status" => 'A'
    ];
    if (!empty($searchQuery)) {
        $conditions["OR"] = [
            "services.title[~]" => "%{$searchQuery}%",
            "services.description[~]" => "%{$searchQuery}%",
            "categories.name[~]" => "%{$searchQuery}%"
        ];
    }
    if (!empty($categoryFilter)) {
        $conditions["services.category_id"] = $categoryFilter;
    }

    // Tổng số dịch vụ để tính tổng số trang
    $totalServices = $app->count("services", $conditions);
    $view->total_pages = ceil($totalServices / $limit);

    // Lấy danh sách dịch vụ giới hạn theo phân trang
    $services_data = [];
    try {
        $services = $app->select("services", [
            "[>]categories" => ["category_id" => "id"]
        ], [
            "services.id",
            "services.image",
            "services.title",
            "services.slug",
            "services.description",
            "services.type",
            "services.category_id",
            "categories.name(category_name)"
        ], array_merge($conditions, [
            "LIMIT" => [$offset, $limit],
            "ORDER" => ["services.id" => "ASC"]
        ]));

        if ($services === false || $services === null || empty($services)) {
            $view->content = $jatbi->lang("Không tìm thấy dịch vụ nào.");
        } else {
            foreach ($services as $service) {
                $description_items = explode("\n", $service['description'] ?? '');
                $formatted_items = array_map('trim', $description_items);

                $image_path = $service['image'] ?? '';
                $relative_image_path = '';
                if (!empty($image_path)) {
                    $template_pos = strpos($image_path, '/templates');
                    if ($template_pos !== false) {
                        $relative_image_path = substr($image_path, $template_pos);
                        $relative_image_path = str_replace('\\', '/', $relative_image_path);
                    } else {
                        $relative_image_path = str_replace('\\', '/', $image_path);
                    }
                }

                $services_data[] = [
                    'type' => $service['type'] ?? '',
                    'image' => $relative_image_path,
                    'category_name' => $service['category_name'] ?? '',
                    'title' => $service['title'] ?? '',
                    'slug' => $service['slug'] ?? '',
                    'description_items' => $formatted_items,
                    'id' => $service['id'] ?? ''
                ];
            }
        }
    } catch (Exception $e) {
        $view->content = $jatbi->lang("Lỗi: " . $e->getMessage());
    }

    // Gán dữ liệu cho view
    $view->services_data = $services_data;
    $view->current_page = $page;
    $view->setting = $setting;
    $view->header = $app->component('header_frontend');
    $view->footer = $app->component('footer_frontend');

    // Render template
    $view->render($template);
};

// Đăng ký 2 route riêng biệt, dùng chung handler
$app->router("/business-services", 'GET', function($vars) use ($servicesHandler) {
    $vars['type'] = 'business';
    $servicesHandler($vars);
});

$app->router("/event-services", 'GET', function($vars) use ($servicesHandler) {
    $vars['type'] = 'event';
    $servicesHandler($vars);
});