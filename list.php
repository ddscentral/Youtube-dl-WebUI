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

	if($session->is_logged_in() && isset($_GET["delete"]))
	{
		$file->delete($_GET["delete"]);
		header("Location: list.php");
		exit;
	}

	$files = $file->listFiles();
	$parts = $file->listParts();

	require 'views/header.php';
?>
		<div class="container my-4">
		<?php
			if(!empty($files))
			{
		?>
			<h2>List of available files:</h2>
			<table class="table table-striped table-hover ">
				<thead>
					<tr>
						<th>Title</th>
						<th>Size</th>
						<th colspan="2"><span class="pull-right">Actions</span></th>
					</tr>
				</thead>
				<tbody>
			<?php
				foreach($files as $f)
				{
					// DDS: a lazy way to extract video ID
					$fparts = pathinfo($f['name']);

					$filename = $fparts['filename'];

					$videoid = substr($filename, -11);

					echo "<tr>";
					if ($file->get_relative_downloads_folder())
					{
						echo "<td><a href=\"".rawurlencode($file->get_relative_downloads_folder()).'/'.rawurlencode($f["name"])."\" download>".$f["name"]."</a></td>";
					}
					else
					{
						echo "<td>".$f["name"]."</td>";
					}
					echo "<td>".$f["size"]."</td>";
					$basename = htmlspecialchars(basename($f['name']));
					echo "<td><a href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm pull-right\" onclick=\"return confirm('Really delete $basename ?')\">Delete</a></td>";
					if ($file->get_relative_downloads_folder()) {
						echo "<td><a href=\"./player.php?video=".sha1($f["name"])."\" class=\"btn btn-success btn-sm pull-right\" target=\"_blank\">Play</a></td>";
					}
					if (strlen($videoid) == 11) {
						echo "<td><a href=\"./../redirect.php?v=".$videoid."\" class=\"btn btn btn-info btn-sm pull-right\" target=\"_blank\">Visit</a></td>";
					}
					echo "</tr>";
				}
			?>
				</tbody>
			</table>
		<?php
			}
			else
			{
				echo "<br><div class=\"alert alert-warning\" role=\"alert\">No files!</div>";
			}
		?>
			<br/>
		<?php
			if(!empty($parts))
			{
		?>
			<h2>List of part files:</h2>
			<table class="table table-striped table-hover ">
				<thead>
					<tr>
						<th>Title</th>
						<th>Size</th>
						<th><span class="pull-right">Delete link</span></th>
					</tr>
				</thead>
				<tbody>
			<?php
				foreach($parts as $f)
				{
					echo "<tr>";
					if ($file->get_relative_downloads_folder())
					{
						echo "<td><a href=\"".rawurlencode($file->get_relative_downloads_folder()).'/'.rawurlencode($f["name"])."\" download>".$f["name"]."</a></td>";
					}
					else
					{
						echo "<td>".$f["name"]."</td>";
					}
					echo "<td>".$f["size"]."</td>";
					echo "<td><a href=\"./list.php?delete=".sha1($f["name"])."\" class=\"btn btn-danger btn-sm pull-right\">Delete</a></td>";
					echo "</tr>";
				}
			?>
				</tbody>
			</table>
			<br/>
			<br/>
		<?php
			}
		?>
			<br/>
		</div><!-- End container -->
<?php
	require 'views/footer.php';
?>
