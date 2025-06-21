<?php
$common = [];
$base_url = '/datas/imgs/';

function getFileStoragePath($url)
{
	global $base_url;
	return $base_url . $url;
}

function getThumbnail($img_name)
{
	global $base_url;
	$defaultImage = "default_thumbnail.jpg";
	return $base_url . (empty($img_name) ? $defaultImage : $img_name);
}

function getAvatar($img_name)
{
	global $base_url;
	$defaultImage = "default_user_avatar.png";
	return $base_url . (empty($img_name) ? $defaultImage : $img_name);
}
