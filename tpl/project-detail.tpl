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
          . '<li class="breadcrumb-item"><a href="/projects">Dự án</a></li>';
    echo $html;
?>

// Hiển thị nội dung tab Overview
#pills-1 = <?php
    $html = '<div class="course-details-item">'
          . '<div class="course-learn-list">'
          . '<h4 class="title">' . htmlspecialchars($this->project['title']) . '</h4>'
          . '<div class="row">' . $this->project['description'] . '</div>'
          . '</div>'
          . '<div class="course-learner-slide">';
    foreach ($this->project_images as $project_image) {
        $html .= '<div class="course-learner-item text-center">'
               . '<div class="course-learner-thumb mx-auto" style="width: 300px; height: 300px; overflow: hidden;">'
               . '<img src="' . htmlspecialchars($this->setting['template'] . '/uploads/projects/' . $project_image['image_url']) . '" alt="learner" class="w-100 h-100" style="object-fit: cover; display: block;">'
               . '</div>'
               . '<div class="course-learner-content mt-2">'
               . '<h5 class="title mb-0">' . htmlspecialchars($project_image['caption']) . '</h5>'
               . '</div>'
               . '</div>';
    }
    $html .= '</div></div>';
    echo $html;
?>
// Thêm dữ liệu tác giả vào khối tư vấn (consultation-section)
.expert-avatar img|src = <?php
    // Kiểm tra và sử dụng ảnh tác giả, nếu không có thì dùng ảnh mặc định
    echo htmlspecialchars($this->setting['template'] . '/' . ($this->project['author_image'] ?? 'default_author.jpg'));
?>
.expert-avatar img|alt = <?php
    // Sử dụng tên tác giả cho alt text, nếu không có thì dùng tên mặc định
    echo htmlspecialchars($this->project['author_name'] ?? 'Chuyên gia');
?>
.expert-name = <?php
    // In tên tác giả, nếu không có thì dùng tên mặc định
    echo htmlspecialchars($this->project['author_name'] ?? 'Chuyên gia. A');
?>
.consultation-quote = <?php 
    echo ("Bạn đang gặp vấn đề về đồng bộ văn hóa nội bộ như " . ($this->project['client_name'] ?? 'một công ty nào đó') . "?");
?>

// Hiển thị nội dung tab Reviews
#pills-2 = <?php
    $html = '<div class="course-details-item">'
          . '<div class="course-text"><p>This is the most comprehensive, yet straight-forward, course for the Python programming language on Udemy! Whether you have never programmed before, already know basic syntax, or want to learn about the advanced features of Python, this course is for you! In this course we will teach you Python 3. (Note, we also provide older Python 2 notes in case you need them)</p></div>'
          . '<div class="course-learn-list">'
          . '<h4 class="title">What you\'ll learn </h4>'
          . '<div class="row">'
          . '<div class="col-lg-6">'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '</div>'
          . '<div class="col-lg-6">'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '<div class="course-learn-item"><i class="fal fa-check"></i><p>Learn to use Python professionally, learning both Python 2 and Python 3!</p></div>'
          . '</div>'
          . '</div>'
          . '</div>'
          . '<div class="course-learn-text">'
          . '<h4 class="title">What you\'ll learn </h4>'
          . '<span>Become a Python Programmer and learn one of employer\'s most requested skills of 2019!</span>'
          . '<p>With over 100 lectures and more than 20 hours of video this comprehensive course leaves no stone unturned! This course includes quizzes, tests, and homework assignments as well as 3 major projects to create a Python project portfolio!</p>'
          . '<p class="pt-15 pb-15">This course will teach you Python in a practical manner, with every lecture comes a full coding screencast and a corresponding code notebook! Learn in whatever manner is best for you!</p>'
          . '<p>We will start by helping you get Python installed on your computer, regardless of your operating system, whether its Linux, MacOS, or Windows, we\'ve got you covered!</p>'
          . '</div>'
          . '<div class="course-learner-slide">'
          . '<div class="course-learner-item d-flex align-items-center">'
          . '<div class="course-learner-thumb"><img src="assets/images/learner-thumb-1.jpg" alt="learner"></div>'
          . '<div class="course-learner-content"><h5 class="title">Rosalina D. Williamson</h5><span>Python Learner</span><p>We will start by helping you get Python installed on your computer, regardless of your operating system, whether its Linux, MacOS, or Windows, we\'ve got you covered!</p></div>'
          . '</div>'
          . '<div class="course-learner-item d-flex align-items-center">'
          . '<div class="course-learner-thumb"><img src="assets/images/learner-thumb-1.jpg" alt="learner"></div>'
          . '<div class="course-learner-content"><h5 class="title">Rosalina D. Williamson</h5><span>Python Learner</span><p>We will start by helping you get Python installed on your computer, regardless of your operating system, whether its Linux, MacOS, or Windows, we\'ve got you covered!</p></div>'
          . '</div>'
          . '<div class="course-learner-item d-flex align-items-center">'
          . '<div class="course-learner-thumb"><img src="assets/images/learner-thumb-1.jpg" alt="learner"></div>'
          . '<div class="course-learner-content"><h5 class="title">Rosalina D. Williamson</h5><span>Python Learner</span><p>We will start by helping you get Python installed on your computer, regardless of your operating system, whether its Linux, MacOS, or Windows, we\'ve got you covered!</p></div>'
          . '</div>'
          . '<div class="course-learner-item d-flex align-items-center">'
          . '<div class="course-learner-thumb"><img src="assets/images/learner-thumb-1.jpg" alt="learner"></div>'
          . '<div class="course-learner-content"><h5 class="title">Rosalina D. Williamson</h5><span>Python Learner</span><p>We will start by helping you get Python installed on your computer, regardless of your operating system, whether its Linux, MacOS, or Windows, we\'ve got you covered!</p></div>'
          . '</div>'
          . '</div>'
          . '</div>';
    echo $html;
?>
// Biến giả để khắc phục lỗi Vtpl
#dummy-variable-for-vtpl-fix = $this->dummy