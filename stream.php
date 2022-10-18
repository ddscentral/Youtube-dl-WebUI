<?php
        require_once 'class/Session.php';
        require_once 'class/Downloader.php';
        require_once 'class/FileHandler.php';
	require_once 'class/HttpDownload.php';

        $session = Session::getInstance();
        $file = new FileHandler;

        if(!$session->is_logged_in())
        {
                header("Location: login.php");
                exit;
        }

	$fileId = (isset($_GET['video']) && !empty($_GET['video'])) ? $_GET['video'] : false;

	if ($fileId === false) {
		return;
	}

	$realFileName = $file->idToFileName($fileId, true);

	if ($realFileName === false) {
		echo "Not found !\n";

		return;
	}

	$mimeType = $file->getPlayerMimeType($realFileName);

	if ($mimeType === false) {
		echo "Not supported !\n";

		return;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
		header('Content-Type: ' . $mimeType);

		return;
	}

	$downloader = new httpdownload;
 	$downloader->set_byfile($realFileName);
	$downloader->mime = $mimeType;
 	$downloader->use_resume = true;
 	$downloader->download();
?>
