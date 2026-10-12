var g_members;

$jq(document).ready(function() {
  g_members = $jq('#taskmbrs option'); // Save initial list
  $jq('#taskhidden').css({display:'inline'});

  $jq('.date').datepicker($jq.datepicker.regional['fr']);
  $jq('<select multiple="multiple">').attr({
    id: 'taskmbrs_select',
    name: 'members[]'
  }).appendTo('#taskadd form');
  $jq('#taskmbrs_select').css('display', 'none');
  //if ($jq('#taskshortdate').length)
    //$jq('#taskedate').hide();
  updateMembers();

  $jq('#taskmbrslist li').bind('click', confirmRemove); // Bind existing members

  $jq('#tasklinklabel').autocomplete({
    source: 'taskmanager/link_search.php',
    minLength: 2,
    select: function( event, ui ) {
      $jq('#tasklinkid').val(ui.item.id);
      $jq('#tasklinktype').val(ui.item.type);
      $jq('#tasklinkshowtype').text(ui.item.type);
    }
  })
  .data('ui-autocomplete')._renderItem = function(ul, item) {
    return $jq('<li>')
      .append('<a>'+item.label+'<br><span style="font-size:8px">'+item.type+'</span></a>')
      .appendTo(ul);
  };
});

$jq('#taskincharge').change(updateMembers);

$jq('#taskshortdate').change(function () {
  if ($jq('#taskshortdate :selected').text() == 'Autre')
    $jq('#taskedate').fadeIn();
  else
    $jq('#taskedate').fadeOut();
});

// Add member to the participants list
$jq('#taskmbrs').change(function() {
  var member = $jq('#taskmbrs :selected');
  var id = 'mbr_' + member.val();
  if ($jq('#' + id).length == 0) {
    $jq('#taskmbrslist').append(
      '<li id="' + id + '" style="display:none"><p>' + member.text() + '</p>' +
      '<span class="ui-icon ui-icon-trash"></li>'
    );
    $jq('#' + id).bind('click', confirmRemove);
    $jq('#' + id).slideDown(100);
  }
  updateMembers();
});

$jq('#tasktype').change(function() {
  var type =$jq('#tasktype :selected');
  if(type.val() == 'suivi'){
    $jq('#tski').css('display', 'inline');
    $jq('#tskb').css('display', 'none');
    $jq('#taskedate').fadeIn();
  }else{
    $jq('#tski').css('display', 'none');
    $jq('#tskb').css('display', 'inline');
    $jq('#taskedate').fadeOut();
	if($jq('#taskshortdate :selected').text() == 'Autre') $jq('#taskedate').fadeIn();
  }
})

// Remove participant to the list
function removeMember(member) {
  member.slideUp(100, function(){
    member.remove();
    updateMembers();
  });

}

// Rebuild members list
function updateMembers() {
  $jq('#taskmbrs').html(g_members);

  // Remove "in charge" member from participants
  var inCharge = $jq('#taskincharge :selected').val();
  $jq('#taskmbrs option[value="' + inCharge + '"]').remove();
  removeMember($jq('#mbr_' + inCharge));

  // Remove participants
  $jq('#taskmbrslist li').each(function(key, member) {
    $jq('#taskmbrs option[value="' + member.id.split('_')[1] + '"]').remove();
  });

  // Select first element
  $jq('#taskmbrs').val(0);

  // Disable select when no more participant can be added
  if ($jq('#taskmbrs option').length == 1)
    $jq('#taskmbrs').prop('disabled', 'disabled');
  else
    $jq('#taskmbrs').prop('disabled', false);
  rebuildMembersSelect();
}

function rebuildMembersSelect()
{
  $jq('#taskmbrs_select option').remove();
  $jq('#taskmbrslist li').each(function(key, member) {
    $jq('#taskmbrs_select').append('<option value="' + member.id.split('_')[1] + '" />');
    $jq('#taskmbrs_select option').prop('selected', true);
  });
  if($jq('#taskmbrslist li').length==0) {
    $jq('#taskmbrs_select').append('<option value="" />');
    $jq('#taskmbrs_select option').prop('selected', true);
  }
}

function confirmRemove(e) {
  var el = $jq(e.delegateTarget);
  $jq('<div>').appendTo('#taskadd')
  .html('<span class="ui-icon ui-icon-alert" style="float: left; margin: 0 7px 20px 0;"></span>'+
	'<div>Supprimer <strong>' + el.text() + '</strong> de la liste des intervenants ?</div>')
  .dialog({
    modal: true, title: 'Confirmez la suppression', zIndex: 1000, autoOpen: true,
    width: 'auto', resizable: false,
    buttons: {
      Oui: function () {
	removeMember(el);
	$jq(this).dialog("close");
      },
      Non: function () {
	$jq(this).dialog("close");
      }
    },
    close: function (event, ui) {
      $jq(this).remove();
    }
  });
}
