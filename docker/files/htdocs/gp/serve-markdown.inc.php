<?
  // @ref: Uses https://github.com/michelf/php-markdown
  require_once(dirname(__FILE__)."/php/MarkdownExtra.inc.php");

  /**
   * Converts markdown to HTML, returns an
   * empty string on invalid input.
   * @param string $md_text
   * @return string
   */
  function markdown_text($md_text) {
    if((!is_string($md_text)) || empty(trim($md_text))) return "";
    $parser = new Michelf\MarkdownExtra;
    $parser->fn_id_prefix = "idpf-";
    return $parser->transform($md_text);
  }

  /**
   * Converts markdown file contents to HTML, returns
   * the HTML output or an empty string on invalid input.
   * @param string $path
   * @return string
   */
  function markdown_file($path) {
    return markdown_text(file_get_contents($path));
  }
?>