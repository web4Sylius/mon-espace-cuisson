<?php
  include_once($rootpath.'class/articles/article_piano.php');

  function article_micro_ondes_fields($typearticle) {
    return (article_four_fields($typearticle));
  }
  function article_micro_ondes_show($tab, $title, $id) {
    return (article_four_show($tab, $title, $id));
  }
  function article_micro_ondes_update($tab, $title, $id) {
    return (article_four_update($tab, $title, $id));
  }
?>