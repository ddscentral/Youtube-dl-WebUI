<?php

class FileHandler
{
	private $config = [];
	private $re_partial = '/(?:\.part(?:-Frag\d+)?|\.ytdl)$/m';

	public function __construct()
	{
		$this->config = require dirname(__DIR__).'/config/config.php';
	}

	public function listFiles()
	{
		$files = [];

		if(!$this->outuput_folder_exists())
			return;

		$folder = $this->get_downloads_folder().'/';

		foreach(glob($folder.'*.*', GLOB_BRACE) as $file)
		{
			$content = [];
			$content["name"] = str_replace($folder, "", $file);
			$content["size"] = $this->to_human_filesize(filesize($file));
			
			if (preg_match($this->re_partial, $content["name"]) === 0) {
				$files[] = $content;
			}
			
		}

		return $files;
	}

	public function listParts()
	{
		$files = [];

		if(!$this->outuput_folder_exists())
			return;

		$folder = $this->get_downloads_folder().'/';

		foreach(glob($folder.'*.*', GLOB_BRACE) as $file)
		{
			$content = [];
			$content["name"] = str_replace($folder, "", $file);
			$content["size"] = $this->to_human_filesize(filesize($file));
			
			if (preg_match($this->re_partial, $content["name"]) !== 0) {
				$files[] = $content;
			}
			
		}

		return $files;
	}
	
	public function is_log_enabled()
	{
		return !!($this->config["log"]);
	}
	
	public function countLogs()
	{
		if(!$this->config["log"])
			return;

		if(!$this->logs_folder_exists())
			return;

		$folder = $this->get_logs_folder().'/';
		return count(glob($folder.'*.txt', GLOB_BRACE));
	}

	public function listLogs()
	{
		$files = [];
		
		if(!$this->config["log"])
			return;

		if(!$this->logs_folder_exists())
			return;

		$folder = $this->get_logs_folder().'/';

		foreach(glob($folder.'*.txt', GLOB_BRACE) as $file)
		{
			$content = [];
			$content["name"] = str_replace($folder, "", $file);
			$content["size"] = $this->to_human_filesize(filesize($file));

			try {
				$lines = explode("\r", file_get_contents($file));
				$content["lastline"] = array_slice($lines, -1)[0];
				$content["100"] = strpos($lines[count($lines)-1], ' 100% of ') > 0;
			} catch (Exception $e) {
				$content["lastline"] = '';
				$content["100"] = False;
			}	
			try {
				$handle = fopen($file, 'r');
				fseek($handle, filesize($file) - 1);
				$lastc = fgets($handle);
				fclose($handle);
				$content["ended"] = ($lastc === "\n");
			} catch (Exception $e) {
				$content["ended"] = False;
			}


			$files[] = $content;
		}

		return $files;
	}

	public function delete($id)
	{
		$folder = $this->get_downloads_folder().'/';

		foreach(glob($folder.'*.*', GLOB_BRACE) as $file)
		{
			if(sha1(str_replace($folder, "", $file)) == $id)
			{
				unlink($file);
			}
		}
	}

	public function idToFileName($id, $real = false)
	{
		$folder = $this->get_downloads_folder().'/';

		$outputFolder = $this->config['outputFolder'];

		// DDS: check if relative folder is defined

		if (!$real) {
			$relativeFolder = isset($this->config['relativeFolder']) ? $this->config['relativeFolder'] : false;

			if ($relativeFolder === false) {
				// DDS: check if output folder is relative
				if (strpos($outputFolder, '/') !== 0) {
					$relativeFolder == $outputFolder;
				} else {
					// DDS: it's not - bail out
					return false;
				}
			}
		} else {
			// DDS: disable replacing if real path is requested
			$relativeFolder = $outputFolder;
		}

		foreach(glob($folder.'*.*', GLOB_BRACE) as $file)
		{
			if(sha1(str_replace($folder, "", $file)) == $id)
			{
				$relativeFile = str_replace($outputFolder, $relativeFolder, $file);

				return $relativeFile;
			}
		}


		return false;
	}

	public function getPlayerMimeType($file)
	{
		$pi = pathinfo($file);

		$ext = strtolower($pi['extension']);

		switch ($ext) {
			case 'mp4':
				return 'video/mp4';
				break;
			case 'mkv':
				return 'video/webm';
				break;
			case 'webm':
				return 'video/webm';
				break;
			case 'm4a':
				return 'audio/m4a';
				break;
			case 'opus':
				return 'audio/opus';
				break;
			default:
				return false;
				break;
		}
	}

	public function deleteLog($id)
	{
		$folder = $this->get_logs_folder().'/';

		foreach(glob($folder.'*.txt', GLOB_BRACE) as $file)
		{
			if(sha1(str_replace($folder, "", $file)) == $id)
			{
				unlink($file);
			}
		}
	}

	private function outuput_folder_exists()
	{
		if(!is_dir($this->get_downloads_folder()))
		{
			//Folder doesn't exist
			if(!mkdir($this->get_downloads_folder(),0777))
			{
				return false; //No folder and creation failed
			}
		}
		
		return true;
	}

	public function to_human_filesize($bytes, $decimals = 1)
	{
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	public function free_space()
	{
		return $this->to_human_filesize(disk_free_space(realpath($this->get_downloads_folder())));
	}

	public function used_space()
	{
		$path = realpath($this->get_downloads_folder());
		$bytestotal = 0;
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
			$bytestotal += $object->getSize();
		}
		return $this->to_human_filesize($bytestotal);
	}

	public function get_downloads_folder()
	{
		$path =  $this->config["outputFolder"];
		if(strpos($path , "/") !== 0)
		{
				$path = dirname(__DIR__).'/' . $path;
		}
		return $path;
	}

	public function get_logs_folder()
	{
		$path =  $this->config["logFolder"];
		if(strpos($path , "/") !== 0)
		{
				$path = dirname(__DIR__).'/' . $path;
		}
		return $path;
	}

	public function get_relative_downloads_folder()
	{
		$path =  $this->config["outputFolder"];
		if(strpos($path , "/") !== 0)
		{
				return $this->config["outputFolder"];
		} else {
			// DDS: rffmpeg requires that we use a real folder, not a relative symlink. Add extra config option for specifying relative symlink.
			if (strlen($this->config['relativeFolder']) !== 0) {
				return $this->config['relativeFolder'];
			}
		}
		return false;
	}

	public function get_relative_log_folder()
	{
		$path =  $this->config["logFolder"];
		if(strpos($path , "/") !== 0)
		{
				return $this->config["logFolder"];;
		}
		return false;
	}

	private function logs_folder_exists()
	{
		if(!is_dir($this->get_logs_folder()))
		{
			//Folder doesn't exist
			if(!mkdir($this->get_logs_folder(),0777))
			{
				return false; //No folder and creation failed
			}
		}
		
		return true;
	}
}

?>
