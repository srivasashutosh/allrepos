/*
 * Copyright 2011 DTO Solutions, Inc. (http://dtosolutions.com)
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

/*
* BaseWorkflowStrategy.java
* 
* User: Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
* Created: Aug 26, 2010 2:19:17 PM
* $Id$
*/
package com.dtolabs.rundeck.core.execution.workflow;

import com.dtolabs.rundeck.core.Constants;
import com.dtolabs.rundeck.core.common.Framework;
import com.dtolabs.rundeck.core.common.INodeEntry;
import com.dtolabs.rundeck.core.common.SelectorUtils;
import com.dtolabs.rundeck.core.execution.*;
import com.dtolabs.rundeck.core.execution.dispatch.DispatcherException;
import com.dtolabs.rundeck.core.execution.dispatch.DispatcherResult;
import com.dtolabs.rundeck.core.execution.workflow.steps.FailureReason;
import com.dtolabs.rundeck.core.execution.workflow.steps.NodeDispatchStepExecutor;
import com.dtolabs.rundeck.core.execution.workflow.steps.StepException;
import com.dtolabs.rundeck.core.execution.workflow.steps.StepExecutionResultImpl;
import com.dtolabs.rundeck.core.execution.workflow.steps.StepFailureReason;
import com.dtolabs.rundeck.core.execution.workflow.steps.StepExecutionResult;
import com.dtolabs.rundeck.core.execution.workflow.steps.node.NodeStepResult;

import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.HashSet;
import java.util.List;
import java.util.Map;

/**
 * BaseWorkflowStrategy is ...
 *
 * @author Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
 * @version $Revision$
 */
public abstract class BaseWorkflowStrategy implements WorkflowStrategy {
    final Framework framework;

    public BaseWorkflowStrategy(final Framework framework) {
        this.framework = framework;
    }

    static class BaseWorkflowExecutionResult implements WorkflowExecutionResult {
        private final List<StepExecutionResult> results;
        private final Map<String, Collection<StepExecutionResult>> failures;
        private final Map<Integer, StepExecutionResult> stepFailures;
        private final boolean success;
        private final Exception orig;

        public BaseWorkflowExecutionResult(List<StepExecutionResult> results,
                                       Map<String, Collection<StepExecutionResult>> failures,
                                       final Map<Integer, StepExecutionResult> stepFailures,
                                       boolean success, Exception orig) {
            this.results = results;
            this.failures = failures;
            this.stepFailures=stepFailures;
            this.success = success;
            this.orig = orig;
        }

        public List<StepExecutionResult> getResultSet() {
            return results;
        }

        public Map<String, Collection<StepExecutionResult>> getNodeFailures() {
            return failures;
        }

        public boolean isSuccess() {
            return success;
        }

        public Exception getException() {
            return orig;
        }

        @Override
        public String toString() {
            return "[Workflow "
//                   + (null != getResultSet() && getResultSet().size() > 0 ? "results: " + getResultSet() : "")
                   + (null != getStepFailures() && getStepFailures().size() > 0 ? "step failures: " + getStepFailures() : "")
                   + (null != getNodeFailures() && getNodeFailures().size() > 0 ? ", Node failures: "
                                                                                        + getNodeFailures() : "")
                   + (null != getException() ? ": exception: " + getException() : "")
                   + "]";
        }

        public Map<Integer, StepExecutionResult> getStepFailures() {
            return stepFailures;
        }
    }

    public final WorkflowExecutionResult executeWorkflow(final StepExecutionContext executionContext,
                                                         final WorkflowExecutionItem item) {

        final WorkflowExecutionListener wlistener = getWorkflowListener(executionContext);
        if (null != wlistener && !StepFirstWorkflowStrategy.isInnerLoop(item)) {
            wlistener.beginWorkflowExecution(executionContext, item);
        }
        WorkflowExecutionResult result = null;
        try {
            result = executeWorkflowImpl(executionContext, item);
        } finally {
            if (null != wlistener && !StepFirstWorkflowStrategy.isInnerLoop(item)) {
                wlistener.finishWorkflowExecution(result, executionContext, item);
            }
        }
        return result;
    }

    protected WorkflowExecutionListener getWorkflowListener(final ExecutionContext executionContext) {
        WorkflowExecutionListener wlistener = null;
        final ExecutionListener elistener = executionContext.getExecutionListener();
        if (null != elistener && elistener instanceof WorkflowExecutionListener) {
            wlistener = (WorkflowExecutionListener) elistener;
        }
        return wlistener;
    }

    public abstract WorkflowExecutionResult executeWorkflowImpl(StepExecutionContext executionContext,
                                                                WorkflowExecutionItem item);

    /**
     * Execute a workflow item, returns true if the item succeeds.  This method will throw an exception if the workflow
     * item fails and the Workflow is has keepgoing==false.
     *
     * @param failedMap  List to add any messages if the item fails
     * @param c          index of the WF item
     * @param cmd        WF item descriptor
     * @return true if the execution succeeds, false otherwise
     *
     */
    protected StepExecutionResult executeWFItem(final StepExecutionContext executionContext,
                                                final Map<Integer, StepExecutionResult> failedMap,
                                                final int c,
                                                final StepExecutionItem cmd) {

        if (null != executionContext.getExecutionListener()) {
            executionContext.getExecutionListener().log(Constants.DEBUG_LEVEL,
                                                        c + ": Workflow step executing: " + cmd);
        }
        StepExecutionResult result;
        try {
            result = framework.getExecutionService().executeStep(
                ExecutionContextImpl.builder(executionContext).stepNumber(c).build(),
                cmd);
            if (!result.isSuccess()) {
                failedMap.put(c, result);
            }
        } catch (StepException e) {
            result = StepExecutionResultImpl.wrapStepException(e);
            failedMap.put(c, result);
        }
        if (null != executionContext.getExecutionListener()) {
            executionContext.getExecutionListener().log(Constants.DEBUG_LEVEL,
                                                        c + ": Workflow step finished, result: " + result);
        }
        return result;
    }


    /**
     * Execute the sequence of ExecutionItems within the context, and with the given keepgoing value, return true if
     * successful
     */
    protected boolean executeWorkflowItemsForNodeSet(final StepExecutionContext executionContext,
                                                     final Map<Integer, StepExecutionResult> failedMap,
                                                     final List<StepExecutionResult> resultList,
                                                     final List<StepExecutionItem> iWorkflowCmdItems,
                                                     final boolean keepgoing)  {
        return executeWorkflowItemsForNodeSet(executionContext, failedMap, resultList, iWorkflowCmdItems, keepgoing,
                                              executionContext.getStepNumber());
    }
    /**
     * Execute the sequence of ExecutionItems within the context, and with the given keepgoing value, return true if
     * successful
     */
    protected boolean executeWorkflowItemsForNodeSet(final StepExecutionContext executionContext,
                                                     final Map<Integer, StepExecutionResult> failedMap,
                                                     final List<StepExecutionResult> resultList,
                                                     final List<StepExecutionItem> iWorkflowCmdItems,
                                                     final boolean keepgoing,
                                                     final int beginStepIndex) {

        boolean workflowsuccess = true;
        final WorkflowExecutionListener wlistener = getWorkflowListener(executionContext);
        int c = beginStepIndex;
        for (final StepExecutionItem cmd : iWorkflowCmdItems) {
            boolean stepSuccess=false;
            if (null != wlistener) {
                wlistener.beginWorkflowItem(c, cmd);
            }

            //wrap node failed listener (if any) and capture status results
            NodeRecorder stepCaptureFailedNodesListener = new NodeRecorder();
            StepExecutionContext stepContext = replaceFailedNodesListenerInContext(executionContext,
                stepCaptureFailedNodesListener);
            Map<String,NodeStepResult> nodeFailures;

            //execute the step item, and store the results
            StepExecutionResult stepResult=null;
            Map<Integer, StepExecutionResult> stepFailedMap = new HashMap<Integer, StepExecutionResult>();
            stepResult = executeWFItem(stepContext, stepFailedMap, c, cmd);
            stepSuccess = stepResult.isSuccess();
            nodeFailures = stepCaptureFailedNodesListener.getFailedNodes();

            if(null!=executionContext.getExecutionListener() && null!=executionContext.getExecutionListener().getFailedNodesListener()) {
                executionContext.getExecutionListener().getFailedNodesListener().matchedNodes(
                    stepCaptureFailedNodesListener.getMatchedNodes());

            }

            try {
                if(!stepSuccess && cmd instanceof HasFailureHandler) {
                    final HasFailureHandler handles = (HasFailureHandler) cmd;
                    final StepExecutionItem handler = handles.getFailureHandler();
                    if (null != handler) {
                        //if there is a failure, and a failureHandler item, execute the failure handler
                        //set keepgoing=false, and store the results
                        //will throw an exception on failure because keepgoing=false

                        NodeRecorder handlerCaptureFailedNodesListener = new NodeRecorder();
                        StepExecutionContext handlerExecContext = replaceFailedNodesListenerInContext(executionContext,
                            handlerCaptureFailedNodesListener);

                        //if multi-node, determine set of nodes to run handler on: (failed node list only)
                        if(stepCaptureFailedNodesListener.getMatchedNodes().size()>1) {
                            HashSet<String> failedNodeList = new HashSet<String>(
                                stepCaptureFailedNodesListener.getFailedNodes().keySet());

                            handlerExecContext = new ExecutionContextImpl.Builder(handlerExecContext).nodeSelector(
                                SelectorUtils.nodeList(failedNodeList)).build();

                        }

                        if(null!=stepResult) {
                            //add step failure data to data context
                            handlerExecContext = addStepFailureContextData(stepResult, handlerExecContext);

                            //extract node-specific failure and set as node-context data
                            handlerExecContext = addNodeStepFailureContextData(stepResult, handlerExecContext);
                        }

                        Map<Integer, StepExecutionResult> handlerFailedMap = new HashMap<Integer, StepExecutionResult>();
                        StepExecutionResult handlerResult = executeWFItem(handlerExecContext,
                                                                          handlerFailedMap,
                                                                          c,
                                                                          handler);
                        boolean handlerSuccess = handlerResult.isSuccess();

                        //handle success conditions:
                        //1. if keepgoing=true, then status from handler overrides original step
                        //2. keepgoing=false, then status is the same as the original step, unless
                        //   the keepgoingOnSuccess is set to true and the handler succeeded
                        boolean useHandlerResults=keepgoing;
                        if(!keepgoing && handlerSuccess && handler instanceof HandlerExecutionItem) {
                            useHandlerResults= ((HandlerExecutionItem) handler).isKeepgoingOnSuccess();
                        }
                        if(useHandlerResults){
                            stepSuccess = handlerSuccess;
                            stepResult=handlerResult;
                            stepFailedMap = handlerFailedMap;
                            nodeFailures = handlerCaptureFailedNodesListener.getFailedNodes();
                        }
                    }
                }
            } finally {
                if (null != wlistener) {
                    wlistener.finishWorkflowItem(c, cmd);
                }
            }
            resultList.add(stepResult);
            failedMap.putAll(stepFailedMap);
            if(!stepSuccess){
                workflowsuccess = false;
            }

            //report node failures based on results of step and handler run.
            if (null != executionContext.getExecutionListener() && null != executionContext.getExecutionListener()
                .getFailedNodesListener()) {
                if(nodeFailures.size()>0){
                    executionContext.getExecutionListener().getFailedNodesListener().nodesFailed(
                    nodeFailures);
                }else if(workflowsuccess){
                    executionContext.getExecutionListener().getFailedNodesListener().nodesSucceeded();
                }

            }

            if(!stepSuccess && !keepgoing) {
                break;
            }
            c++;
        }
        return workflowsuccess;
    }

    /**
     * Add step result failure information to the data context
     */
    private StepExecutionContext addStepFailureContextData(StepExecutionResult stepResult,
                                                           StepExecutionContext handlerExecContext) {
        HashMap<String, String>
        resultData = new HashMap<String, String>();
        if (null != stepResult.getFailureData()) {
            //convert values to string
            for (final Map.Entry<String, Object> entry : stepResult.getFailureData().entrySet()) {
                resultData.put(entry.getKey(), entry.getValue().toString());
            }
        }
        FailureReason reason = stepResult.getFailureReason();
        if(null== reason){
            reason= StepFailureReason.Unknown;
        }
        resultData.put("reason", reason.toString());
        String message = stepResult.getFailureMessage();
        if(null==message) {
            message = "No message";
        }
        resultData.put("message", message);
        //add to data context

        handlerExecContext = ExecutionContextImpl.builder(handlerExecContext).
            setContext("result", resultData)
            .build();
        return handlerExecContext;
    }

    /**
     * Add any node-specific step failure information to the node-specific data contexts
     */
    private StepExecutionContext addNodeStepFailureContextData(StepExecutionResult dispatcherStepResult,
                                                               StepExecutionContext handlerExecContext) {
        final Map<String, ? extends NodeStepResult> resultMap;
        if (NodeDispatchStepExecutor.isWrappedDispatcherResult(dispatcherStepResult)) {
            DispatcherResult dispatcherResult = NodeDispatchStepExecutor.extractDispatcherResult(dispatcherStepResult);
            resultMap = dispatcherResult.getResults();
        } else if (NodeDispatchStepExecutor.isWrappedDispatcherException(dispatcherStepResult)) {
            DispatcherException exception = NodeDispatchStepExecutor.extractDispatcherException(dispatcherStepResult);
            resultMap = exception.getResultMap();
        } else {
            return handlerExecContext;
        }
        ExecutionContextImpl.Builder builder = ExecutionContextImpl.builder(handlerExecContext);
        for (final Map.Entry<String, ? extends NodeStepResult> dentry : resultMap.entrySet()) {
            String nodename = dentry.getKey();
            NodeStepResult stepResult = dentry.getValue();
            HashMap<String, String> resultData = new HashMap<String, String>();
            if (null != stepResult.getFailureData()) {
                //convert values to string
                for (final Map.Entry<String, Object> entry : stepResult.getFailureData().entrySet()) {
                    resultData.put(entry.getKey(), entry.getValue().toString());
                }
            }
            FailureReason reason = stepResult.getFailureReason();
            if (null == reason) {
                reason = StepFailureReason.Unknown;
            }
            resultData.put("reason", reason.toString());
            String message = stepResult.getFailureMessage();
            if (null == message) {
                message = "No message";
            }
            resultData.put("message", message);
            //add to data context
            HashMap<String, Map<String, String>> ndata = new HashMap<String, Map<String, String>>();
            ndata.put("result", resultData);
            builder.nodeDataContext(nodename, ndata);
        }
        return builder.build();
    }

    private StepExecutionContext replaceFailedNodesListenerInContext(StepExecutionContext executionContext,
                                                                 FailedNodesListener captureFailedNodesListener) {
        ExecutionListenerOverride listen=null;
        if(null!= executionContext.getExecutionListener()) {
            listen = executionContext.getExecutionListener().createOverride();
        }
        if(null!=listen){
            listen.setFailedNodesListener(captureFailedNodesListener);
        }

        return new ExecutionContextImpl.Builder(executionContext).executionListener(listen).build();
    }

    /**
     * Convert map of step execution results keyed by step number, to a collection of step execution results
     * keyed by node name
     */
    protected Map<String, Collection<StepExecutionResult>> convertFailures(
        final Map<Integer, StepExecutionResult> failedMap) {

        final Map<String, Collection<StepExecutionResult>> failures
            = new HashMap<String, Collection<StepExecutionResult>>();
        for (final Map.Entry<Integer, StepExecutionResult> entry : failedMap.entrySet()) {
            final StepExecutionResult o = entry.getValue();

            if (NodeDispatchStepExecutor.isWrappedDispatcherResult(o)) {
                //indicates dispatcher returned node results
                final DispatcherResult dispatcherResult = NodeDispatchStepExecutor.extractDispatcherResult(o);

                for (final String s : dispatcherResult.getResults().keySet()) {
                    final NodeStepResult interpreterResult = dispatcherResult.getResults().get(s);
                    if (!failures.containsKey(s)) {
                        failures.put(s, new ArrayList<StepExecutionResult>());
                    }
                    failures.get(s).add(interpreterResult);
                }
            } else if (NodeDispatchStepExecutor.isWrappedDispatcherException(o)) {
                DispatcherException e = NodeDispatchStepExecutor.extractDispatcherException(o);
                final INodeEntry node = e.getNode();
                if (null != node) {
                    //dispatch failed for a specific node
                    final String key = node.getNodename();
                    if (!failures.containsKey(key)) {
                        failures.put(key, new ArrayList<StepExecutionResult>());
                    }
                    failures.get(key).add(e.getResultMap().get(node.getNodename()));
                } else {
                    //dispatch failed for a set of nodes
                    for (final String s : e.getResultMap().keySet()) {
                        final NodeStepResult interpreterResult = e.getResultMap().get(s);
                        if (!failures.containsKey(s)) {
                            failures.put(s, new ArrayList<StepExecutionResult>());
                        }
                        failures.get(s).add(interpreterResult);
                    }
                }
            }
        }
        return failures;
    }
}
