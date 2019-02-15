<?php

$dir = $argv[1];

function scan_dir($dir)
{
	global $recursive;
	$recursive = true;
	global $arrayFiles;
	static $arrayFiles = [];
	if ($folder = opendir($dir))
	{
		while (false !== ($entry = readdir($folder)))
		{
			if ($entry != "." && $entry != ".." && $entry != ".git")
			{
				echo "$entry\n";
				if (is_dir($dir ."/". $entry) && $recursive)
				{
					scan_dir($dir."/".$entry);
				}
				else
				{
					$arrayFiles[] = $dir . "/" .$entry;
				}
			}
		}

		$img = array();

		foreach ($arrayFiles as $file)
		{
			if (exif_imagetype($file) == false)
			{
				unset($file);
				$arrayFiles = array_values($arrayFiles);
			}
			elseif (!is_dir($file))
			{
				$img[] = $file;
			}
		}
		closedir($folder);
		return $img;
	}
}

function img_size($arrayFiles, $argv)
{
	$arrayWidth = array();
	$totalSize = 0;
	$path = $argv[1];

	foreach ($arrayFiles as $filepng)
	{
		$filepng = imagecreatefrompng($filepng);

		$imWidth = imagesx($filepng);

		$totalSize = $totalSize += $imWidth;

		$imHeight = imagesy($filepng);

		array_push($arrayWidth, $imHeight);
	}
	$biggestHeight = max($arrayWidth);
	create_background($biggestHeight, $totalSize, $arrayFiles);
}

function create_background($biggestHeight, $totalSize, $arrayFiles)
{
	$sprite = imagecreatetruecolor($totalSize, $biggestHeight);
	$background = imagecolorallocatealpha($sprite, 255, 255, 255, 127);
	imagefill($sprite, 0, 0, $background);
	imagealphablending($sprite, false);
	imagesavealpha($sprite, true);
	$im = imageinterlace($sprite, true);
	my_sprite($sprite, $im, $arrayFiles);
}

function my_sprite($sprite, $im, $arrayFiles)
{

	$fileCss = "style.css";

	$png = imagecreatefrompng($arrayFiles[0]);
	$widthFirst = imagesx($png);
	$heightFirst = imagesy($png);

	$im = imagecopy($sprite, $png, 0, 0, 0, 0, $widthFirst, $heightFirst);

	$totalHeight = 0;
	$totalWidth = 0;
	$css = "";
	$xpos = 0;

	foreach ($arrayFiles as $filename)
	{
		$png = imagecreatefrompng($filename);

		$width = imagesx($png);
		$height = imagesy($png);

		$im = imagecopy($sprite, $png, $totalWidth, $totalHeight, 0, 0, $width, $height);

		$totalWidth = $totalWidth + $width;

		$im = imagepng($sprite, "sprite.png");

		$clearPng = pathinfo($filename);
		$imgName = str_replace(".", "", $clearPng['filename']);
		$css .=
".". $imgName . "
{
width: ".$width."px; height: ".$height."px;
background: url('sprite.png') -". $xpos."px 0px;
}\n\n";

	$xpos += $width;

	}
	file_put_contents($fileCss, $css);
	echo "done\n";
	return 0;
}
img_size(scan_dir($dir), $argv);
