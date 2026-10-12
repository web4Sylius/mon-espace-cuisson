$jq(document).ready(function() {
     // Auto-submit form
     $jq('#menucat').bind('change', function() {
       $jq('#taskmenuaction').submit();
     });
     
     $jq('#menustatus').bind('change', function() {
       $jq('#taskmenuaction').submit();
     });
     
     $jq('#menutypes').bind('change', function() {
       $jq('#taskmenuaction').submit();
     });
     
     $jq('#menupriorite').bind('change', function() {
       $jq('#taskmenuaction').submit();
     });
     
     $jq('#menuown').bind('change', function() {
       $jq('#taskmenuaction').submit();
     });

     $jq('#menuresp').bind('change', function() {
       $jq('#taskmenuaction').submit();
     });
     
     $jq('#menuinterv').bind('change', function() {
       $jq('#taskmenuaction').submit();
     });
     
     $jq('#taskshowcloses').bind('click', function() {
       $jq('#taskmenuaction').submit();
     });
});

function activeOnglet(div)
{
    switch(div)
    {
        case 'taskownlist':

            jQuery('#tasklist').hide();
            jQuery('#taskstudentlist').hide()
            jQuery('#taskownlist').show();
            jQuery('#onglet_task').css({
                'color' : '#363e42',
                'background-color' : '#cccccc'
            });
            jQuery('#onglet_task_student').css({
                'color' : '#363e42',
                'background-color' : '#cccccc'
            });
            jQuery('#onglet_task_own').css({
                'color' : 'white',
                'background-color' : '#363e42'
            });

            break;

        case 'tasklist':

            jQuery('#taskownlist').hide();
            jQuery('#taskstudentlist').hide(); 
            jQuery('#tasklist').show();
            jQuery('#onglet_task').css({
                'color' : 'white',
                'background-color' : '#363e42'
            });
            jQuery('#onglet_task_own').css({
                'color' : '#363e42',
                'background-color' : '#cccccc'
            });
            
            break;
    }
}