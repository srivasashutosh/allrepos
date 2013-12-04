<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="layout" content="base"/>
    <meta name="tabpage" content="events"/>
    <g:ifServletContextAttribute attribute="RSS_ENABLED" value="true">
    <link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="${createLink(controller:"feed",action:"index",params:paginateParams?paginateParams:[:])}"/>
    </g:ifServletContextAttribute>
    <title><g:message code="gui.menu.Events"/> - ${session.project.encodeAsHTML()}</title>
    <g:javascript library="yellowfade"/>
    <g:javascript library="pagehistory"/>
    <g:javascript>
                

        var pagefirstload=true;
        function _pageUpdateNowRunning(count, perc) {
            document.title = "Now Running (" + count + ")";
            if($('nrlocal')){
                $('nrlocal').innerHTML = '' + count;
            }
            if(pagefirstload){
                pagefirstload=false;
                if(count > 0){
                    Expander.toggle('_exp_dashholder','dashholder');
                }
            }
        }
        function showError(message) {
            if ($('loaderror')) {
                $("loaderror").innerHTML += message;
                $("loaderror").show();
            }
        }

        var bfilters=${filterPref.encodeAsJSON()};
        <g:set var="pageparams" value="${[offset:params.offset,max:params.max]}"/>
        <g:set var="eventsparams" value="${paginateParams}"/>
        var eventsparams=${eventsparams.encodeAsJSON()};
        var pageparams=${pageparams.encodeAsJSON()};
        var autoLoad=${params.refresh == 'true' ? true : false};
        var links = {
            events:'${createLink(controller: "reports", action: "eventsFragment")}',
            nowrunning:'${createLink(controller: "menu", action: "nowrunningFragment")}',
            baseUrl:"${createLink(controller: "reports", action: "index")}"
        };
        var runupdate;
        function loadNowRunning(){
            runupdate=new Ajax.PeriodicalUpdater({ success:'nowrunning'},links.nowrunning,{
                evalScripts:true,
                parameters:eventsparams,
                frequency:5,
                onFailure:function (response) {
                    showError("AJAX error: Now Running [" + runupdate.url + "]: " + response.status + " "
                                      + response.statusText);
                    runupdate.stop();
                }
            });
        }
        /** START history
         *
         */
        var histControl = new HistoryControl('histcontent',{xcompact:true,nofilters:true});
        function loadHistory(){
            histControl.loadHistory( eventsparams );
        }
        function setAutoLoad(auto){
            autoLoad=auto;

            $$('input.autorefresh').each(function(e){
                e.checked=auto;
            });
        }
        function _pageInit() {
            try{
                if(pageparams && pageparams.offset){
                    Object.extend(eventsparams,pageparams);
                }
            }catch(e){
                console.log("error: "+e);
            }
            loadNowRunning();
            $$('input.autorefresh').each(function(e){
                var changeHandler=function(evt){
                    Object.extend(eventsparams,{refresh:e.checked});
                    autoLoad=e.checked;
                    url=_genUrl(links.baseUrl,eventsparams);
                    if(typeof(history.pushState)=='function'){
                        history.pushState(eventsparams, pageTitle, url);
                    }else{
                        document.location=url;
                    }
                    if(autoLoad){
                        loadHistory();
                        $('eventsCountBadge').hide();
                    }else{
                        _scheduleSinceCheck();
                    }
                };
                Event.observe(e,'change',  changeHandler);
                if(Prototype.Browser.IE
                    && $(e).tagName.toLowerCase()=='input'
                    && ($(e).type.toLowerCase()=='radio' ||$(e).type.toLowerCase()=='checkbox')){
                    Event.observe(e,'click',  changeHandler);
                }

            });
        }

        var lastRunExec = 0;
        var lastRunTime = 0;
        var checkUpdatedUrl='';
        var pageTitle="${g.message(code: 'gui.menu.Events').encodeAsJavaScript()} - ${session?.project.encodeAsJavaScript()}";
        var firstLoad=true;
        var firstparams=Object.extend({refresh:${params.refresh == 'true' ? true : false}},eventsparams);
        window.onpopstate = function(event) {
            if(firstLoad){
                firstLoad=false;
            }else if(event.state){
                Object.extend(eventsparams,event.state);
                setAutoLoad(event.state.refresh);
                loadHistory();
            }else {
                eventsparams=firstparams;
                setAutoLoad(eventsparams.refresh);
                loadHistory();
            }
        };
        function _updateBoxInfo(name, data) {
            if(name==='events' && data.total){
                $$('._obs_histtotal').each(function(e){
                    $(e).innerHTML=data.total;
                });
            }
            if(name=='events' && data.checkUpdatedUrl ){
                checkUpdatedUrl=data.checkUpdatedUrl;
                if(!autoLoad){
                    _updateEventsCount(0);
                    _scheduleSinceCheck();
                }
            }
            if(name==='events' && data.lastDate){
                histControl.setHiliteSince(data.lastDate);
            }
            if(name=='events' && data.rssUrl && $('rsslink')){
                $('rsslink').href=data.rssUrl;
                $$('link[rel="alternate"]').each(function(elem){$(elem).href=data.rssUrl});
            }
            if(name=='events' && data.total){
                var e = $('evtsholder').down('.paginate');
                if(e){
                    var pagefunc=function(e,params){
                        Object.extend(eventsparams,params);
                        loadHistory();
                        history.pushState(params, pageTitle, e.href);
                    };
                    paginate(e,data.offset,data.total,data.max,{
                        baseUrl:links.baseUrl,
                        'paginate.prev':"${g.message(code: 'default.paginate.prev',default:'Previous')}",
                        'paginate.next':"${g.message(code: 'default.paginate.next',default:'Next')}",
                        prevBehavior:pagefunc,
                        stepBehavior:pagefunc,
                        nextBehavior:pagefunc
                    });
                }
            }
            if (name == 'nowrunning' && data.lastExecId && data.lastExecId != lastRunExec) {
                lastRunExec = data.lastExecId;
                if(autoLoad){
                    loadHistory();
                }
            }
        }
        var sincechecktimer=null;
        function _scheduleSinceCheck(){
            if(sincechecktimer){
                clearTimeout(sincechecktimer);
            }
            sincechecktimer=setTimeout(_checkSince,5000);
        }
        function _checkSince(){
            var url=checkUpdatedUrl;
            new Ajax.Request(url, {
                 evalJSON:true,
                 onSuccess: function(transport) {
                     _checkSinceSuccess(transport);
                 },
                 onFailure: function() {
                 }
             });
            
        }
        function _checkSinceSuccess(response){
            var data=eval("("+response.responseText+")"); // evaluate the JSON;
            if(data && data.since){
                var count=data.since.count;
                //display badge
                _updateEventsCount(count);
            }else {
                showError(data.error && data.error.message? data.error.message : 'Invalid data response');
            }
            if(!autoLoad){
                _scheduleSinceCheck();
            }else{
                $('eventsCountBadge').hide();
            }
        }

        function _setFilterSuccess(response,name){
            var data=eval("("+response.responseText+")"); // evaluate the JSON;
            if(data){
                var bfilters=data['filterpref'];
                eventsparams={filterName:bfilters[name]};
                pageparams={filterName:bfilters[name]};
                loadHistory();
                //reload page
//                document.location="${createLink(controller:'reports',action:'index')}"+(bfilters[name]?"?filterName="+bfilters[name]:'');
            }
        }

        function _updateEventsCount(count){
            if(count>0){
                $('eventsCountContent').innerHTML=count+" new";
                $('eventsCountBadge').show();
            }else{
                $('eventsCountBadge').hide();
            }
        }
        
        Event.observe(window, 'load', _pageInit);
    </g:javascript>
    <style type="text/css">
    table.dashboxes td.dashbox {
        width: auto;
    }

    table.dashboxes td.dashbox.small {
        width: auto;
    }

    td.dashbox div.wbox {
        max-height: none;
        width: auto;
        height: auto;
    }

    td.dashbox.small div.wbox {
        width: auto;
    }
    span.action.large{
        font-size:12pt;
        clear:both;
    }
    span.action.large.closed{
        %{--background: #ddd url(<g:resource dir='images' file='bggrad-rev.png'/>) repeat-x 0px 0px;--}%
        background: #ddd ;
        -moz-border-radius: 3px;
        -webkit-border-radius: 3px;
        border:1px solid #aaa;
        margin:10px 5px 10px 0;

    }
    span.action.large.closed:hover{
        background: #eee;
    }
    div.expanderwrap {
        line-height:12pt;
        margin-top:10px;
    }
    </style>
</head>
<body>
<div>


<div class="pageBody">
    <g:render template="/common/messages"/>

    <div id="evtsholder" class="eventspage">
    <g:render template="eventsFragment" model="${[paginateParams:paginateParams,params:params,includeBadge:true,includeAutoRefresh:true,reports:reports,filterName:filterName, filtersOpen: true, includeNowRunning:true]}"/>
    </div>

    </div>
</div>
</body>
</html>
