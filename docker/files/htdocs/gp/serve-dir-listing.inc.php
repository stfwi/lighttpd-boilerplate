<?

/**
 * Returns a human readable file size text
 * (in KB, MB, etc) from a numeric file
 * size in bytes.
 * @param int $size
 * @return string
 */
function pretty_filesize($size) {
  $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'KB', 'B');
  $total = count($sizes);
  while($total-- && ($size > 1024)) $size /= 1024;
  return sprintf('%.1f', $size) . $sizes[$total];
}

/**
 * Returns the filesystem path for a URL path
 * (relative to document root).
 * @param string $rpath
 * @return string
 */
function local_path($rpath) {
  return "/" . trim($_SERVER["DOCUMENT_ROOT"] . "/" . trim($rpath, "/"), "/");
}

/**
 * Converts a numeric unix timestamp into
 * an ISO date string YYYY-MM-DD hh:mm:ss.
 * @param int $ts
 * @return string
 */
function iso_datetime($ts) {
  if(!is_numeric($ts)) return htmlspecialchars($ts);
  return date("Y-m-d H:i:s", $ts);
}

/**
 * Collects and returns directory entry data
 * as assoc array.
 * @param string $path
 * @return array
 */
function file_listing($path) {
  $files = array();
  $all = array();
  $root = local_path($path);
  $docroot = $_SERVER["DOCUMENT_ROOT"];
  $rofs = strlen($docroot);
  foreach(array_filter(glob($root . "/*"), function($e){ return basename($e)[0] != "."; }) as $p) {
    $rpath = realpath($p);
    if(empty($rpath) || (!str_contains($p, $docroot))) continue;
    $rpath = substr($rpath, $rofs);
    if(is_dir($p)) {
      array_push($all, array(
        "name" => basename($rpath) . "/",
        "link" => $rpath,
        "type" => "directory",
        "size" => "-",
        "mtime" => iso_datetime(filemtime($p))
      ));
    } else {
      array_push($files, array(
        "name" => basename($rpath),
        "link" => $rpath,
        "type" => mime_content_type(basename($p)),
        "size" => pretty_filesize(filesize($p)),
        "mtime" => iso_datetime(filemtime($p))
      ));
    }
  }
  foreach ($files as $f) array_push($all, $f);
  if($path != "") array_unshift($all, array(
    "name" => "..",
    "link" => "../",
    "type" => "parent",
    "size" => "-",
    "mtime" => "-"
  ));
  return $all;
}

/**
 * Prints the given string HTML escaped.
 * @param string $s
 */
function esc($s) {
  if(empty($s)) {
    print("&nbsp;");
  } else {
    print(htmlspecialchars($s));
  }
}

/**
 * Main directory index composition.
 * Prints to output buffer, returns
 * meta data for the framing HTML in
 * serve.php.
 * @return array
 */
function serve_directory_index($path) {
  $path = ltrim($path, '/');
  if($_SERVER['PHP_SELF'] == ('/' . $path)) {
    header("Content-Type: text/plain", true, 404);
    print("Library, not to be invoked directly.");
    return;
  } else if(!is_dir(local_path($path))) {
    header("Content-Type: text/plain", true, 404);
    print("Not a directory: \"" . $path . "\"");
    return;
  }
  $meta = array(
    "html_headers" => array(
      '<link rel="stylesheet" type="text/css" href="/gp/css/dir-listing.css" />',
      '<meta name="viewport" content="initial-scale=1">'
    ),
    "title" => $_SERVER["SERVER_NAME"] . "/" . $path . " (directory)"
  );
  $items = file_listing($path);
  ?>
    <h2>Index of <?=$path?></h2>
    <div class="list">
      <table summary="Directory Listing" cellpadding="0" cellspacing="0">
        <thead>
          <tr>
            <th class="n">Name</th>
            <th class="m">Last Modified</th>
            <th class="s">Size</th>
            <th class="t">Type</th>
          </tr>
        </thead>
        <tbody><?
          foreach($items as $item) {
            print(($item["type"]=="directory") ? '<tr class="d">' : '<tr>'); ?>
              <td class="n"><a href="<?esc($item["link"]);?>"><?esc($item["name"]);?></a></td>
              <td class="m"><?esc($item["mtime"]);?></td>
              <td class="s"><?esc($item["size"]);?></td>
              <td class="t"><?esc($item["type"]);?></td>
            </tr>
            <?
          }
        ?></tbody>
      </table>
    </div>
    <div class="foot"></div>
    <script src="/gp/js/dir-listing.js"></script>
  <?
  return $meta;
}
?>
