<?php
if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');
$view = $app->getValueData('view');
$etpl = $app->getValueData('etpl');

$app->router("::error", 'GET', function ($vars) use ($app, $jatbi, $setting) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo $app->render('templates/common/404.html', $vars, 'global');
    } else {
        echo $app->render('templates/common/404.html', $vars);
    }
});

$app->router("/lang/{code}", 'GET', function ($vars) use ($app, $jatbi, $setting) {
    $code = $vars['code'] ?? 'vi';
    // $langPath = dirname(__DIR__, 2) . "/templates/lang/frontend/$code.php";
    // echo $langPath;

    // if (file_exists($langPath)) {
    // Dùng hàm setCookie của $app
    // }
    $app->setCookie('lang', $code, time() + $setting['cookie'], '/');
    $app->redirect($_SERVER['HTTP_REFERER']);
});

/* $app->router("/{page}/{child_page}", 'GET', function ($vars) use ($app, $jatbi, $setting) {
    if (isset($vars['page']) && isset($vars['child_page'])) {
        $page = $vars['page'];
        $data = $app->get('page', '*', [
            'status' => 'A',
            'link' => [$page],
        ]);
        if ($data == null) {
            echo $app->render('templates/common/404.html', $vars);
            die();
        }

        $child_page = $vars['child_page'];
    } else {
        echo $app->render('templates/common/404.html', $vars);
        die();
    }
}); */

$app->router("/{page}/{child_page}/{seo}", 'GET', function ($vars) use ($app, $jatbi, $setting, $view) {
    $view->admin_path = explode("/", $setting['backend'])[1];

    if (isset($vars['page']) && isset($vars['child_page'])) {
        $page = $vars['page'];
        $child_page = $vars['child_page'];
        $data = $app->select('page', '*', [
            'status' => 'A',
            'OR' => ['link' => $page, 'link' => $child_page],
        ]);
        if ($data == null) {
            echo $app->render('templates/common/404.html', $vars);
            die();
        }
    } else {
        echo $app->render('templates/common/404.html', $vars);
        die();
    }
    // Lấy blog id
    if (isset($vars['seo'])) {
        $blogSEO = $vars['seo'];
        $data = $app->get('blog', [
            'title',
            'content',
            'thumbnail',
            'description',
            'seo',
        ], [
            'seo' => $blogSEO,
            'deleted' => 0,
        ]);
        if ($data == null) {
            echo $app->render('templates/common/404.html', $vars);
            die();
        }
    } else {
        echo $app->render('templates/common/404.html', $vars);
        die();
    }
    $view->admin_path = explode("/", $setting['backend'])[1];

    $view->title1 = "placeholder";
    // Lấy dữ liệu database
    $view->data = $data;

    $view->data['thumbnail'] = $setting['mediaUrl'] . $view->data['thumbnail'];
    // Tạo metadata
    $title = mb_ucfirst(mb_strtolower($jatbi->lang($view->data['title']), 'UTF-8'), 'UTF-8');
    $view->metadata = [
        'locale' => "vi_VN",
        'type' => "website",
        'title' => $title . " | " . $setting['name'],
        'url' => $setting['url'] . $_SERVER['REQUEST_URI'],
        'site_name' => $setting['name'],
        'modified_time' => date('Y-m-d H:i:s'),
    ];

    $view->header = $app->component('header_frontend');
    $view->footer = $app->component('footer_frontend');
    // Hiển thị
    $view->render("blog.html");
});

$app->router("/test", 'GET', function ($vars) use ($app, $jatbi, $setting, $view, $etpl) {
    $etpl->set("#title", "Tiêu đề h3 đã được thay đổi!");
    $etpl->set("#title|style:color", 'red');
    // $etpl->set(".test", "Tiêu đề div class test đã được thay đổi!");
    // $etpl->set("p", "Nội dung tất cả thẻ p đã được thay đổi!");
    // $etpl->set("p#tag-p", "Nội dung thẻ p có id tag-p đã được thay đổi!");
    // $etpl->set("p.tag-p", "Nội dung thẻ p có class tag-p đã được thay đổi!");

    // $etpl->set(".product > .item.active", "Nội dung item active trong product!");
    // $etpl->set(".product > .item > .name", "Tên của item trong product! asdasd");

    // $app->select("accounts", "*", ["deleted" => 0], function ($data) use (&$datas, $jatbi, $app) {
    //     $datas[] = [
    //         '.name' => $data['name'],
    //         '.price' => $data['active'],
    //         '.images img|src' => $data['avatar'],
    //     ];
    // });
    // $etpl->loop(".list-new .item", $datas);

    // $etpl->set('[data-test="showWelcome"]', 'đây là test'); // Hoặc false


    // $etpl->set("a", "Đây là một liên kết");
    // $etpl->set("a|href", "https://google.com");

    // $etpl->set("a.link", "Đây là một liên kết eclo.vn");
    // $etpl->set("a.link|href", "https://eclo.com");

    // $etpl->set(".img", ""); // Thường thì thẻ img không có text content
    // $etpl->set(".img|src", "https://ellm.io/templates/assets/img/logo.svg");
    // $etpl->set(".img|alt", "Ảnh placeholder");
    // $etpl->set(".img|data-action", "Ảnh placeholder");
    // $etpl->set(".img|class", "Ảnh placeholder");

    // $etpl->set(".my-button", "Nhấn  đây");
    // $etpl->set(".my-button|data-action", "openModal");
    // $etpl->set(".my-button|data-modal-id", "myModal");
    // $etpl->set(".my-button|class:add", "btn btn-primary");

    // // // Thêm nội dung vào cuối phần tử .container
    // $etpl->set('.container|append:html', '<b>Thêm nội dung</b>');

    // // // Thêm nội dung vào đầu phần tử .container
    // $etpl->set('.container|prepend:html', '<b>Đầu tiên là</b>');

    // // // Gán chỉ văn bản (không bao gồm thẻ HTML)
    // $etpl->set('.container', 'Chỉ là văn bản');
    // $etpl->set('.container|style:font-weight', 'bold');

    // // // Gán CSS inline cho phần tử .container
    // $etpl->set('.container|style:color', 'red');

    // // // Thêm class vào phần tử .container
    // $etpl->set('.container|class:add', 'active');
    // $etpl->set('.container|class:add', 'test');
    // $etpl->set('.container|id:add', 'active');
    // $etpl->set('.container|id:add', 'ok');

    // // // Xóa class khỏi phần tử .container
    // $etpl->set('.container|class:remove', 'ks');

    // // // Gán giá trị cho phần tử input hoặc textarea trong .container
    // $etpl->set('.container|value', 'Giá trị mới');

    // // // Gán dataset cho phần tử .container
    // $etpl->set('.container|data-custom', 'value');

    $vars['html'] = $etpl->render("templates/frontend/blog.html");
    echo $app->render('templates/test.html', $vars);
});
