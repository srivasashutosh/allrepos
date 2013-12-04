/*
 * Copyright 2012 DTO Labs, Inc. (http://dtolabs.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

/*
* StepException.java
* 
* User: Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
* Created: 11/6/12 10:50 AM
* 
*/
package com.dtolabs.rundeck.core.execution.workflow.steps;

/**
 * StepException is ...
 *
 * @author Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
 */
public class StepException extends Exception {
    protected FailureReason failureReason;

    public StepException(String msg, FailureReason reason) {
        super(msg);
        this.failureReason = reason;
    }

    public StepException(Throwable cause, FailureReason reason) {
        super(cause);
        this.failureReason = reason;
    }

    public StepException(String msg, Throwable cause, FailureReason reason) {
        super(msg, cause);
        this.failureReason = reason;
    }

    public FailureReason getFailureReason() {
        return failureReason;
    }
}
