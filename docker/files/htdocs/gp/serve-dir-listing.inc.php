<?

function pretty_filesize($size, $precision=0) {
  $sizes = array('YB', 'ZB', 'EB', 'PB', 'TB', 'GB', 'MB', 'KB', 'B');
  $total = count($sizes);
  while($total-- && ($size > 1024)) $size /= 1024;
  return sprintf('%.'.$precision.'f', $size) . $sizes[$total];
}

function local_path($rpath) {
  return "/" . trim($_SERVER["DOCUMENT_ROOT"] . "/" . trim($rpath, "/"), "/");
}

function iso_datetime($ts) {
  if(!is_numeric($ts)) return htmlspecialchars($ts);
  return date("Y-m-d H:i:s", $ts);
}

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

function esc($s) {
  if(empty($s)) {
    print("&nbsp;");
  } else {
    print(htmlspecialchars($s));
  }
}

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
