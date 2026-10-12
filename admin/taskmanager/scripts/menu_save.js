$jq(document).ready(function() {
  // Auto-submit form
  $jq('#menucat').bind('change', function() {
    $jq('#taskmenuaction').submit();
  });
  $jq('#menustatus').bind('change', function() {
    $jq('#taskmenuaction').submit();
  });
  $jq('#menumember').bind('change', function() {
    $jq('#taskmenuaction').submit();
  });
  $jq('#taskshowcloses').bind('click', function() {
    $jq('#taskmenuaction').submit();
  });
});
