<?php
define('PATH', dirname(realpath(__FILE__)));

// Define Constants to Connect to Source Forge
define('WURFL_RSS', 'http://sourceforge.net/api/file/index/project-id/55408/mtime/desc/limit/20/rss');
define('WURFL_REGEX', '|http://sourceforge\.net/projects/wurfl/files/WURFL/([0-9\.]+)/wurfl-.+\.zip/download|ie');
define('WURFL_MIN_VERSION', '2.3.4');
define('WURFL_TIMESTAMP', round(microtime(true)));
define('WURFL_MIRROR', 'autoselect');

// Define Log Paths
define('WURFL_FILE_VERSION', PATH.'/logs/wurfl_version.log');
define('WURFL_FILE_UPDATES', PATH.'/logs/wurfl_updates.log');
define('WURFL_FILE_ERRORS', PATH.'/logs/wurfl_errors.log');
define('WURFL_FILE_LOG', PATH.'/logs/wurfl.log');

// Define WURL API Data
define('WURFL_API_HOST', 'http://'.$_SERVER['SERVER_NAME'].str_replace('/update.php', '', $_SERVER['PHP_SELF']));
define('WURFL_API_DATA_PATH', PATH.'/data/wurfl.xml');
define('WURFL_API_UPDATE', WURFL_API_HOST.'/admin/updatedb.php?source=local');
define('WURFL_API_CLEAR_CACHE', WURFL_API_HOST.'/admin/updatedb.php?action=clearCache');

// REGEX WURFL_RSS for WURFL_REGEX to look for most recent WURFL version
preg_match_all(WURFL_REGEX, file_get_contents(WURFL_RSS), $out, PREG_SET_ORDER);

// Figure out new Version Number
$new_version = (count($out) > 0) ? $out[0][1] : WURFL_MIN_VERSION;

// Check WURFL_FILE_VERSION for what the last version we installed was
$current_version = (file_exists(WURFL_FILE_VERSION)) ? file_get_contents(WURFL_FILE_VERSION) : null;

// Cleanup
unset($out);

// Check if the New Version of WURFL is newer than what we have logged in WURFL_FILE_VERSION
if($new_version !== $current_version)
{
	// Generate a Download Link based on the version number we got back
	$download_link = "http://downloads.sourceforge.net/project/wurfl/WURFL/{$new_version}/wurfl-{$new_version}.zip?r=&ts=" . WURFL_TIMESTAMP . "&use_mirror=" . WURFL_MIRROR;

	// Try to download WURFL Zip File
	$remote_zip = file_get_contents($download_link);
	if($remote_zip)
	{
		// Name for local file
		$file = PATH."/downloads/wurfl-{$new_version}.zip";

		// Store Zip File Locally
		$fp = fopen($file, "w");
		fwrite($fp, $remote_zip);
		fclose($fp);

		// Make sure Zip File was Downloaded
		if(file_exists($file))
		{
			$wurfl_file = PATH.'/downloads/wurfl.xml';

			// Remote previous wurfl.xml file
			if(file_exists($wurfl_file))
			{
				unlink($wurfl_file);
			}

			// get the absolute path to $file
			$path = pathinfo(realpath($file), PATHINFO_DIRNAME);

			// Unzip WURFL Zip File
			$zip = new ZipArchive;
			$res = $zip->open($file);
			if ($res === TRUE)
			{
				// extract it to the path we determined above
				$zip->extractTo($path);
				$zip->close();

				if(file_exists($wurfl_file))
				{
					// Delete the old WURFL File
					unlink(WURFL_API_DATA_PATH);

					// Move unzipped file to main location
					rename($wurfl_file, WURFL_API_DATA_PATH);

					// Update API
					file_get_contents(WURFL_API_UPDATE);
					file_get_contents(WURFL_API_CLEAR_CACHE);

					// Log the Results
					file_put_contents(WURFL_FILE_VERSION, $new_version);
					file_put_contents(WURFL_FILE_UPDATES, 'Updated to version '.$new_version.' ( '.date('Y-m-d H:i:s')." )\r\n", FILE_APPEND | LOCK_EX);
				}
			}
			else
			{
				file_put_contents(WURFL_FILE_ERRORS, 'Failed to update from version '. $current_version .' to '.$new_version.' ( '.date('Y-m-d H:i:s')." )\r\n", FILE_APPEND | LOCK_EX);
			}

			// Cleanup
			unlink($file);
			unset($zip);
			unset($res);
		}
		else
		{
			file_put_contents(WURFL_FILE_ERRORS, 'Download Failed. Failed to update from version '. $current_version .' to '.$new_version.' ( '.date('Y-m-d H:i:s')." )\r\n", FILE_APPEND | LOCK_EX);
		}

		// Cleanup
		unset($remote_zip);
		unset($fp);
	}
}
// We're already up to date
else
{
	file_put_contents(WURFL_FILE_LOG, 'WURFL is up to date. Already on version '.$new_version.' ( '.date('Y-m-d H:i:s')." )\r\n", FILE_APPEND | LOCK_EX);
}
 | LOCK_EX);
}
