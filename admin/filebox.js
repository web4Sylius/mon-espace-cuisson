
var filebox=Class.create();

filebox.prototype={

    initialize: function(name,container){
        this.name=name;
        this.delurl=undefined;
        this.renameurl=undefined;
        this.selected=new Array();
        this.fileids=new Array();
        this.files=new Array();
        this.fileinfos=new Array();
        this.movefileid=undefined;
        this.selectmode=false;
        this.callback=undefined;
        this.parent=0;
        if(!document.getElementById('filebox')){
            var div=document.createElement('div');
            div.className="filebox";
            this.moveto=document.createElement('div');
            this.moveto.className="filemoveto";
            div.appendChild(this.moveto);
            
            this.title=document.createElement('div');
            this.title.className="fileboxtitle";
            this.title.innerHTML="Fichiers envoyés";
            div.appendChild(this.title);
            var bclose=document.createElement('img');
            bclose.className="fileboxclose";
            bclose.src="images/icon-x.svg";
            bclose.setAttribute("onclick",this.name+".hide()");
            div.appendChild(bclose);
            var liste=document.createElement('div');
            liste.className="fileboxlist";
            liste.onclick=function(){
                this.contextmenu();
            }.bind(this);
            liste.onscroll=function(){
                this.contextmenu();
            }.bind(this);
            div.appendChild(liste);
            this.filelist=liste;
            var iframe=document.createElement('iframe');
            iframe.className="fileboxupload";
            div.appendChild(iframe);
            this.iframe=iframe;
            this.selectbtn=document.createElement('input');
            this.selectbtn.className="fileboxselect";
            this.selectbtn.type="button";
            this.selectbtn.style.display="none";
            this.selectbtn.value="Sélectionner";
            this.selectbtn.onclick=this.returnfiles.bindAsEventListener(this);
            div.appendChild(this.selectbtn);
            this.newfolder=document.createElement('div');
            this.newfolder.className="newfolder";
            this.newfolder.setAttribute("onclick",this.name+".createdir()");
            div.appendChild(this.newfolder);
            div.setAttribute("onclick",this.name+".click()");
            var clear=document.createElement("div");
            clear.className="clear";
            div.appendChild(clear);

            if(container==undefined)
                document.body.appendChild(div);
            else
                container.appendChild(div);
            this.maindiv=div;
        }
    },
    
    show: function(dataurl){
        if(dataurl!=undefined) this.dataurl=dataurl;
        this.callback=undefined;
        this.maindiv.style.display="block";
        this.maindiv.style.left=Math.round(screen.width/2-200)+"px";
        this.maindiv.style.top=document.body.scrollTop+"px";
        this.selectmode=false;
        this.load();
    },
    
    hide: function(){
        this.maindiv.style.display="none";
        this.parent=0;
        if(this.selectmode && this.callback!=undefined){
            this.selected=new Array();
            this.callback(new Array());
        }
    },
    
    click: function(e){
        this.moveto.style.display="none";
    },
    
    showselect: function(dataurl,callback){
        if(dataurl!=undefined) this.dataurl=dataurl;
        if(callback!=undefined) this.callback=callback;
        this.maindiv.style.display="block";
        this.maindiv.style.position="absolute";
        console.log(this.maindiv.offsetWidth);
        this.maindiv.style.left=Math.round((screen.width-this.maindiv.offsetWidth)/2)+"px";
        this.maindiv.style.top=document.body.scrollTop+"px";
        this.selectmode=true;
        this.load();
    },
    
    upload: function(dataurl){
        this.uploadurl=dataurl;
        this.iframe.src=dataurl;
    // 	this.iframe.style.display=(dataurl==undefined?"none":"block");
    },
    
    chdir: function(id,tempurl){
        this.parent=id;
        this.load(tempurl);
    },
    
    load: function(tempurl,arg){
        this.filelist.innerHTML='<img src="images/loader.gif" class="loader">';
        if(this.dataurl==undefined && tempurl==undefined) return;
        if(tempurl!=undefined) this.dataurl=tempurl;
        if(arg==undefined) arg="";

        new Ajax.Request(this.dataurl+arg,{method:'get',
            onSuccess:this.load_cb.bindAsEventListener(this),
            onFailure:function(){ alert('Erreur') }
        });
    },

    load_cb: function (httpRequest){
        var xml=httpRequest.responseXML.documentElement;
        var html='';
        this.fileids=new Array();
        this.files=new Array();
        this.newfolder.style.display='none';
        this.title.innerHTML="";
        this.filelist.innerHTML="";
        this.fileinfos=undefined;
        if(xml.getElementsByTagName("fileinfo").length>0){
            this.fileinfos=document.createElement("select");
            var opt=document.createElement("option");
            opt.value="";
            opt.innerHTML="";
            this.fileinfos.appendChild(opt);
            for(i=0;i<xml.getElementsByTagName("fileinfo").length;i++){
                    var fi=xml.getElementsByTagName("fileinfo")[i];
                    var opt=document.createElement("option");
                    opt.value=fi.getAttribute("code");
                    opt.innerHTML=fi.getAttribute("name");
                    this.fileinfos.appendChild(opt);
            }
            this.fileinfos.onclick=function(e){
                e.stopPropagation();
            }
        }
        
        var col=0;
        var maxcols=Math.round(this.filelist.offsetWidth/110-1);
        if(xml.getElementsByTagName('path')){
            var nbpath=xml.getElementsByTagName('path').length;
            for(i=0;i<nbpath;i++){
                var f=xml.getElementsByTagName('path')[i];
                var title=f.getAttribute('name');
                var dataurl=f.getAttribute('dataurl').replace(/[\\']/g,'\\$&');
                var a=document.createElement('a');
                a.href="javascript:"+this.name+".chdir(0,\""+dataurl+"\")";
                a.innerHTML=title;
                this.title.appendChild(a);
                this.title.innerHTML+=" "+(i<nbpath-1?" &gt; ":"");
            }
        }
        if(xml.getElementsByTagName('parent')[0]){
            var f=xml.getElementsByTagName('parent')[0];
            var id=f.getAttribute('id');
            var dataurl=f.getAttribute('dataurl');
            var mod=f.getAttribute('mod');
            this.newfolder.style.display=(mod=="rw"?'block':'none');
            if(dataurl!=null){
                var goparent=document.createElement("div");
                goparent.id="parent";
                goparent.className="file";
                var goparenticon=document.createElement("div");
                goparenticon.className="folderup";
                goparent.appendChild(goparenticon);
                goparent.setAttribute("ondblclick",this.name+".chdir("+id+",'"+dataurl+"')");
                this.filelist.appendChild(goparent);
                col++;
            }
        }
        
        if(xml.getElementsByTagName('folder')){
            for(i=0;i<xml.getElementsByTagName('folder').length;i++){
                var f=xml.getElementsByTagName('folder')[i];
                var id=f.getAttribute('id');
                var dataurl=f.getAttribute('dataurl');
//                 if(dataurl!=null) dataurl="'"+dataurl.replace(/[\\']/g,'\\$&')+"'";
                var path="";
                var name=f.getAttribute('name');
                var mod=f.getAttribute('mod');
                var utilisateur=f.getAttribute('utilisateur');
                var date=f.getAttribute('date');
                var d=new Date();
                d.setTime(date*1000);
                date=d.getDate()+'/'+(1+d.getMonth())+'/'+d.getFullYear()+' '+d.getHours()+':'+(d.getMinutes()<10?"0":"")+d.getMinutes();
                
                var img="";
                if(f.getAttribute('img')!=null) img="<img src=\""+f.getAttribute('img')+"\" class=\"foldericon\">";
                var mouseover="onmouseover=\"document.getElementById('del"+id+"').style.display='block';\" onmouseout=\"document.getElementById('del"+id+"').style.display='none';\"";
                
                var divfolder=document.createElement("div");
                divfolder.id="fileid"+id;
                divfolder.className="file";
                var divfoldericon=document.createElement("div");
                divfoldericon.className="folder";
                divfoldericon.innerHTML=img;
                divfolder.appendChild(divfoldericon);
                divfolder.innerHTML+=name;
                divfolder.setAttribute("onclick",this.name+".select("+id+")");
                divfolder.setAttribute("ondblclick",this.name+".chdir("+id+",'"+dataurl+"')");
                divfolder.setAttribute("oncontextmenu",this.name+".contextmenu(this);return false;");
                divfolder.dataset.id=id;
                divfolder.dataset.name=name;
                divfolder.dataset.type="folder";
                divfolder.dataset.access=f.getAttribute('mod');
                divfolder.dataset.utilisateur=f.getAttribute('utilisateur');
                divfolder.dataset.size=f.getAttribute('size');
                divfolder.dataset.date=date;
                this.filelist.appendChild(divfolder);
                
                col++;
                if(col%maxcols==0){
                    var clear=document.createElement("div");
                    clear.style.clear="both";
                    this.filelist.appendChild(clear);
                }
            }
        }
        
        if(xml.getElementsByTagName('file')){
            for(i=0;i<xml.getElementsByTagName('file').length;i++){
                var f=xml.getElementsByTagName('file')[i];
                
                var id=f.getAttribute('id');
                var path=f.getAttribute('path');
                var name=f.getAttribute('name');
                var ext=f.getAttribute('ext');
                var date=f.getAttribute('date');
                var size=f.getAttribute('size');
                var infos=f.getAttribute("infos");
                var img=' ';
                if((ext=='jpg' || ext=='png') && size<1000000){
                    ext='img';
                    img='<img src=\"'+path+'\" class=\"thumb\">';
                }
                if(date!=""){
                    var d=new Date();
                    d.setTime(date*1000);
                    date=d.getDate()+'/'+(1+d.getMonth())+'/'+d.getFullYear()+' '+d.getHours()+':'+(d.getMinutes()<10?"0":"")+d.getMinutes();
                }

                this.fileids.push(id);
                this.files[id]={"id":id,"path":path,"name":f.getAttribute('name')};
//                 var mouseover="onmouseover=\"document.getElementById('del"+id+"').style.display='block';\" onmouseout=\"document.getElementById('del"+id+"').style.display='none';\"";
                var divfile=document.createElement("div");
                divfile.id="fileid"+id;
                divfile.className="file";
                var divicon=document.createElement("div");
                divicon.className="file"+ext;
                divicon.innerHTML=img;
                divfile.setAttribute("onclick",this.name+".select('"+id+"')");
                divfile.setAttribute("ondblclick","window.open('"+path+"')");
                divfile.setAttribute("oncontextmenu",this.name+".contextmenu(this);return false;");
                divfile.appendChild(divicon);
                divfile.innerHTML+=name;
                divfile.dataset.id=id;
                divfile.dataset.name=name;
                divfile.dataset.type="file";
                if(infos!=null){
                    divfile.dataset.infos=infos;
                    if(divfile.dataset.infos!=""){
                        var infos=document.createElement("span");
                        infos.className="infos";
                        infos.innerHTML=divfile.dataset.infos;
                        divfile.appendChild(infos);
                    }
                }
                divfile.dataset.access=f.getAttribute('mod');
                divfile.dataset.utilisateur=f.getAttribute('utilisateur');
                divfile.dataset.size=size;
                divfile.dataset.date=date;
                
                this.filelist.appendChild(divfile);
                col++
                if(col%maxcols==0){
                    var clear=document.createElement("div");
                    clear.style.clear="both";
                    this.filelist.appendChild(clear);
                }
                this.selectbtn.style.display=(this.selectmode?"inline":"none");
            }
        }
        if(xml.getElementsByTagName('upload')){
            var f=xml.getElementsByTagName('upload')[0];
            if(f!=undefined){
                var uploadurl=f.getAttribute('dataurl');
                this.iframe.src=uploadurl;
            }else
                this.iframe.src="";
        }
    },
  
    contextmenu: function(elem){
        this.filelist.querySelectorAll(".context").forEach(e => e.parentNode.removeChild(e));
        if(elem==undefined) return;
        
        var menu=document.createElement("div");
        menu.className="context";
        var pos=elem.getClientRects();
        menu.style.left=elem.offsetLeft;
        menu.style.top=elem.offsetTop-this.filelist.scrollTop;
        menu.innerHTML="";
        if(elem.dataset.utilisateur!="") menu.innerHTML+=elem.dataset.utilisateur+"<br>";
        if(elem.dataset.date!="") menu.innerHTML+="le "+elem.dataset.date+"<br>";
        if(elem.dataset.size>0){
            if(elem.dataset.size>1024000)
                menu.innerHTML+=(Math.round(elem.dataset.size/10240)/100)+' Mo';
            else
                menu.innerHTML+=(Math.round(elem.dataset.size/10.24)/100)+' ko';
        }
        if(elem.dataset.access=="rw"){
            var p=document.createElement("p");
            p.className="del";
            p.innerHTML="Supprimer";
            p.onclick=function(){ this.delfile(elem.dataset.id); }.bind(this);
            menu.appendChild(p);
            
            var p=document.createElement("p");
            p.className="rename";
            p.innerHTML="Renommer";
            p.onclick=function(){ this.renamefile(elem.dataset.id); }.bind(this);
            menu.appendChild(p);
            
            var p=document.createElement("p");
            p.className="move";
            p.innerHTML="Déplacer";
            p.onclick=function(){ this.showmove(elem.dataset.id); }.bind(this);
            menu.appendChild(p);
        }

        if(elem.dataset.type=="file" && this.fileinfos!=undefined){
            this.fileinfos.onchange=function(){
                this.fileinfo(elem.dataset.id,this.fileinfos.value);
            }.bind(this);
            this.fileinfos.value=elem.dataset.infos;
            var d=document.createElement("div");
            d.innerHTML="Type : ";
            d.appendChild(this.fileinfos);
            menu.appendChild(d);
        }
        if(menu.innerHTML=="") menu.innerHTML="Aucune info";
        this.filelist.appendChild(menu);
    },
    
    select: function(id){
        if(this.selected[id]==undefined) this.selected[id]=false;
        this.selected[id]=!this.selected[id];
        for(i=0;i<this.fileids.length;i++){
            var sel=this.fileids[i];
    // 	  if(sel!=id) this.selected[sel]=false;
            if(document.getElementById('fileid'+sel)) document.getElementById('fileid'+sel).style.background=(this.selected[sel]?"#AACCFF":"white");
        }
    },
  
    delfile: function (fid){
        if(confirm("Etes-vous sur de vouloir supprimer ce fichier ?")) this.load(this.dataurl,"&delfile=1&fid="+fid);
    },

    renamefile: function (fid){
        var old=document.getElementById('fileid'+fid).dataset.name;
        var newname=prompt('Nouveau nom de '+old+' :',old);
        if(newname==null || newname=='') return false;
        this.load(this.dataurl,"&fid="+fid+"&newname="+newname);
    },
  
    movefile: function(parent){
        if(this.movefileid==undefined) return;
        if(this.movefileid==parent){
            alert("Un dossier ne peut pas être déplacé à l'intérieur de lui même !");
            return;
        }
        this.load(this.dataurl,"&fid="+this.movefileid+"&moveto="+parent);
        this.moveto.style.display="none";
    },
    
    fileinfo: function(fid,info){
        this.load(this.dataurl,"&fid="+fid+"&info="+info);
    },
  
    showmove: function(id){
        this.movefileid=id;
        var f=document.getElementById('fileid'+id);
        var fbox=f.parentNode;
        this.moveto.style.left=f.offsetLeft+"px";
        this.moveto.style.top=(f.offsetTop-fbox.scrollTop)+"px";
        new Ajax.Request(this.dataurl+"&action=tree",{method:'get',
            onSuccess:this.tree_cb.bindAsEventListener(this),
            onFailure:function(){ alert('Erreur') }
        });
    },
  
    tree_cb: function(httpRequest){
        var xml=httpRequest.responseXML.documentElement;
        var html="Déplacer vers :";
        var arbo={};
        var foldernames=new Array();
        if(xml.getElementsByTagName('folder')){
            for(i=0;i<xml.getElementsByTagName('folder').length;i++){
                var f=xml.getElementsByTagName('folder')[i];
                var parentid=f.getAttribute('parent');
                if(arbo[parentid]==undefined) arbo[parentid]=new Array();
                arbo[parentid].push(f.getAttribute('id'));
                foldernames[f.getAttribute('id')]=f.getAttribute('name');
//         	    html+="<p class=\"movetofolder\"><img src=\"images/icon/folder.png\">"+f.getAttribute('name')+"</p>";
            }
        }

        html+=this.displaytree(-1,arbo,foldernames);
        this.moveto.style.display="block";
        this.moveto.innerHTML=html;
    },
  
    displaytree: function(parent,arbo,foldernames){
        if(arbo.length==0) return "<br>Impossible de déplacer cet élément";
        var ret="<ul class=\"movetofolder\">";
        for(x in arbo[parent]){
            if(arbo[parent].hasOwnProperty(x)) {
                ret+="<li onclick=\""+this.name+".movefile('"+arbo[parent][x]+"')\"><img src=\"images/icon/folder.png\">"+foldernames[arbo[parent][x]]+"</li>";
                if(arbo[parent][x]!=undefined) ret+=this.displaytree(arbo[parent][x],arbo,foldernames);
            }
        }
        ret+="</ul>";
        return ret;
    },
  
    returnfiles: function(){
        var r=new Array();
        for(i=0;i<this.fileids.length;i++){
            var fileid=this.fileids[i];
            if(this.selected[fileid]) r.push(this.files[fileid]);
        }
        this.hide();
        if(this.callback!=undefined) this.callback(r);
        return r;
    },
  
    createdir: function(){
        var name=prompt('Nom du dossier :','Nouveau dossier')
        if(name==null || name=='') return false;
        this.load(this.dataurl,"&newfolder&name="+name);
    },
 
    addslashes: function(txt) {
        return (txt + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
    }
};
