package com.dtolabs.rundeck.app.support
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

/*
 * ExtNodeFilters.java
 * 
 * User: Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
 * Created: Jul 2, 2010 10:48:53 AM
 * $Id$
 */

/**
 * Extends BaseNodeFilters to add filter params used in GUI filtering
 */
public class ExtNodeFilters extends BaseNodeFilters{

    String project

    static constraints={
        project(nullable:true)
    }

    public boolean nodeFilterIsEmpty(){
        return super.nodeFilterIsEmpty() 
    }

    public static from(BaseNodeFilters filters, String project){
        def filterProperties = filterKeys.values().collect { 'nodeInclude' + it } + filterKeys.values().collect { 'nodeExclude' + it }
        def filters1 = new ExtNodeFilters(filters.properties.subMap(filterProperties))
        filters1.project=project
        return filters1
    }
}
