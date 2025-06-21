<?php
if (!defined('ECLO'))
    die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');
$common = $app->getValueData('common');
$view = $app->getValueData('view');

$app->router("/", 'GET', function ($vars) use ($app, $jatbi, $view, $setting) {
    $view->admin_path = explode("/", $setting['backend'])[1];

    // 1. Lấy dữ liệu dịch vụ từ CSDL (giữ nguyên logic của bạn)
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

    // 2. Gán dữ liệu vào đối tượng $view để file .tpl có thể sử dụng
    $view->services = $services;

    // 3. Thiết lập metadata cho trang (tùy chọn nhưng nên có)
    $view->metadata = array(
        'title' => "Trang chủ CORE",
        // Thêm các metadata khác nếu cần
    );

    // 4. Gọi các component chung
    // Giả sử bạn đã đổi tên component trong file components.php
    $view->header = $app->component('header_frontend'); 
    $view->footer = $app->component('footer_frontend');

    // 5. Render file view tương ứng
    $view->render("home.html");
});

$app->router("/login", 'GET', function($vars) use ($app, $jatbi,$setting) {
        if(!$app->getSession("accounts")){
            $vars['templates'] = 'login';
            echo $app->render('templates/frontend/login.html', $vars);
        }
        else {
            $app->redirect('/admin/consultation');
        }
    });
    $app->router("/login", 'POST', function($vars) use ($app, $jatbi,$setting) {
        $app->header([
            'Content-Type' => 'application/json',
        ]);
        if($app->xss($_POST['email']) && $app->xss($_POST['password'])){
            $data = $app->get("accounts","*",[
                "OR"=>[
                    "email"     => $app->xss($_POST['email']),
                    "account"   => $app->xss($_POST['email']),
                ],
                "status"=>"A",
                "deleted"=>0
            ]);
            if(isset($data) && password_verify($app->xss($_POST['password']), $data['password'])) {
                $gettoken = $app->randomString(256);
                $payload = [
                    "ip"        => $app->xss($_SERVER['REMOTE_ADDR']),
                    "id"        => $data['active'],
                    "email"     => $data['email'],
                    "token"     => $gettoken,
                    "agent"     => $_SERVER["HTTP_USER_AGENT"],
                ];
                $token = $app->addJWT($payload);
                $getLogins = $app->get("accounts_login","*",[
                    "accounts"  => $data['id'],
                    "agent"     => $payload['agent'],
                    "deleted"   => 0,
                ]);
                if($getLogins>1){
                    $app->update("accounts_login",[
                        "accounts" => $data['id'],
                        "ip"    =>  $payload['ip'],
                        "token" =>  $payload['token'],
                        "agent" =>  $payload["agent"],
                        "date"  => date("Y-m-d H:i:s"),
                    ],["id"=>$getLogins['id']]);
                }
                else {
                    $app->insert("accounts_login",[
                        "accounts" => $data['id'],
                        "ip"    =>  $payload['ip'],
                        "token" =>  $payload['token'],
                        "agent" =>  $payload["agent"],
                        "date"  => date("Y-m-d H:i:s"),
                    ]);
                }
                $app->setSession('accounts',[
                    "id" => $data['id'],
                    "agent" => $payload['agent'],
                    "token" => $payload['token'],
                    "active" => $data['active'],
                ]);
                if($app->xss($_POST['remember'] ?? '' )){
                    $app->setCookie('token', $token,time()+$setting['cookie'],'/');
                }
                echo json_encode(['status' => 'success','content' => $jatbi->lang('Đăng nhập thành công')]);
                $payload['did'] = $app->getCookie('did');
                $jatbi->logs('accounts','login',$payload);
            }
            else {
                echo json_encode(['status' => 'error','content' => $jatbi->lang('Tài khoản hoặc mật khẩu không đúng')]);
            }
        }
        else {
            echo json_encode(['status' => 'error','content' => $jatbi->lang('Vui lòng không để trống')]);
        }
    });

$app->router("/register-post", 'GET', function($vars) use ($app, $jatbi, $setting) {
    $vars['title'] = $jatbi->lang("Đăng ký nhận tư vấn");
    $serviceID =  isset($_GET['serviceID']) ? $_GET['serviceID'] : '';
    $vars['serviceID'] = $serviceID;
    $services = $app->select("services", ["id","title", "type"], [
        "status" => "A",
        "ORDER" => ["id" => "ASC"]
    ]);
    $vars['service_packages']= $services ;  

    echo $app->render('templates/frontend/register-post.html', $vars, 'global');
});

$app->router("/register-post", 'POST', function($vars) use ($app, $jatbi, $setting) {
    $app->header(['Content-Type' => 'application/json']);

        // Lấy dữ liệu và xử lý XSS
        $name            = $app->xss($_POST['name'] ?? '');
        $phone           = $app->xss($_POST['phone'] ?? '');
        $email           = $app->xss($_POST['email'] ?? '');
        $company         = $app->xss($_POST['name_business'] ?? '');
        $note            = $app->xss($_POST['note'] ?? '');
        $service_package = $app->xss($_POST['service_package'] ?? '');
        $consult_method  = $app->xss($_POST['consult_method'] ?? '');

        // Kiểm tra dữ liệu bắt buộc
        if (empty($name) || empty($phone) || empty($service_package) || empty($consult_method)) {
            echo json_encode([
                "status" => "error",
                "content" => $jatbi->lang("Vui lòng điền đầy đủ thông tin bắt buộc.")
            ]);
            return;
        }

        // Kiểm tra định dạng email nếu có
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "content" => "Địa chỉ email không hợp lệ."
            ]);
            return;
        }

        // Kiểm tra định dạng số điện thoại (tùy chỉnh theo yêu cầu thực tế)
        if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
            echo json_encode([
                "status" => "error",
                "content" => "Số điện thoại không hợp lệ."
            ]);
            return;
        }

        // Thực hiện lưu dữ liệu
        try {
            $insert = [
                "name"     => $name,
                "phone"    => $phone,
                "email"    => $email,
                "name_business"  => $company,
                "note"     => $note,
                "service"  => $service_package,
                "method"   => $consult_method,
            ];

            $result = $app->insert("appointments", $insert);

            if (!$result) {
                echo json_encode(["status" => "error","content" => $jatbi->lang("Không thể lưu dữ liệu.")]);
                return;
            }

        echo json_encode([
            "status" => "success",
            "content" => $jatbi->lang("Yêu cầu đã được lên lịch "),
        ]);

        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "content" => "Lỗi: " . $e->getMessage()
            ]);
        }
});


$app->router("/contact", 'GET', function($vars) use ($app, $jatbi, $view, $setting) {
    $view->admin_path = explode("/", $setting['backend'])[1];

    // 1. Gán các giá trị cho view (nếu có)
    // Ví dụ, gán tiêu đề cho trang
    $view->title = "Liên hệ";
    $view->setting = $setting; // Truyền biến setting để có thể dùng trong TPL

    // 2. Gọi các component header và footer chung
    $view->header = $app->component('header_frontend');
    $view->footer = $app->component('footer_frontend');

    // 3. Render file view
    $view->render("contact.html");
});

$app->router("/consultation", 'GET', function ($vars) use ($app, $jatbi, $view, $setting) {
    $view->admin_path = explode("/", $setting['backend'])[1];

    // 1. Lấy danh sách dịch vụ từ CSDL
    $services = $app->select("services", ["id", "title", "type"], [
        "status" => "A",
        "ORDER" => ["id" => "ASC"]
    ]);

    // 2. Gán dữ liệu vào đối tượng $view để file .tpl có thể sử dụng
    $view->service_packages = $services;

    // 3. Gọi các component header và footer chung
    $view->header = $app->component('header_frontend');
    $view->footer = $app->component('footer_frontend');

    // 4. Render file view
    $view->render("consultation.html");
});


// $app->router("/project-detail", 'GET', function($vars) use ($app, $jatbi, $setting) {
//     echo $app->render('templates/dhv/project-detail.html', $vars);
// }); 

// $app->router("/news-detail", 'GET', function($vars) use ($app, $jatbi, $setting) {
//     echo $app->render('templates/dhv/news-detail.html', $vars);
// }); 

// $app->router("/library-detail", 'GET', function($vars) use ($app, $jatbi, $setting) {
//     echo $app->render('templates/dhv/library-detail.html', $vars);
// }); 

$app->router("/about", 'GET', function($vars) use ($app, $jatbi, $setting, $view) {
    $view->admin_path = explode("/", $setting['backend'])[1];

    // 1. Gán dữ liệu vào đối tượng $view để file .tpl có thể sử dụng
    $view->setting = $setting;

    // 2. Thiết lập metadata cho trang (tùy chọn nhưng nên có)
    $view->metadata = array(
        'title' => "Giới thiệu CORE",
        // Thêm các metadata khác nếu cần
    );

    // 3. Gọi các component chung
    $view->header = $app->component('header_frontend'); 
    $view->footer = $app->component('footer_frontend');

    // 4. Render file view tương ứng
    $view->render("about.html");
});

// $app->router("/business-services", 'GET', function($vars) use ($app, $jatbi, $setting) {
//     echo $app->render('templates/dhv/business-services.html', $vars);
// }); 

// $app->router("/event-services", 'GET', function($vars) use ($app, $jatbi, $setting) {
//     echo $app->render('templates/dhv/event-services.html', $vars);
// });

// $app->router("/services-detail", 'GET', function($vars) use ($app, $jatbi, $setting) {
//     echo $app->render('templates/dhv/services-detail.html', $vars);
// });