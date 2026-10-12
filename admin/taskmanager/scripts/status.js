function show_status(sender) {

  var statuslist=document.getElementById('statuslist');
  if(statuslist.style.display=="block" || sender==document.body)
    statuslist.style.display='none';
  else
    statuslist.style.display='block';
  statuslist.style.left=sender.offsetLeft+'px';
  statuslist.style.top=(sender.offsetTop+sender.offsetHeight)+'px';
  
}


function update_state(newstate) {
    document.getElementsByName('status')[0].value=newstate;
    document.forms.change_state.submit();
}
