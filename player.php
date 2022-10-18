<?php
        require_once 'class/Session.php';
        require_once 'class/Downloader.php';
        require_once 'class/FileHandler.php';

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

	$fileName = $file->idToFileName($fileId);

	if ($fileName === false) {
		echo "Not found !\n";

		return;
	}

	$mimeType = $file->getPlayerMimeType($fileName);

	if ($mimeType === false) {
		echo "Not supported !\n";

		return;
	}

?>
<!DOCTYPE html>
<html>
        <head>
                <meta charset="utf-8">
                <title>Youtube-dl Player - <?php echo htmlspecialchars($fileName); ?></title>
                <!-- https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css -O cdn/bootstrap.min.css -->
                <link rel="stylesheet" href="./css/cdn/bootstrap.min.css" media="screen">
                <link rel="stylesheet" href="./css/custom.css">
		<link rel="stylesheet" href="./css/player.css">
                <link rel="Shortcut Icon" href="./favicon_144.png" type="image/x-icon">
        </head>
	<body>
	<video class="video-player" controls>
		<source src="<?php echo 'stream.php?video=' . $fileId; ?>" type="<?php echo $mimeType; ?>">
	</video>
	</body>
</html>


