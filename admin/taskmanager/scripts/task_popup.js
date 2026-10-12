jQuery(document).ready(function() {
  jQuery('#task_assoc').autocomplete({
    source: 'taskmanager/link_search.php',
    minLength: 2,
    select: function( event, ui ) {
      jQuery('#popuplinkid').val(ui.item.id);
      jQuery('#popuplinktype').val(ui.item.type);
      jQuery('#popuptype').text(ui.item.type);
    }
  })
  .data('ui-autocomplete')._renderItem = function(ul, item) {
    return jQuery('<li>')
      .append('<a>'+item.label+'<br><span style="font-size:10px;">'+item.type+'</span></a>')
      .appendTo(ul);
  };
});