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

package com.dtolabs.rundeck.core.authorization;


import com.dtolabs.rundeck.core.common.Framework;
import org.apache.log4j.Logger;

import java.io.File;

/**
 * Provides trivial ALLOW implementation of {@link Authorization} interface.
 *
 * @author alexh
 */
public class NoAuthorization extends BaseAuthorization{
    private final static Logger logger = Logger.getLogger(NoAuthorization.class);


    public NoAuthorization(final Framework framework, final File aclBaseDir) {
        super(framework, aclBaseDir);
    }

    @Override
    protected Logger getLogger() {
        return logger;
    }

    @Override
    protected String getDescription() {
        return this.getClass().getName() + "NoAuthorization: All authorization allowed.";
    }

    @Override
    protected Explanation.Code getResultCode() {
        return Explanation.Code.GRANTED_NO_AUTHORIZATION_ATTEMPTED;
    }

    @Override
    protected boolean isAuthorized() {
        return true;
    }

    /**
     * Factory method returning an instance implementing the {@link com.dtolabs.rundeck.core.authorization.Authorization}
     * interface.
     *
     * @param framework  Framework instance
     * @param aclBasedir Directory where the ACLs reside.
     *
     * @return
     */
    public static Authorization create(final Framework framework, final File aclBasedir) {
        return new NoAuthorization(framework, aclBasedir);
    }

}
