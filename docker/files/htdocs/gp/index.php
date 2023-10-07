<? ob_start(); ?>
<h1>Unauthorized</h1>
<?
  http_response_code(401);
  $GLOBALS["SERVE_META"] = array("title" => "Unauthorized");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/gp/serve.php");
?>
