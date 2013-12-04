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

package com.dtolabs.rundeck.core.common;

import java.io.File;

/**
 * Implementations of this interface provide a resource in a composition hierarchy of resources.
 * Conceptually, one can imagine a framework resource as a node in a acyclic directed graph. The word
 * "Node" was not chosen to avoid confusion with "machine nodes".
 * <p/>
 */
public interface IFrameworkResource {
    /**
     * Getter to resource name
     *
     * @return
     */

    String getName();

    /**
     * Getter to resource base dir
     *
     * @return
     */
    File getBaseDir();

    /**
     * Get the parent of this resource
     */
    IFrameworkResourceParent getParent();

    boolean isValid();

}
