<?php
if (!defined('ECLO'))
    die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');
$common = $app->getValueData('common');
$view = $app->getValueData('view');

$app->group($setting['backend'], function ($app) use ($jatbi, $view, $setting) {
    $app->router("/editor", 'GET', function ($vars) use ($app) {
        echo $app->render('templates/admin/editor.html', $vars);
    })->setPermissions(['editor']);

    $app->router("/editor/{page}", 'GET', function ($vars) use ($app, $jatbi, $view, $setting) {
        $view->admin_path = explode("/", $setting['backend'])[1];
        switch ($vars['page']) {

            case 'about':
                $view->setting = $setting;

                $view->metadata = array(
                    'title' => "Giới thiệu CORE",
                );

                $view->render("about.html");
                break;
            case 'home':
                $services = $app->select("services", [
                    "[>]services_detail" => ["id" => "service_id"],
                    "[>]categories" => ["category_id" => "id"],
                    "[>]author_boxes" => ["services_detail.author_box_id" => "id"]
                ], [
                    "services.image(service_image)",
                    "services.title(service_title)",
                    "services.slug(service_slug)",
                    "categories.name(category_name)",
                    "author_boxes.name(author_name)",
                    "author_boxes.image_url(author_image)",
                    "services_detail.rate"
                ], [
                    "ORDER" => ["services_detail.rate" => "DESC"]
                ]);

                // Gán dữ liệu vào đối tượng $view
                $view->services = $services;

                // Render file view tương ứng
                $view->render("home.html");
                break;
            case 'contact':
                $view->title = "Liên hệ";
                $view->setting = $setting;

                // 3. Render file view
                $view->render("contact.html");
                break;
            case 'business-services':
                // Xác định tiêu đề và template dựa trên loại dịch vụ
                    $view->title = $jatbi->lang('Dịch vụ doanh nghiệp');
                    $serviceType = 'Doanh nghiệp';
                

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
                $view->render("business-services.html");
                break;
            case 'event-services':

                // Xác định tiêu đề và template dựa trên loại dịch vụ
                    $view->title = $jatbi->lang('Dịch vụ tổ chức sự kiện');
                    $serviceType = 'Tổ chức sự kiện';
                

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
                $view->render("event-services.html");
                break;
            case 'news':
                $view->title = $jatbi->lang('Tin tức');

                // Số bài viết tối đa mỗi trang
                $perPage = 4;
                $currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

                // Lấy từ khóa tìm kiếm
                $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
                $view->search_query = $searchQuery;

                // Lấy slug danh mục từ URL (nếu có)
                $categorySlug = $vars['slug'] ?? '';
                $view->category_slug = $categorySlug;

                // Nếu không có slug, hiển thị tất cả danh mục; nếu có slug, chỉ lấy danh mục tương ứng
                $categoryId = null;
                if (!empty($categorySlug)) {
                    $category = $app->get("categories_news", ["id"], ["slug" => $categorySlug, "deleted" => 0, "status" => 'A']);
                    if (!$category) {
                        http_response_code(404);
                        echo "Danh mục không tồn tại.";
                        return;
                    }
                    $categoryId = $category['id'];
                }

                // Truy vấn tất cả danh mục, chỉ lấy danh mục có deleted = 0 và status = 'A', sắp xếp theo tổng views giảm dần
                $all_categories = $app->select("categories_news", [
                    "[>]news" => ["id" => "category_id"]
                ], [
                    "categories_news.id",
                    "categories_news.name",
                    "categories_news.slug",
                    "total_views" => $app->raw("SUM(news.views)"),
                    "count" => $app->raw("COUNT(news.id)")
                ], [
                    "categories_news.deleted" => 0,
                    "categories_news.status" => 'A',
                    "news.status" => 'A',
                    "news.deleted" => 0,
                    "GROUP" => "categories_news.id",
                    "ORDER" => ["total_views" => "DESC"]
                ]);

                // Chuẩn hóa dữ liệu danh mục
                foreach ($all_categories as &$category) {
                    $category['name'] = mb_strtoupper($category['name'], 'UTF-8');
                    $category['count'] = (int)$category['count'];
                }
                unset($category); // Hủy tham chiếu
                $view->all_categories = $all_categories;

                // Kiểm tra nếu không có danh mục
                if (empty($all_categories)) {
                    $view->category_posts = [];
                    $view->total_pages = 0;
                    $view->render('news.html');
                    return;
                }

                // Xây dựng điều kiện truy vấn
                $conditions = [
                    "news.status" => 'A',
                    "news.deleted" => 0,
                    "category_id" => $app->select("categories_news", "id", [
                        "deleted" => 0,
                        "status" => 'A'
                    ])
                ];
                if ($categoryId !== null) {
                    $conditions["category_id"] = $categoryId;
                }
                if (!empty($searchQuery)) {
                    $conditions["OR"] = [
                        "news.title[~]" => "%{$searchQuery}%",
                        "news.content[~]" => "%{$searchQuery}%"
                    ];
                }

                // Tính tổng số bài viết
                $totalPosts = $app->count("news", "id", [
                    "AND" => $conditions
                ]);
                $totalPages = ceil($totalPosts / $perPage);

                // Lấy bài viết cho trang hiện tại
                $posts = $app->select("news", [
                    "[>]categories_news" => ["category_id" => "id"]
                ], [
                    "news.id",
                    "news.title",
                    "news.slug",
                    "news.excerpt",
                    "news.content",
                    "news.image_url",
                    "news.views",
                    "news.published_at",
                    "news.category_id",
                    "categories_news.name(category_name)",
                    "categories_news.slug(category_slug)"
                ], [
                    "AND" => $conditions,
                    "LIMIT" => [($currentPage - 1) * $perPage, $perPage],
                    "ORDER" => ["news.views" => "DESC"]
                ]);

                // Chuẩn hóa dữ liệu bài viết
                $category_posts = [];
                foreach ($posts as $post) {
                    $category_posts[] = [
                        'category' => [
                            'id' => $post['category_id'],
                            'name' => mb_strtoupper($post['category_name'], 'UTF-8'),
                            'slug' => $post['category_slug']
                        ],
                        'post' => [
                            'id' => $post['id'],
                            'title' => mb_strtoupper($post['title'], 'UTF-8'),
                            'slug' => $post['slug'],
                            'excerpt' => isset($post['excerpt']) && $post['excerpt'] 
                                ? $post['excerpt'] 
                                : (isset($post['excerpt']) 
                                    ? substr(strip_tags((string)$post['excerpt']), 0, 150) . '...' 
                                    : ''),
                            'content' => substr(strip_tags($post['content']), 0, 150) . '...',
                            'image_url' => $post['image_url'] ?: 'blog-grid-1.jpg',
                            'views' => (int)($post['views'] ?? 0),
                            'published_at' => $post['published_at'] ? date('d/m/Y', strtotime($post['published_at'])) : ''
                        ]
                    ];
                }

                // Truyền dữ liệu vào view
                $view->category_posts = $category_posts;
                $view->current_page = $currentPage;
                $view->total_pages = $totalPages;
                $view->setting = $setting;
                $view->jatbi = $jatbi; // Thêm $jatbi để sử dụng lang()
                $view->render("news.html");
                break;
            case 'projects':
                $view->title = $jatbi->lang('Dự án');

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
                $view->jatbi = $jatbi; // Thêm $jatbi để sử dụng lang()
                $view->render('project.html');
                break;
            case 'library':
                $slug = $vars['slug'] ?? '';
                $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

                // Lấy thông tin danh mục hiện tại
                if (empty($slug)) {
                    $category = $app->get("categories", "*", ["ORDER" => ["id" => "ASC"]]);
                } else {
                    $category = $app->get("categories", "*", ["slug" => $slug]);
                }

                if (!$category) {
                    echo "404 - Danh mục không tồn tại.";
                    return;
                }
                
                // Phân trang
                $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
                $limit = 16;
                $offset = ($page - 1) * $limit;

                // Điều kiện lọc
                $conditions = ["id_category" => $category['id']];
                if ($searchQuery !== '') {
                    $conditions["OR"] = [
                        "title[~]" => "%{$searchQuery}%",
                        "description[~]" => "%{$searchQuery}%"
                    ];
                }
                
                // Truy vấn CSDL
                $totalDocuments = $app->count("resources", $conditions);
                $view->total_pages = ceil($totalDocuments / $limit);
                $view->documents = $app->select("resources", "*", ["AND" => $conditions, "LIMIT" => [$offset, $limit]]);
                
                // Lấy danh sách tất cả danh mục cho sidebar
                $view->categories = $app->select("categories", [
                    "[>]resources" => ["id" => "id_category"]
                ], [
                    "categories.id", "categories.name", "categories.slug",
                    "total" => $app->raw("COUNT(resources.id)")
                ], [
                    "GROUP" => ["categories.id", "categories.name", "categories.slug"],
                    "ORDER" => "categories.name"
                ]);
                $view->admin_path = explode("/", $setting['backend'])[1];

                // Gán các biến cho view
                $view->current_category = $category;
                $view->current_page = $page;
                $view->search_query = $searchQuery;
                $view->setting = $setting; // Thêm setting để dùng trong template
    

                $view->render("library.html");
                break;
            case 'consultation':
                // 1. Lấy danh sách dịch vụ từ CSDL
                $services = $app->select("services", ["id", "title", "type"], [
                    "status" => "A",
                    "ORDER" => ["id" => "ASC"]
                ]);

                // 2. Gán dữ liệu vào đối tượng $view để file .tpl có thể sử dụng
                $view->service_packages = $services;
                $view->render("consultation.html");
                break;
            default:
                echo $app->render('templates/error.html', $vars);
                break;
        }
    })->setPermissions(['editor']);

    $app->router("/editor/save-page", 'POST', function ($vars) use ($jatbi, $view, $setting) {
        define('MAX_FILE_LIMIT', $setting['maxUploadSize']); //5 Megabytes max html file size
        define('ALLOW_PHP', true); //check if saved html contains php tag and don't save if not allowed
        define('ALLOWED_OEMBED_DOMAINS', [
            'https://www.youtube.com/',
            'https://www.vimeo.com/',
            'https://www.x.com/',
            'https://x.com/',
            'https://publish.twitter.com/',
            'https://www.twitter.com/',
            'https://www.reddit.com/',
        ]); //load urls only from allowed websites for oembed

        function sanitizeFileName($file, $allowedExtension = 'html')
        {
            $basename = basename($file);
            $disallow = ['.htaccess', 'passwd'];
            if (in_array($basename, $disallow)) {
                showError('Filename not allowed!');
                return '';
            }

            //sanitize, remove double dot .. and remove get parameters if any
            $file = preg_replace('@\?.*$@', '', preg_replace('@\.{2,}@', '', preg_replace('@[^\/\\a-zA-Z0-9\-\._]@', '', $file)));

            if ($file) {
                $file = $_SERVER['DOCUMENT_ROOT'] . $file;
            } else {
                return '';
            }

            //allow only .html extension
            if ($allowedExtension) {
                $file = preg_replace('/\.[^.]+$/', '', $file) . ".$allowedExtension";
            }
            return $file;
        }

        function showError($error)
        {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            die($error);
        }

        function validOembedUrl($url)
        {
            foreach (ALLOWED_OEMBED_DOMAINS as $domain) {
                if (strpos($url, $domain) === 0) {
                    return true;
                }
            }

            return false;
        }

        $html   = '';
        $file   = '';
        $action = '';

        if (isset($_POST['startTemplateUrl']) && !empty($_POST['startTemplateUrl'])) {
            $startTemplateUrl = sanitizeFileName('VvvebJs' . $_POST['startTemplateUrl']);
            $html = '';
            if ($startTemplateUrl) {
                $html = file_get_contents($startTemplateUrl);
            }
        } else if (isset($_POST['html'])) {
            $html = substr($_POST['html'], 0, MAX_FILE_LIMIT);
            if (!ALLOW_PHP) {
                //if (strpos($html, '<?php') !== false) {
                if (preg_match('@<\?php|<\? |<\?=|<\s*script\s*language\s*=\s*"\s*php\s*"\s*>@', $html)) {
                    showError('PHP not allowed!');
                }
            }
        }

        if (isset($_POST['file'])) {
            $file = sanitizeFileName($_POST['file']);
        }

        if (isset($_GET['action'])) {
            $action = htmlspecialchars(strip_tags($_GET['action']));
        }

        if ($action) {
            //file manager actions, delete and rename
            switch ($action) {
                case 'rename':
                    $newfile = sanitizeFileName($_POST['newfile']);
                    if ($file && $newfile) {
                        if ($_POST['duplicate'] == 'true') {
                            $html = file_get_contents($file);
                            file_put_contents($newfile, $html);
                            echo "File '$newfile' duplicated from '$file'";
                        } else if (rename($file, $newfile)) {
                            echo "File '$file' renamed to '$newfile'";
                        } else {
                            showError("Error renaming file '$file' renamed to '$newfile'");
                        }
                    }
                    break;
                case 'delete':
                    if ($file) {
                        if (unlink($file)) {
                            echo "File '$file' deleted";
                        } else {
                            showError("Error deleting file '$file'");
                        }
                    }
                    break;
                case 'saveReusable':
                    //block or section
                    $type = $_POST['type'] ?? false;
                    $name = $_POST['name'] ?? false;
                    $html = $_POST['html'] ?? false;

                    if ($type && $name && $html) {

                        $file = sanitizeFileName("$type/$name");
                        if ($file) {
                            $dir = dirname($file);
                            if (!is_dir($dir)) {
                                echo "$dir folder does not exist\n";
                                if (mkdir($dir, 0777, true)) {
                                    echo "$dir folder was created\n";
                                } else {
                                    showError("Error creating folder '$dir'\n");
                                }
                            }
                            if (file_put_contents($file, $html)) {
                                echo "File saved '$file'";
                            } else {
                                showError("Error saving file '$file'\nPossible causes are missing write permission or incorrect file path!");
                            }
                        } else {
                            showError('Invalid filename!');
                        }
                    } else {
                        showError("Missing reusable element data!\n");
                    }
                    break;
                case 'oembedProxy':
                    $url = $_GET['url'] ?? '';
                    if (validOembedUrl($url)) {
                        $options = array(
                            'http' => array(
                                'method' => "GET",
                                'header' => 'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n"
                            )
                        );
                        $context = stream_context_create($options);
                        header('Content-Type: application/json');
                        echo file_get_contents($url, false, $context);
                    } else {
                        showError('Invalid url!');
                    }
                    break;
                default:
                    showError("Invalid action '$action'!");
            }
        } else {
            //save page
            if ($html) {
                if ($file) {
                    $dir = dirname($file);
                    if (!is_dir($dir)) {
                        echo "$dir folder does not exist\n";
                        if (!mkdir($dir, 0777, true)) {
                            showError("Error creating folder '$dir'\n");
                        } else {
                            echo "$dir folder was created\n";
                        }
                    }

                    // Dò encoding thủ công
                    $encodings = ['UTF-8', 'ISO-8859-1', 'WINDOWS-1252', 'ASCII', 'GB2312', 'Big5'];
                    $html_utf8 = '';

                    foreach ($encodings as $enc) {
                        $converted = @iconv($enc, 'UTF-8//IGNORE', $html);
                        if ($converted && preg_match('//u', $converted)) {
                            $html_utf8 = $converted;
                            break;
                        }
                    }

                    // Nếu không dò được thì fallback
                    if (!$html_utf8) {
                        $encoding = mb_detect_encoding($html, mb_detect_order(), true);
                        $html_utf8 = mb_convert_encoding($html, 'UTF-8', $encoding ?: 'auto');
                    }

                    // Đảm bảo có meta charset UTF-8
                    $html_utf8 = preg_replace('/<meta[^>]*charset=[^>]*>/i', '', $html_utf8); // Xóa meta charset cũ
                    $html_utf8 = preg_replace('/<head[^>]*>/i', '$0<meta charset="UTF-8">', $html_utf8, 1); // Thêm lại

                    // Ghi file
                    if (file_put_contents($file, $html_utf8)) {
                        echo "File saved '$file'";
                    } else {
                        showError("Error saving file '$file'\nPossible causes are missing write permission or incorrect file path!");
                    }
                } else {
                    showError('Filename is empty!');
                }
            } else {
                showError('HTML content is empty!');
            }
        }
    })->setPermissions(['editor']);

    $app->router("/blog/upload-image", 'POST', function ($vars) use ($app, $jatbi, $setting) {

        if (!isset($_FILES['image']) && empty($_FILES['image'])) {
            $error = ['status' => 'error', 'content' => $jatbi->lang('Không có hình ảnh')];
        }
        if ($_FILES['image']['error'] == UPLOAD_ERR_INI_SIZE) {
            $error = ['status' => 'error', 'content' => $jatbi->lang('Ảnh vượt quá 5MB')];
        }

        if (empty($error)) {
            $imageUrl = $_FILES['image'];
            $blogId = isset($_POST['blog_id'])  ? $_POST['blog_id'] : 0;
            $handle = $app->upload($imageUrl);

            $path_upload = dirname(dirname(dirname(__DIR__))) . $setting['mediaUrl'];

            if (!is_dir($path_upload)) {
                mkdir($path_upload, 0755, true);
            }

            $newimages = $jatbi->active();
            if ($handle->uploaded) {
                $handle->allowed = array('image/*');
                $handle->file_new_name_body = $newimages;
                $handle->Process($path_upload);
            }
            if ($handle->processed) {
                echo json_encode(['status' => 'success', 'content' => $handle->file_dst_name]);
                if ($blogId > 0) {
                    $app->update("blog", ["thumbnail" => $setting['domain'] . "/datas/imgs/$handle->file_dst_name"], ["id" => $blogId]);
                    echo json_encode(['status' => 'success', 'content' => $jatbi->lang("Cập nhật thành công"), "test" => $imageUrl]);
                }
            }

            $jatbi->logs('blog', 'blog-edit', ["thumbnail" => $newimages]);
        } else {
            // echo json_encode($error);
            echo json_encode($error);
        }
    });

    $app->router("/blog/delete-image", 'POST', function ($vars) use ($app, $jatbi, $setting) {
        if (isset($_POST['file']) && !empty($_POST['file'])) {
            $file = $_POST['file'];
        } else {
            $error = ['status' => 'error', 'content' => $jatbi->lang('Không có hình ảnh')];
        }
        if (empty($error)) {
            $path_upload = dirname(dirname(dirname(__DIR__))) . $setting['mediaUrl'];
            $file = $path_upload . $file;
            if (file_exists($file)) {
                unlink($file);
                echo json_encode(['status' => 'success', 'content' => $jatbi->lang("Xóa thành công")]);
            } else {
                echo json_encode(['status' => 'error', 'content' => $jatbi->lang("Không tìm thấy file")]);
            }
        } else {
            echo json_encode($error);
        }
    });

    $app->router("/client/services/investment-call", 'GET', function ($vars) use ($app, $jatbi) {
        $templateData = $app->select('blog', [
            'id',
            'title',
            'content',
            'thumbnail'
        ], [
            'id' => 24,
        ]);

        $vars['data'] = $templateData[0] ?? null;
        $vars['title'] = $jatbi->lang("Gọi vốn - Tìm nhà đầu tư");

        echo $app->render('templates/client/services/investment_call.php', $vars);
    })->setPermissions(['investment-call']);

    $app->router("/client/services/acquisition", 'GET', function ($vars) use ($app, $jatbi) {
        $templateData = $app->select('blog', [
            'id',
            'title',
            'content',
            'thumbnail'
        ], [
            'id' => 26,
        ]);

        $vars['data'] = $templateData[0] ?? null;
        $vars['title'] = $jatbi->lang("Mua bán doanh nghiệp - M&A");

        echo $app->render('templates/client/services/acquisitions.php', $vars);
    })->setPermissions(['acquisition']);

    $app->router("/client/fee", 'GET', function ($vars) use ($app, $jatbi) {
        $templateData = $app->select('blog', [
            'id',
            'title',
            'content',
            'thumbnail'
        ], [
            'id' => 25,
        ]);

        $vars['data'] = $templateData[0] ?? null;
        $vars['title'] = $jatbi->lang("Chi phí");

        echo $app->render('templates/client/fee.php', $vars);
    })->setPermissions(['fee']);
});
