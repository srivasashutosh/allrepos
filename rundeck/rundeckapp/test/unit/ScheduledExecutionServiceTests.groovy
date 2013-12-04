/*
 * Copyright 2010 DTO Labs, Inc. (http://dtolabs.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

import grails.test.GrailsUnitTestCase
import rundeck.JobExec
import rundeck.PluginStep
import rundeck.ScheduledExecution
import rundeck.CommandExec
import rundeck.Workflow
import rundeck.WorkflowStep
import rundeck.services.FrameworkService
import rundeck.services.ScheduledExecutionService

/*
* ScheduledExecutionServiceTests.java
*
* User: Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
* Created: Jul 29, 2010 4:38:24 PM
* $Id$
*/

public class ScheduledExecutionServiceTests extends GrailsUnitTestCase {


    private void assertParseParamNotifications(ArrayList<Map<String, Object>> expected, Map<String, Object> params) {
        def result = ScheduledExecutionService.parseParamNotifications(params)
        assertNotNull(result)
        assertEquals(expected, result)
    }
    public void testParseParamNotificationsSuccess() {
        assertParseParamNotifications(
                [[eventTrigger: 'onsuccess', type: 'email', content: 'c@example.com,d@example.com']],
                [notifyOnsuccess: 'true', notifySuccessRecipients: 'c@example.com,d@example.com']
        )
    }

    public void testParseParamNotificationsSuccessUrl() {
        assertParseParamNotifications(
                [[eventTrigger: 'onsuccess', type: 'url', content: 'http://blah.com']],
                [notifyOnsuccessUrl: 'true', notifySuccessUrl: 'http://blah.com']
        )
    }
    public void testParseParamNotificationsFailure() {
        assertParseParamNotifications(
                [[eventTrigger: 'onfailure', type: 'email', content: 'c@example.com,d@example.com']],
                [notifyOnfailure: 'true', notifyFailureRecipients: 'c@example.com,d@example.com']
        )
    }

    public void testParseParamNotificationsFailureUrl() {
        assertParseParamNotifications(
                [[eventTrigger: 'onfailure', type: 'url', content: 'http://blah.com']],
                [notifyOnfailureUrl: 'true', notifyFailureUrl: 'http://blah.com']
        )
    }
    public void testParseParamNotificationsStart() {
        assertParseParamNotifications(
                [[eventTrigger: 'onfailure', type: 'email', content: 'c@example.com,d@example.com']],
                [notifyOnfailure: 'true', notifyFailureRecipients: 'c@example.com,d@example.com']
        )
    }

    public void testParseParamNotificationsStartUrl() {
        assertParseParamNotifications(
                [[eventTrigger: 'onstart', type: 'url', content: 'http://blah.com']],
                [notifyOnstartUrl: 'true', notifyStartUrl: 'http://blah.com']
        )
    }
    public void testParseParamNotificationsSuccessPluginEnabled() {
        assertParseParamNotifications(
                [[eventTrigger: 'onsuccess', type: 'plugin1', configuration: [:]]],
                [
                        notifyPlugin: [
                                'success': [
                                        type: 'plugin1',
                                        enabled: [
                                                'plugin1': 'true'
                                        ],
                                        'plugin1': [
                                                config: [:]
                                        ]
                                ]
                        ],
                ]
        )
    }
    public void testParseParamNotificationsFailurePluginEnabled() {
        assertParseParamNotifications(
                [[eventTrigger: 'onfailure', type: 'plugin1', configuration: [:]]],
                [
                        notifyPlugin: [
                                'failure': [
                                        type: 'plugin1',
                                        enabled: [
                                                'plugin1': 'true'
                                        ],
                                        'plugin1': [
                                                config: [:]
                                        ]
                                ]
                        ],
                ]
        )
    }
    public void testParseParamNotificationsStartPluginEnabled() {
        assertParseParamNotifications(
                [[eventTrigger: 'onstart', type: 'plugin1', configuration: [:]]],
                [
                        notifyPlugin: [
                                'start': [
                                        type: 'plugin1',
                                        enabled: [
                                                'plugin1': 'true'
                                        ],
                                        'plugin1': [
                                                config: [:]
                                        ]
                                ]
                        ],
                ]
        )
    }
    public void testParseParamNotificationsSuccessPluginDisabled() {
        assertParseParamNotifications(
                [],
                [
                        notifyPlugin: [
                                'success': [
                                        type: 'plugin1',
                                        enabled: [
                                                'plugin1': 'false'
                                        ],
                                        'plugin1': [
                                                config: [:]
                                        ]
                                ]
                        ],
                ]
        )
    }
    public void testParseParamNotificationsSuccessPluginConfiguration() {
        assertParseParamNotifications(
                [[eventTrigger: 'onsuccess', type: 'plugin1', configuration: [a:'b',c:'def']]],
                [
                        notifyPlugin: [
                                'success': [
                                        type: 'plugin1',
                                        enabled: [
                                                'plugin1': 'true'
                                        ],
                                        'plugin1': [
                                                config: [a:'b',c:'def']
                                        ]
                                ]
                        ],
                ]
        )
    }
    public void testParseParamNotificationsSuccessPluginMultiple() {
        assertParseParamNotifications(
                [
                        [eventTrigger: 'onsuccess', type: 'plugin1', configuration: [a:'b',c:'def']],
                        [eventTrigger: 'onsuccess', type: 'plugin2', configuration: [g: 'h', i: 'jkl']]
                ],
                [
                        notifyPlugin: [
                                'success': [
                                        type: ['plugin1','plugin2'],
                                        enabled: [
                                                'plugin1': 'true',
                                                'plugin2': 'true'
                                        ],
                                        'plugin1': [
                                                config: [a:'b',c:'def']
                                        ],
                                        'plugin2': [
                                                config: [g:'h',i:'jkl']
                                        ]
                                ]
                        ],
                ]
        )
    }
    public void testParseNotificationsFromParamsSuccess() {
        def params = [
                notifyOnsuccess: 'true', notifySuccessRecipients: 'c@example.com,d@example.com',
        ]
        ScheduledExecutionService.parseNotificationsFromParams(params)
        assertNotNull(params.notifications)
        assertEquals([
            [eventTrigger: 'onsuccess', type: 'email', content: 'c@example.com,d@example.com'],
        ],params.notifications)
    }
    public void testParseNotificationsFromParamsFailure() {
        def params = [
                notifyOnfailure: 'true', notifyFailureRecipients: 'monkey@example.com',
        ]
        ScheduledExecutionService.parseNotificationsFromParams(params)
        assertNotNull(params.notifications)
        assertEquals([
                [eventTrigger: 'onfailure', type: 'email', content: 'monkey@example.com'],
        ],params.notifications)
    }
    public void testParseNotificationsFromParamsStart() {
        def params = [
                notifyOnstart: 'true', notifyStartRecipients: 'monkey@example.com',
        ]
        ScheduledExecutionService.parseNotificationsFromParams(params)
        assertNotNull(params.notifications)
        assertEquals([
                [eventTrigger: 'onstart', type: 'email', content: 'monkey@example.com'],
        ],params.notifications)
    }
    public void testGetGroups(){
        mockDomain(ScheduledExecution)
        def schedlist=[new ScheduledExecution(jobName:'test1',groupPath:'group1'),new ScheduledExecution(jobName:'test2',groupPath:null)]

        registerMetaClass(ScheduledExecution)
        ScheduledExecution.metaClass.static.findAllByProject={proj-> return schedlist}

        ScheduledExecutionService test = new ScheduledExecutionService()
        def fwkControl = mockFor(FrameworkService, true)

        fwkControl.demand.authResourceForJob{job->
            [type:'job',name:job.jobName,group:job.groupPath?:'']
        }
        fwkControl.demand.authResourceForJob{job->
            [type:'job',name:job.jobName,group:job.groupPath?:'']
        }
        fwkControl.demand.authorizeProjectResources{fwk,Set resset,actionset,proj->
            assertEquals 2,resset.size()
            def list = resset.sort{a,b->a.name<=>b.name}
            assertEquals([type:'job',name:'test1',group:'group1'],list[0])
            assertEquals([type:'job',name:'test2',group:''],list[1])
            
            assertEquals 1,actionset.size()
            assertEquals 'read',actionset.iterator().next()

            assertEquals 'proj1',proj

            return [[authorized:true,resource:list[0]],[authorized:false,resource:list[1]]]
        }
        test.frameworkService = fwkControl.createMock()
        def result=test.getGroups("proj1",null)
        assertEquals 1,result.size()
        assertEquals 1,result['group1']

    }

    void testClaimScheduledJobsUnassigned() {
        def (ScheduledExecution job1, String serverUUID2, ScheduledExecution job2, ScheduledExecution job3,
        ScheduledExecutionService testService, String serverUUID) = setupTestClaimScheduledJobs()

        assertEquals(null, job1.serverNodeUUID)
        assertEquals(serverUUID2, job2.serverNodeUUID)
        assertEquals(null, job3.serverNodeUUID)

        registerMetaClass(ScheduledExecution)
        ScheduledExecution.metaClass.static.withNewSession = { clos -> clos.call([:]) }
        def resultMap = testService.claimScheduledJobs(serverUUID)

        assertEquals(serverUUID, job1.serverNodeUUID)
        assertEquals(serverUUID2, job2.serverNodeUUID)
        assertEquals(null, job3.serverNodeUUID)

        assertTrue(resultMap[job1.extid])
        assertEquals(null, resultMap[job2.extid])
        assertEquals(null, resultMap[job3.extid])
    }

    void testClaimScheduledJobsFromServerUUID() {
        def (ScheduledExecution job1, String serverUUID2, ScheduledExecution job2, ScheduledExecution job3,
        ScheduledExecutionService testService, String serverUUID) = setupTestClaimScheduledJobs()

        assertEquals(job1, ScheduledExecution.lock(job1.id))
        assertEquals(job2, ScheduledExecution.lock(job2.id))
        assertEquals(job3, ScheduledExecution.lock(job3.id))
        assertEquals(null, job1.serverNodeUUID)
        assertEquals(serverUUID2, job2.serverNodeUUID)
        assertEquals(null, job3.serverNodeUUID)

        registerMetaClass(ScheduledExecution)
        ScheduledExecution.metaClass.static.withNewSession = { clos -> clos.call([:]) }

        def resultMap = testService.claimScheduledJobs(serverUUID, serverUUID2)

        assertEquals(null, job1.serverNodeUUID)
        assertEquals(serverUUID, job2.serverNodeUUID)
        assertEquals(null, job3.serverNodeUUID)

        assertEquals(null, resultMap[job1.extid])
        assertTrue(resultMap[job2.extid])
        assertEquals(null, resultMap[job3.extid])
    }

    private List setupTestClaimScheduledJobs() {
        mockDomain(ScheduledExecution)
        mockDomain(Workflow)
        mockDomain(CommandExec)
        mockLogging(ScheduledExecutionService)
        ScheduledExecutionService testService = new ScheduledExecutionService()
        def serverUUID = UUID.randomUUID().toString()
        def serverUUID2 = UUID.randomUUID().toString()
        ScheduledExecution job1 = new ScheduledExecution(
                jobName: 'blue',
                project: 'AProject',
                groupPath: 'some/where',
                description: 'a job',
                argString: '-a b -c d',
                workflow: new Workflow(keepgoing: true, commands:
                        [new CommandExec([adhocRemoteString: 'test buddy'])]),
                serverNodeUUID: null,
                scheduled: true
        )
        job1.save()
        ScheduledExecution job2 = new ScheduledExecution(
                jobName: 'blue2',
                project: 'AProject2',
                groupPath: 'some/where2',
                description: 'a job2',
                argString: '-a b -c d2',
                workflow: new Workflow(keepgoing: true, commands:
                        [new CommandExec([adhocRemoteString: 'test buddy2'])]),
                serverNodeUUID: serverUUID2,
                scheduled: true
        )
        job2.save()
        ScheduledExecution job3 = new ScheduledExecution(
                jobName: 'blue2',
                project: 'AProject2',
                groupPath: 'some/where2',
                description: 'a job2',
                argString: '-a b -c d2',
                workflow: new Workflow(keepgoing: true, commands:
                        [new CommandExec([adhocRemoteString: 'test buddy2'])]),
                scheduled: false,
        )
        job3.save()
        def map = [(job1.id): job1, (job2.id): job2, (job3.id): job3]
        registerMetaClass(ScheduledExecution)
        ScheduledExecution.metaClass.static.lock = { id ->
            println("lock for id ${id}")
            return map[id]
        }

        [job1, serverUUID2, job2, job3, testService, serverUUID]
    }

    public void testValidateWorkflow() {
        mockDomain(ScheduledExecution)
        mockDomain(Workflow)
        mockDomain(CommandExec)
        mockDomain(JobExec)
        mockDomain(PluginStep)
        mockLogging(ScheduledExecutionService)
        ScheduledExecutionService testService = new ScheduledExecutionService()

        def cmdExecProps = [adhocRemoteString: 'test buddy2']
        def jobrefWorkflowStepProps = [jobName: "name", jobGroup: "group"]
        def jobrefNodeStepProps = [jobName: "name2", jobGroup: "group2", nodeStep: true]
        def pluginNodeStepProps = [type: 'plug1', nodeStep: true,]
        def pluginWorkflowStepProps = [type: 'plug1', nodeStep: false]

        //simple
        assertValidateWorkflow([new CommandExec(cmdExecProps)], testService, true)

        //exec step cannot have a workflow step jobref error handler
        assertValidateWorkflow(
                [new CommandExec(cmdExecProps + [errorHandler: new JobExec(jobrefWorkflowStepProps)])],
                testService, false)

        //exec step can have a job ref (node step) error handler
        assertValidateWorkflow(
                [new CommandExec(cmdExecProps + [errorHandler: new JobExec(jobrefNodeStepProps)])],
                testService, true)

        //exec step cannot have a workflow step plugin error handler
        assertValidateWorkflow(
                [new CommandExec(cmdExecProps + [errorHandler: new PluginStep(pluginWorkflowStepProps)])],
                testService, false)

        //exec step can have a node step plugin error handler
        assertValidateWorkflow(
                [new CommandExec(cmdExecProps + [errorHandler: new PluginStep(pluginNodeStepProps)])],
                testService, true)

        //node step plugin cannot have a workflow step error handler
        assertValidateWorkflow(
                [new PluginStep(pluginNodeStepProps + [errorHandler: new JobExec(jobrefWorkflowStepProps)])],
                testService, false)

        //workflow step plugin can have a workflow step error handler
        assertValidateWorkflow(
                [new PluginStep(pluginWorkflowStepProps + [ errorHandler: new JobExec(jobrefWorkflowStepProps)])],
                testService, true)

        //job ref(workflow step) can have another as error handler
        assertValidateWorkflow(
                [new JobExec(jobrefWorkflowStepProps + [ errorHandler: new JobExec(jobrefWorkflowStepProps)])],
                testService, true)

        //job ref(workflow step) can have a plugin workflow step handler
        assertValidateWorkflow(
                [new JobExec(jobrefWorkflowStepProps + [ errorHandler: new PluginStep(pluginWorkflowStepProps)])],
                testService, true)

        //job ref(workflow step) can have a node step plugin erro handler
        assertValidateWorkflow(
                [new JobExec(jobrefWorkflowStepProps + [ errorHandler: new PluginStep(pluginNodeStepProps)])],
                testService, true)


    }

    private void assertValidateWorkflow(List<WorkflowStep> commands, ScheduledExecutionService testService, boolean valid) {
        def workflow = new Workflow(keepgoing: true, commands: commands, strategy: 'node-first')
        ScheduledExecution scheduledExecution = new ScheduledExecution(
                jobName: 'blue2',
                project: 'AProject2',
                groupPath: 'some/where2',
                description: 'a job2',
                argString: '-a b -c d2',
                workflow: workflow,
                scheduled: false,
        )
        assert valid == testService.validateWorkflow(workflow, scheduledExecution)
        assert !valid == scheduledExecution.hasErrors()
        assert !valid == scheduledExecution.errors.hasFieldErrors('workflow')
        assert !valid == workflow.commands[0].hasErrors()
        assert !valid == workflow.commands[0].errors.hasFieldErrors('errorHandler')
    }
}
