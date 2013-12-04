/*
 * Copyright 2013 SimplifyOps
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *    http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package org.rundeck.jaas.jetty;

import org.mortbay.jetty.plus.jaas.spi.PropertyFileLoginModule;
import org.mortbay.jetty.plus.jaas.spi.UserInfo;
import org.rundeck.jaas.AbstractSharedLoginModule;

import javax.security.auth.Subject;
import javax.security.auth.callback.CallbackHandler;
import javax.security.auth.callback.UnsupportedCallbackException;
import javax.security.auth.login.LoginException;
import java.io.IOException;
import java.security.Principal;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;
import java.util.logging.Level;
import java.util.logging.Logger;

/**
 * Extends Jetty property file login module {@link PropertyFileLoginModule}, to ignore authentication via property file
 * login, but match the username with supplied Role lists from the property file.
 */
public class JettyRolePropertyFileLoginModule extends AbstractSharedLoginModule {
    public static final Logger logger = Logger.getLogger(JettyRolePropertyFileLoginModule.class.getName());
    PropertyFileLoginModule module;
    UserInfo userInfo;

    @Override
    public void initialize(Subject subject, CallbackHandler callbackHandler, Map shared, Map options) {
        super.initialize(subject, callbackHandler, shared, options);
        if (!isUseFirstPass() && !isTryFirstPass()) {
            throw new IllegalStateException("JettyRolePropertyFileLoginModule must have useFirstPass or tryFirstPass " +
                    "set to true");
        }
        module = new PropertyFileLoginModule();
        module.initialize(subject, callbackHandler, shared, options);
    }

    protected Object[] getCallBackAuth() throws IOException, UnsupportedCallbackException, LoginException {
        if (isHasSharedAuth()) {
            return new Object[]{getSharedUserName(), getSharedUserPass().toString().toCharArray()};
        } else {
            return new Object[]{null, null};
        }
    }

    @Override
    protected Principal createUserPrincipal() {
        //do not create user principal
        return null;
    }


    @Override
    protected List<Principal> createRolePrincipals() {

        ArrayList<Principal> roles = new ArrayList<Principal>();
        if (null != this.userInfo) {
            List roleNames = this.userInfo.getRoleNames();
            debug(String.format("role names: %s", roleNames));
            for (Object roleName : roleNames) {
                roles.add(createRolePrincipal(roleName.toString()));
            }
        }

        return roles;
    }

    protected Principal createRolePrincipal(String role) {
        return JettySupport.createRolePrincipal(role);
    }

    @Override
    protected boolean authenticate(String sharedUserName, char[] chars) throws LoginException {
        if (!isHasSharedAuth()) {
            debug("JettyRolePropertyFileLoginModule: no shared auth, skipping.");
            return false;
        }
        try {
            this.userInfo = module.getUserInfo(sharedUserName);
            debug(String.format("JettyRolePropertyFileLoginModule: userInfo found for %s? %s", sharedUserName,
                    this.userInfo != null));
        } catch (Exception e) {
            if (isDebug()) {
                e.printStackTrace();
            }
        }
        return true;
    }

    /**
     * Emit Debug message via System.err by default
     *
     * @param message
     */
    protected void debug(String message) {
        logger.log(Level.INFO, message);
    }

    @Override
    public boolean commit() throws LoginException {
        if (!isAuthenticated()) {
            userInfo = null;
        }
        return super.commit();
    }

    @Override
    public boolean abort() throws LoginException {
        userInfo = null;

        return super.abort();
    }

    @Override
    public boolean logout() throws LoginException {

        userInfo = null;

        return super.logout();
    }
}
