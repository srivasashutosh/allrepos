package rundeck.filters

import org.apache.log4j.Logger
import org.apache.log4j.MDC
import org.codehaus.groovy.grails.web.util.WebUtils

import javax.servlet.http.HttpServletRequest
import javax.servlet.http.HttpServletResponse

class AA_TimerFilters {
    static final Logger logger = Logger.getLogger('org.rundeck.web.requests')
    static requests=[:]
    public static final String _TIMER = 'AA_TimerFilters._TIMER'
    public static final String _TIMER_ITEM = 'AA_TimerFilters._timer_item'
    public static final String _REPORTS = 'AA_TimerFilters._reports'
    public static final String _REQ_URI = 'AA_TimerFilters._req_uri'

    /**
     * Mark recording request for ident
     * @param request
     * @param ident
     * @return
     */
    static record(request, ident) {
        if(!request[_REPORTS]){
            request[_REPORTS]=new HashSet()
        }
        request[_REPORTS] << ident
    }

    static report(request,Closure clos) {
        request[_REPORTS].each{ident->
            clos.call([id:ident,duration: request[_TIMER_ITEM]?.get(ident) ?: 0])
        }
        request[_REPORTS]=null
    }

    /**
     * Record duration for an ident
     * @param request
     * @param ident
     * @param ms
     * @return
     */
    static time(request, ident,long ms){
        if (!request[_TIMER_ITEM]) {
            request[_TIMER_ITEM] = [(ident):ms]
        }else{
            request[_TIMER_ITEM][ident]= ms
        }
        record(request,ident)
    }
    static clear(){
    }
    def filters = {
        all(controller:'*', action:'*') {
            before = {
                request[_TIMER]=System.currentTimeMillis()
                def ident= (request.getAttribute(WebUtils.FORWARD_REQUEST_URI_ATTRIBUTE) ?: request.getRequestURI())
                request[_REQ_URI]=ident
                record(request, ident)
            }
        }
    }

    static def afterRequest(HttpServletRequest request,HttpServletResponse response,session) {
        def duration = System.currentTimeMillis() - request[_TIMER]
        time(request, request[_REQ_URI], duration)
        report(request) {Map data->
            def map = [uri: data.id,
                    duration: data.duration,
                    remoteUser: request.remoteUser ?: request.authenticatedUser,
                    remoteHost: request.remoteHost,
                    userAgent: request.getHeader('User-Agent') ?: '-',
                    authToken: (request.authenticatedToken ? 'token' : 'form'),
                    method: request.method,
                    secure: request.isSecure() ? 'https' : 'http',
                    contentType: response.isCommitted()?response.getContentType():null,
                    project: session.project
            ]
            map.findAll {it.value!=null}.each{ MDC.put(it.key,it.value)}
            try{
                logger.info(map.toString())
            }finally {
                MDC.clear()
            }
        }
    }
}
