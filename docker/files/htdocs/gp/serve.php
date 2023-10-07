<?

/**
 * Returns the MIME type for a file path, or
 * "application/octet-stream" for invalid/unknown
 * types.
 * @param string $path
 * @return string
 */
if(!function_exists('mime_content_type')) {
  $_SERVER["MIME_CONTENT_TYPES"] = array('ai'=>'application/postscript', 'bmp'=>'image/bmp', 'cab'=>'application/vnd.ms-cab-compressed', 'css'=>'text/css', 'doc'=>'application/msword', 'eps'=>'application/postscript', 'exe'=>'application/x-msdownload', 'flv'=>'video/x-flv', 'gif'=>'image/gif', 'htm'=>'text/html', 'html'=>'text/html', 'ico'=>'image/vnd.microsoft.icon', 'jpe'=>'image/jpeg', 'jpeg'=>'image/jpeg', 'jpg'=>'image/jpeg', 'js'=>'application/javascript', 'json'=>'application/json', 'mov'=>'video/quicktime', 'mp3'=>'audio/mpeg', 'msi'=>'application/x-msdownload', 'ods'=>'application/vnd.oasis.opendocument.spreadsheet', 'odt'=>'application/vnd.oasis.opendocument.text', 'pdf'=>'application/pdf', 'php'=>'text/html', 'png'=>'image/png', 'ppt'=>'application/vnd.ms-powerpoint', 'ps'=>'application/postscript', 'psd'=>'image/vnd.adobe.photoshop', 'qt'=>'video/quicktime', 'rar'=>'application/x-rar-compressed', 'rtf'=>'application/rtf', 'svg'=>'image/svg+xml', 'svgz'=>'image/svg+xml', 'swf'=>'application/x-shockwave-flash', 'tif'=>'image/tiff', 'tiff'=>'image/tiff', 'txt'=>'text/plain', 'xls'=>'application/vnd.ms-excel', 'xml'=>'application/xml', 'zip'=>'application/zip');
  function mime_content_type($path) {
    $ext = explode('.', $path);
    $ext = strtolower(array_pop($ext));
    return (array_key_exists($ext, $_SERVER["MIME_CONTENT_TYPES"])) ? $_SERVER["MIME_CONTENT_TYPES"][$ext] : 'application/octet-stream';
  }
}

/**
 * Returns a resolved path without that the file or
 * directory has to exist (like realpath).
 * @param string $path
 * @return string
 */
function absolute_path($path) {
  $path = preg_replace("/[\?#].*$/", "", preg_replace("/^\/+/", "", str_replace('\\', '/', $path)));
  $abs = array();
  foreach(array_filter(explode('/', $path), 'strlen') as $part) {
    if ('.' == $part) continue;
    if ('..' == $part) { array_pop($abs); continue; }
    $abs[] = $part;
  }
  return "/" . trim(implode("/", $abs));
}

/**
 * JSON pretty serialization without unicode or slah escaping.
 * @param mixed $o
 * @return string
 */
function to_json($o) {
  return json_encode($o, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
}

/**
 * Returns the extension of a given path, or an empty
 * string if no dot "." is contained in the path.
 * @param mixed $path
 * @return string
 */
function file_extension($path) {
  $path = array_filter(explode(".", basename($path)), function($s){ return strlen($s)>0; });
  return (count($path)<2) ? "" : strtolower(array_pop($path));
}

/**
 * Main serve function, returns contents and meta data
 * for generating the HTML using conventional PHP tagged
 * HTML below.
 * @return array
 */
function serve() {
  $rpath = absolute_path(rawurldecode($_SERVER["REQUEST_URI"]));
  $path = $_SERVER["DOCUMENT_ROOT"] . "/" . trim($rpath, "/ ");
  if($rpath == "/gp/serve.php") {
    header('Location: /');
    exit(0);
  } else if(isset($GLOBALS["SERVE_META"])) {
    if(ob_get_level() <= 0) {
      http_response_code(500);
      on_start();
      print("Internal error: Invoking script did not enable output buffering.");
    }
  } else if(is_file($path)) {
    if(file_extension($path) == "md") {
      require_once("serve-markdown.inc.php");
      print(markdown_file($path));
    } else {
      header("Content-Type: " . mime_content_type($path), true, 200);
      readfile($path);
      exit(0);
    }
  } else if(is_dir($path)) {
    if(is_file($path."/index.md")) {
      require_once("serve-markdown.inc.php");
      print(markdown_file($path."/index.md"));
    } else {
      ob_start();
      header("Content-Type: text/html", true, 200);
      require_once("serve-dir-listing.inc.php");
      $data = serve_directory_index($rpath);
    }
  } else {
    http_response_code(404);
    ob_start();
    print("<h2>Not found</h2>\nCould not find any resource for \"$rpath\".");
  }
  if(array_key_exists("SERVE_META", $GLOBALS)) $data = $GLOBALS["SERVE_META"]; // If explicitly called from other scripts (ob_start(), print, require(serve.php))
  if(empty($data) || !is_array($data)) $data = array();
  $data["content"] = ob_get_clean();
  $data["path"] = trim($rpath, "/");
  if(empty($data["html_headers"])) $data["html_headers"] = array();
  if(empty($data["title"])) $data["title"] = "[" . $_SERVER["SERVER_NAME"] . "/" .  $data["path"] . "]";
  return $data;
}


$data = serve();

?><!doctype html><html><head>
  <title><? print(htmlspecialchars($data["title"])) ?></title>
  <meta charset="utf-8" />
  <link rel="stylesheet" type="text/css" href="/gp/css/common.css" />
  <script src="/gp/js/libs.js"></script>
  <script src="/gp/js/navigation.js"></script>
  <? foreach($data["html_headers"] as $e) print("  " . $e . "\n"); ?>
</head><body>
  <nav class="main-menu"></nav>
  <? print($data["content"]); ?>
</body></html>
