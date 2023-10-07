<? ob_start(); // No html and navigation frame neede here, done by serve.php ?>

<h1>Dashboard</h1>

<p>
  This is the dashboard with lots of dashes.
</p>

<?
  // Indicates that serve is directly called from a script.
  // For the keys/values see, serve.php, extend as needed.
  $GLOBALS["SERVE_META"] = array("title" => "Dashboard");
  require_once($_SERVER["DOCUMENT_ROOT"] . "/gp/serve.php");
?>