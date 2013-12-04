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
* Environment.java
* 
* User: Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
* Created: 8/29/11 12:26 PM
* 
*/
package com.dtolabs.rundeck.core.authorization.providers;

import com.dtolabs.rundeck.core.authorization.Attribute;

import java.util.*;

/**
 * EnvironmentalContext determines if a set of environment attributes match a specific context.
 *
 * @author Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
 */
public interface EnvironmentalContext {
    /**
     * Base URI for rundeck environment attribute URIs
     */
    static final String URI_BASE = "http://dtolabs.com/rundeck/env/";

    /**
     * Return true if the context matches the input environment
     */
    public boolean matches(Set<Attribute> environment);
    /**
     * Return true if the context definition is valid
     */
    public boolean isValid();
}
