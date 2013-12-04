/*
 * Copyright 2010 DTO Labs, Inc. (http://dtolabs.com)
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
* NodeFileParserException.java
* 
* User: Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
* Created: Apr 26, 2010 11:28:13 AM
* $Id$
*/
package com.dtolabs.rundeck.core.common;

/**
 * NodeFileParserException indicates an exception with the NodeFileParser
 *
 * @author Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
 * @version $Revision$
 */
public class NodeFileParserException extends Exception {
    public NodeFileParserException() {
        super();
    }

    public NodeFileParserException(final String msg) {
        super(msg);
    }

    public NodeFileParserException(final Exception cause) {
        super(cause);
    }

    public NodeFileParserException(final String msg, final Exception cause) {
        super(msg, cause);
    }
}
