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



// Gán thuộc tính action cho form
#contactForm|action = $_SERVER['REQUEST_URI']

// Thêm biến giả để "đánh lừa" vtpl, tránh lỗi trên trang tĩnh
#dummy-variable-for-vtpl-fix = $this->dummy