<?php
/*
Copyright 2017 Ziadin Givan

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

https://github.com/givanz/VvvebJs
*/

//scan media folder for all files to display in media modal

function sanitizePath($path)
{
	//sanitize, remove double dot .. and remove get parameters if any
	$path = preg_replace('@/+@', DIRECTORY_SEPARATOR, preg_replace('@\?.*$@', '', preg_replace('@\.{2,}@', '', preg_replace('@[^\/\\a-zA-Z0-9\-\._]@', '', $path))));
	return $path;
}

if (isset($_POST['mediaPath']) && ($path = sanitizePath(substr($_POST['mediaPath'], 0, 256)))) {
	define('UPLOAD_PATH', $path);
} else {
	define('UPLOAD_PATH', '/VvvebJs/media');
}
$scandir = dirname(__DIR__) . UPLOAD_PATH;


// Run the recursive function
// This function scans the files folder recursively, and builds a large array

$scan = function ($dir) use ($scandir, &$scan) {
	$files = [];

	// Is there actually such a folder/file?

	if (file_exists($dir)) {
		foreach (scandir($dir) as $f) {
			if (!$f || $f[0] == '.') {
				continue; // Bỏ qua các tệp ẩn
			}

			$filePath = $dir . '/' . $f;
			$relativePath = '';

			// Tìm vị trí của 'datas/' trong đường dẫn
			$pos = strpos($filePath, 'datas/');
			if ($pos !== false) {
				// Lấy phần đường dẫn bắt đầu từ 'datas/'
				$relativePath = substr($filePath, $pos);
			}

			if (is_dir($filePath)) {
				// Nếu là thư mục
				$files[] = [
					'name'  => $f,
					'type'  => 'folder',
					'path'  => $relativePath,
					'items' => $scan($filePath), // Đệ quy để lấy nội dung của thư mục
				];
			} else {
				// Nếu là tệp
				$files[] = [
					'name' => $f,
					'type' => 'file',
					'path' => '/' . $relativePath,
					'size' => filesize($filePath), // Lấy kích thước của tệp
				];
			}
		}
	}



	return $files;
};

$response = $scan($scandir);

// Output the directory listing as JSON

header('Content-type: application/json');

echo json_encode([
	'success' => $scandir,
	'name'  => '',
	'type'  => 'folder',
	'path'  => '',
	'items' => $response,
]);
