<?php
if (!defined('ECLO'))
    die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');
$view = $app->getValueData('view');

// Handler cho chi tiết dịch vụ
$serviceDetailHandler = function ($vars) use ($app, $jatbi, $setting, $view) {
    $slug = $vars['slug'] ?? null;

    // Phân trang
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 4; // 4 item trên mỗi trang
    $offset = ($page - 1) * $limit;

    if (!$slug) {
        http_response_code(400);
        echo $jatbi->lang("Thiếu slug dịch vụ.");
        return;
    }

    // Truy vấn theo slug
    $services = $app->select("services", [
        "[>]services_detail" => ["id" => "service_id"],
        "[>]categories" => ["category_id" => "id"],
        "[>]author_boxes" => ["services_detail.author_box_id" => "id"]
    ], [
        "services.id",
        "services.image",
        "services.title(service_title)",
        "services.slug",
        "services.type",
        "services_detail.id(service_detail_id)", 
        "services_detail.title",
        "services_detail.description_title",
        "services_detail.rate",
        "services_detail.min_price",
        "services_detail.max_price",
        "services_detail.original_min_price",
        "services_detail.original_max_price",
        "services_detail.discount",
        "services_detail.object",
        "services_detail.content",
        "services_detail.author_box_id",
        "services_detail.service_id",
        "categories.name(category_name)",
        "author_boxes.name(author_name)",
        "author_boxes.image_url(author_image)",
        "author_boxes.content(author_content)"
    ], [
        "services.slug" => $slug,
        "LIMIT" => 1
    ]);

    // Tổng số tài liệu để tính tổng số trang
    $totalDocuments = $app->count("services");
    $totalPages = ceil($totalDocuments / $limit);

    if (!$services) {
        http_response_code(404);
        echo $jatbi->lang("Dịch vụ không tồn tại.");
        return;
    }

    $service_detail = $services[0];

    // Kiểm tra xem dịch vụ có chi tiết hay không
    if (empty($service_detail['service_detail_id'])) {
        http_response_code(404);
        echo $jatbi->lang("Dịch vụ này không có chi tiết dịch vụ.");
        return;
    }

    // Xử lý đường dẫn hình ảnh
    $image_path = $service_detail['image'] ?? '';
    $relative_image_path = '';
    if (!empty($image_path)) {
        $template_pos = strpos($image_path, '/templates/');
        if ($template_pos !== false) {
            $relative_image_path = substr($image_path, $template_pos);
            $relative_image_path = str_replace('\\', '/', $relative_image_path);
        } else {
            $relative_image_path = str_replace('\\', '/', $image_path);
        }
    }
    $service_detail['image'] = $relative_image_path;

    // Xử lý đánh giá sao
    $rate = (int) ($service_detail['rate'] ?? 0);
    $service_detail['stars'] = str_repeat('<i class="fas fa-star star text-2xl"></i>', $rate);

    // Lấy danh sách danh mục để hiển thị sidebar (nếu cần)
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

    // Lấy danh sách dịch vụ cho dropdown trong modal
    $service_packages = $app->select("services", ["id", "title", "type"], [
        "status" => "A",
        "ORDER" => ["id" => "ASC"]
    ]);

    // Xác định service_id dựa trên slug
    $selected_service_id = $service_detail['id'] ?? null;

    // Truyền title cho modal
    $view->title = $jatbi->lang("Đăng ký nhận tư vấn");
    $view->admin_path = explode("/", $setting['backend'])[1];

    // Gán dữ liệu cho view
    $view->service_detail = $service_detail;
    $view->categories = $categories ?? [];
    $view->setting = $setting;
    $view->current_page = $page;
    $view->total_pages = $totalPages;
    $view->service_packages = $service_packages;
    $view->selected_service_id = $selected_service_id;
    $view->header = $app->component('header_frontend');
    $view->footer = $app->component('footer_frontend');
    $view->jatbi = $jatbi; // Thêm $jatbi để sử dụng lang()

    // Render template
    $view->render('services-detail.html');
};

// Đăng ký route cho chi tiết dịch vụ
$app->router("/services-detail/{slug}", 'GET', $serviceDetailHandler);