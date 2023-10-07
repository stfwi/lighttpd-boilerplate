<?
ob_start();
$user = trim(isset($_GET["user"]) ? $_GET["user"] : "");
$pass = trim(isset($_GET["pass"]) ? $_GET["pass"] : "");
$text = "";
print("<h1>Example User File Entry Generator</h1>\n\n<pre>\n");

if(empty($user) && empty($pass)) {
  $text = <<<'HERE_TEXT'
    User file entry generator. This is for testing locally
    only, do not use that for production, NOT secure by design.

    Uses the command:

      htpasswd -n -B -b "{user}" "{pass}"

    where {user} and {pass} are specified directly here in URL:

      /gp/passwd.php?user=ENTERHERE&pass=ENTERHERETOO

    e.g. for user="pi" and pass="raspberry" (the probably most
    common user/pass around, a valid line would be:

      pi:$2y$05$TUshs3p2S9C7J2U4RyhBR.MXwMfshVGFuRGM6U26a6GewLV3L/8Di

    In this repository, the file to put this in is:

      docker/files/etc/lighttpd/lighttpd.user.htpasswd

    as configured in the lighttpd.conf.

HERE_TEXT;

} else if(empty($user) || empty($pass)) {
  $text = "You need to specify user and password.";
} else {
  $entry = shell_exec("htpasswd -n -B -b " . escapeshellarg($user) . " " . escapeshellarg($pass));
  $text = "And the entry for the user file is:\n\n    " . htmlspecialchars($entry) . "\n\n";
}

print($text);
$GLOBALS["SERVE_META"] = array("title" => "Example user file entry generator");
require_once($_SERVER["DOCUMENT_ROOT"] . "/gp/serve.php");
