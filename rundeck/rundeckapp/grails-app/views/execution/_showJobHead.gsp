<%@ page import="com.dtolabs.rundeck.server.authorization.AuthConstants" %>
<g:if test="${scheduledExecution}">
    <div class="jobInfoSection">
        <span class="jobInfoPart secondary">
            <g:if test="${!groupOnly}">
            <g:link controller="scheduledExecution" action="show"
                    id="${scheduledExecution.extid}"
                    class=" ${execution?.status == 'true' ? 'jobok' : null == execution?.dateCompleted ? 'jobrunning' : execution?.cancelled ? 'jobwarn' : 'joberror'}" absolute="${absolute ? 'true' :'false'}"
                title="${scheduledExecution?.description.encodeAsHTML()}"
            >
                <span class="jobName">${scheduledExecution?.jobName.encodeAsHTML()}</span></g:link>

            </g:if>
            <g:if test="${scheduledExecution.groupPath && !nameOnly}">
            <span class="jobGroup">
                <span class="grouplabel">
                    <g:link controller="menu" action="jobs"
                            params="${[groupPath: scheduledExecution.groupPath]}"
                            title="${'View ' + g.message(code: 'domain.ScheduledExecution.title') + 's in this group'}"
                            absolute="${absolute ? 'true' : 'false'}">
                        <g:if test="${!noimgs}"><img
                                src="${resource(dir: 'images', file: 'icon-small-folder.png')}"
                                width="16px" height="15px" alt=""/></g:if>
                        ${scheduledExecution.groupPath.encodeAsHTML()}
                    </g:link>
                </span>
            </span>
            </g:if>
        </span>
        <g:if test="${!groupOnly && auth.jobAllowedTest(job: scheduledExecution, action: AuthConstants.ACTION_UPDATE)}">
            <g:link controller="scheduledExecution" title="Edit Job" action="edit" id="${scheduledExecution.extid}"
                class="action textbtn">edit</g:link>
        </g:if>
    </div>
</g:if>
