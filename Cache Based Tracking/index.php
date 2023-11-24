<?php 
	require("config.php");
	
	$sessionsdir = "sessions/";
	
	// An ETag was sent to the webserver
	if (!empty($_SERVER["HTTP_IF_NONE_MATCH"])) {
		$etag = substr(str_replace(".", "", str_replace("/", "", str_replace("\\", "", $_SERVER["HTTP_IF_NONE_MATCH"]))), 0, 18);
	}
	else {
		$etag = substr(sha1($secret . sha1($_SERVER["REMOTE_ADDR"]) . sha1($_SERVER["HTTP_USER_AGENT"])), 0, 18);
	}
	
	// Initialize a new or existing session given any etag
	function initsession($etag, $force_reinit = false) {
		global $session, $sessionsdir;
		if (!$force_reinit && file_exists($sessionsdir . $etag)) {
			$session = unserialize(file_get_contents($sessionsdir . $etag));
		}
		else {
			$session = array("visits" => 1, "last_visit" => time(), "your_string" => "");
		}
	}
	
	function updatesession() {
		global $session;
		$session["visits"] += 1;
		$session["last_visit"] = time();
	}
	
	function storesession($etag) {
		global $session, $sessionsdir;
		$fid = fopen($sessionsdir . $etag, "w");
		fwrite($fid, serialize($session));
		fclose($fid);
	}
	
	initsession($etag);
	
	if (isset($_GET["tracker"])) {
		if (empty($_SERVER["HTTP_IF_NONE_MATCH"])) {
			@unlink($sessionsdir . $etag);
			unset($session);
			initsession($etag);
		}
		updatesession();
		storesession($etag);
		header("Cache-Control: private, must-revalidate, proxy-revalidate");
		header("ETag: " . substr($etag, 0, 18)); 
		header("Content-type: image/jpeg");
		header("Content-length: " . filesize("fingerprinting.png"));
		readfile("fingerprinting.png");
		exit;
	}
	
	if (isset($_POST["newstring"])) {
		$session["your_string"] = substr(htmlentities($_POST["newstring"]), 0, 500);
		storesession($etag);
		header("Location: ./");
		exit;
	}
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Cache Tracking Demo</title>
		<style>
			body {
				font-family: Arial;
			}
		</style>
	</head>
	<body>
		<div style="width: 632px; font-size: 1em; margin: 0 auto 0 auto; margin-top: 40px;">
			<div style="float: right; margin-left: 15px;">
				<img src="tracker.jpg" />
			</div>
			<h2>Cache Tracking</h2>
			<form method="POST" action="./">
				<b>Number of visits:</b> <?php echo $session["visits"]; ?><br/>
				<br/>
				<b>Last visit:</b> <?php echo date("r", $session["last_visit"]); ?><br/>
				<br/>
				<b>Store Text Using the Text Box: </b><br/>
				<textarea name=newstring style="width: 632px;" rows=4><?php echo $session["your_string"]; ?></textarea><br/>
				(max. 350 characters)<br/>
				<input type=submit value=Store />
			</form>
			<hr/>
	</body>
</html>
