<?
  // @ref: Uses https://github.com/michelf/php-markdown
  require_once(dirname(__FILE__)."/php/MarkdownExtra.inc.php");

  function markdown_text($md_text) {
    if((!is_string($md_text)) || empty(trim($md_text))) return "";
    $parser = new Michelf\MarkdownExtra;
    $parser->fn_id_prefix = "idpf-";
    return $parser->transform($md_text);
  }

  function markdown_file($path) {
    return markdown_text(file_get_contents($path));
  }
?>