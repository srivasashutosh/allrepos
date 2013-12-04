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
* SharedConstants.java
* 
* User: greg
* Created: Oct 9, 2007 5:28:19 PM
* $Id$
*/
package com.dtolabs.shared;

/**
 * SharedConstants is ...
 *
 * @author Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
 * @version $Revision$
 */
public class SharedConstants {
    /**
     * View direction constraints
     */
    public static final String INTERNAL = "internal";
    public static final String EXTERNAL = "external";
    public static final String BIDIRECTIONAL = "bidirectional";

    /**
     * Set this value to non-null if running as workbench server
     */
    public static String FRAMEWORK_SERVER_HOSTNAME=null;
}
