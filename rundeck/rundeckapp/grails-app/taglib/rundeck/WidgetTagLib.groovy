package rundeck
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
 * WidgetTagLib.java
 * 
 * User: Greg Schueler <a href="mailto:greg@dtosolutions.com">greg@dtosolutions.com</a>
 * Created: Feb 12, 2010 9:35:25 PM
 * $Id$
 */
import grails.util.Environment;

public class WidgetTagLib {
    def static namespace="wdgt"
    def static String AND_COND_VAR=namespace+":AND_COND"
    def static String ACTION_VAR=namespace+":ACTION_VAR"
    def static String INV_ACTION_VAR=namespace+":INV_ACTION_VAR"
    def static String ELEM_VAR_NAME=namespace+":ELEM_VAR"
    def static String TARGET_VAR_NAME=namespace+":TARGET_VAR"
    def static cssDisplayNone='display:none;'
    private static Long counter=1
    /**
     * styleVisible: inserts a css display depending on conditions
     */
    def styleVisible={attrs,body->
        if(attrs.keySet().contains('if') && attrs.keySet().contains('unless')){
            throw new Exception("styleVisible: both if and unless attributes cannot be specified")
        }else if(!attrs.keySet().contains('if') && !attrs.keySet().contains('unless')){
            throw new Exception("styleVisible: if or unless attribute must be specified")
        }
        def testvalue=attrs.keySet().contains('if')?attrs.'if':attrs.'unless'
        def testtrue=attrs.keySet().contains('if');

        if((testtrue && testvalue && testvalue!='false') || (!testtrue && (!testvalue ||testvalue=='false')) ){
            out<<''
        }else {
            out<<cssDisplayNone
        }
    }


    /**
    * This tag simply calls the normal "eventHandler" tag and sets jsonly="true" automatically.
     */
    def eventHandlerJS={attrs,body->
        attrs.jsonly='true'
        return eventHandler.call(attrs,body)
    }

    /**
    * Generate javascript to add an event listener so that a form field state change
     * can trigger other field state changes (visible, clear content, change css classname)
     * <p>
     * attributes:
     * </p>
     * <p>
     *  for: elment ID to monitor state of (*this or forSelector required)
     *  forSelector: prototype selector for a set of elements to attach event handlers to (*this or for required)
     *  state: state name to monitor: 'checked/unchecked' (checkboxes), 'empty/unempty' (fields, radiobtns) (*this or action or not/equals required)
     *  action: name of explicit event action to monitor (e.g. 'click', 'mouseover') (*this or state or not/equals required)
     *  equals: value to compare to field value (*this or action or state required)
     *  notequals: value to compare not equal to field value (*this or action or state required)
     *  target: element ID to affect if state is changed (defaults to same as *for)
     *  targetSelect: prototype selector for a set of elements to affect
     * </p>
     * <p>
     * Additional attributes declare the kind of effect to apply to the [target] elements:
     * </p>
     * <p>
     *  visible: "true/false" (show or hide the elements)
     *  clear: "true" (clear field value of the elements, must be fields)
     *  check: "true/false" (set checked value of radio btn)
     *  copy: "value" (copy field value of for element to the target element, must be fields)
     *  copy: "html" (copy inner html value of for element to the target element, target must be fields)
     *  setHtml: "true/value" (set inner html value of target element to either the value of this attribute, or the content of the tag (if "true")
     *  addClassname: css classnames to add to the elements
     *  removeClassname: css classnames to remove from the elements
     * </p>
     * <p>
     * All action attributes can be combined.  When the state of the monitored element(s) changes to the opposite state
     * as declared in "state" attribute, then the reverse of all the actions that are specified will be applied.
     *</p>
     * <p>
     * This can be disabled by setting the attribute "oneway" to true.  in that case only a state change to the desired
     * state has an effect.
     * </p>
     * <p>
     * examples:
     * </p>
     *
     * &lt;wdgt:eventHandler for="fromelement" state="checked" visible="false" target="element" oneway="true'/&gt;
     * &lt;wdgt:eventHandler for="fromelement" state="empty" visible="false" target="element"/&gt;
     * &lt;wdgt:eventHandler for="fromelement" state="unchecked" visible="false" target="element"/&gt;
     * &lt;wdgt:eventHandler for="fromelement" state="unchecked" visible="false" targetSelector="span.myclass"/&gt;
     * &lt;wdgt:eventHandler for="fromelement" state="unchecked" clear="true" target="myfield"/&gt;
     * &lt;wdgt:eventHandler for="fromelement" state="checked" addClassname="selected" target="myfield"/&gt;
     */
    def eventHandler={attrs,body->
        def forSelector=attrs.forSelector
        def forElem=attrs.for
        if(!forElem && !forSelector){
            throw new Exception("for or forSelector required")
        }
        def state=attrs.state
        if(!state && null==attrs.equals && !attrs.action && null==attrs.notequals){
            throw new Exception("state or (not)equals or action required")
        }
        def evt='change'
        if(state=='checked'||state=='unchecked'){
            evt='change'
        }
        if(attrs.action){
            evt=attrs.action
        }
        def obsfreq=null
        if(attrs.frequency){
            obsfreq=attrs.frequency
        }
        //define function to trigger
        def ifcase=false;
        def funcstr=new StringBuffer()
        def varname='e'+(++counter)
        if('change'==evt && obsfreq){
            funcstr<<"function(${varname},value){"
        }else{
            funcstr<<"function(evt){var ${varname}=evt.element();"
        }

        //define condition
        this."pageScope"."$ELEM_VAR_NAME" = varname
        this."pageScope"."$AND_COND_VAR" = []


        this."pageScope"."$ACTION_VAR" = []
        this."pageScope"."$INV_ACTION_VAR" = []

        //call conditional tag using this tags attributes to try to evalute the if conditions
        condition.call(attrs,null)
        //call action tag using this tags attributes to try to evalute the action definitions
        if(attrs.target || attrs.targetSelector){
            action.call(attrs,body)
        }

        //call body content to allow embedded conditionals and actions to evaluate
        def body1=body()
        
        if(this."pageScope"."$AND_COND_VAR"){
            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcstr<<'console.log(\'test condition: \'+ "'+this."pageScope"."$AND_COND_VAR".join(" && ").encodeAsJavaScript()+'");'
                funcstr<<'console.log(\'test result: \'+ ('+this."pageScope"."$AND_COND_VAR".join(" && ")+') );'
            }
            funcstr<<'if('
            funcstr<<this."pageScope"."$AND_COND_VAR".join(" && ")
            funcstr<<'){'
            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcstr<<'console.log(\' trigger: \'+'+varname+');'
            }
            ifcase=true
        }
        this."pageScope"."$AND_COND_VAR"=[]


        def funcaction=''
        def funcinvaction=''
        if(this."pageScope"."$ACTION_VAR"){
            funcaction=this."pageScope"."$ACTION_VAR".join("\n")
        }
        if(this."pageScope"."$INV_ACTION_VAR"){
            funcinvaction=this."pageScope"."$INV_ACTION_VAR".join("\n")
        }

        funcstr<<funcaction

        if('true'!=attrs.oneway && ifcase && funcinvaction){
            funcstr<<"}else{"
            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcstr<<'console.log(\'invert trigger: \'+'+varname+');'
            }
            funcstr<<funcinvaction
        }
        if (ifcase){
            funcstr<<"}"
        }

        funcstr<<"}"

        if('true'!=attrs.jsonly){
            out<<"<script language='javascript'>"
        }
        if('true'!=attrs.inline){
            out<<"Event.observe(window,'load',function(z){"
        }
        if(forElem){
            if('change'==evt && obsfreq){
                out<<"new Form.Element.Observer('${forElem}',${obsfreq},"+funcstr+");"
            }else{
                out<<"Event.observe('${forElem}','${evt}',"+funcstr+");"
                if(evt=='change'){
                    // fix for IE's bad onchange event
                    out<<"if(Prototype.Browser.IE && \$('${forElem}').tagName.toLowerCase()=='input' && (\$('${forElem}').type.toLowerCase()=='radio'||\$('${forElem}').type.toLowerCase()=='checkbox')){"
                    out << "Event.observe('${forElem}','click'," + funcstr + ");"
                    out<<"}"
                }
            }
        }else if(forSelector){
            out<<'$$(\''+forSelector.encodeAsJavaScript()+'\').each(function(elem){'

            if('change'==evt && obsfreq){
                out<<"new Form.Element.Observer(elem,${obsfreq},"+funcstr+");"
            }else{
                out<<"Event.observe(elem,'${evt}',"+funcstr+");"
                if (evt == 'change') {
                    // fix for IE's bad onchange event
                    out << "if(Prototype.Browser.IE && \$(elem).tagName.toLowerCase()=='input' && (\$(elem).type.toLowerCase()=='radio'||\$(elem).type.toLowerCase()=='checkbox')){"
                    out << "Event.observe(elem,'click'," + funcstr + ");"
                    out << "}"
                }
            }
            out<<"});"
        }
        if('true'!=attrs.inline){
            out<<"});"
        }
        if('true'!=attrs.jsonly){
            out <<"</script>"
        }
    }
    /**
     * Can be embedded in a eventHandler tag to provide additional conditions to test whether to invoke the action.
     * These conditions are ANDed with any specified in the eventHandler itself, and are the same conditions
     * available to eventHandler[JS] tag:
     *
     *  <p>
     * attributes:
     * </p>
     * <p>
     *  for: elment ID to monitor state of (*this or forSelector required)
     *  forSelector: prototype selector for a set of elements to attach event handlers to (*this or for required)
     *  state: state name to monitor: 'checked/unchecked' (checkboxes), 'empty/unempty' (fields, radiobtns) (*this or action or not/equals required)
     *  equals: value to compare to field value (*this or action or state required)
     *  notequals: value to compare not equal to field value (*this or action or state required)
     * </p>
     *
     * <p>
     * examples:
     * </p>
     *
     * &lt;-- applies the action when fromelement is checked, and anotherelement is empty --&gt;
     * &lt;wdgt:eventHandler for="fromelement" state="checked" visible="false" target="element" oneway="true'&gt;
     * &lt;wdgt:condition for="anotherelement" state="empty" /&gt;
     * &lt;/wdgt:eventHandler&gt;
     */
    def condition={attrs,body->
        def andconds
        if(!this."pageScope"."$AND_COND_VAR"){
            this."pageScope"."$AND_COND_VAR"=[]
        }
        andconds=this."pageScope"."$AND_COND_VAR"
        def varname=this."pageScope"."$ELEM_VAR_NAME"

        def state=attrs.state
        if(!state && null==attrs.equals && !attrs.action && null==attrs.notequals){
            throw new Exception("state or (not)equals or action required")
        }
        def forElem=attrs.for
        if(forElem){
            varname="'${forElem}'"
        }

        //define condition
        if(state=='checked'){
            andconds<<'$('+varname+').checked'

        }else if(state=='unchecked'){
            andconds<<'!$('+varname+').checked'
        }else if(state=='empty'){
            andconds<<'!$F('+varname+') || \'\'==$F('+varname+')'
        }else if(state=='unempty'){
            andconds<<'$F('+varname+') && \'\'!=$F('+varname+')'
        }else if(null!=attrs.equals){
            andconds<<'$F('+varname+')==\''+attrs.equals.encodeAsJavaScript()+'\''
        }else if(null!=attrs.notequals){
            andconds<<'$F('+varname+')!=\''+attrs.notequals.encodeAsJavaScript()+'\''
        }
        
    }

    /**
     * Can be embedded in a eventHandler[JS] tag to define additional actions to perform when the conditions are met.
     * Has the same attributes as the actions available to eventHandler[JS]:
     * <p>
     * * target: element ID to affect if state is changed (defaults to same as *for)
     *  targetSelect: prototype selector for a set of elements to affect
     * </p>
     * <p>
     * Additional attributes declare the kind of effect to apply to the [target] elements:
     * </p>
     * <p>
     *  visible: "true/false" (show or hide the elements)
     *  clear: "true" (clear field value of the elements, must be fields)
     *  copy: "value" (copy field value of for element to the target element, must be fields)
     *  copy: "html" (copy inner html value of for element to the target element, target must be fields)
     *  setHtml: "true/value" (set inner html value of target element to either the value of this attribute, or the content of the tag (if "true")
     *  addClassname: css classnames to add to the elements
     *  removeClassname: css classnames to remove from the elements
     * </p>
     * <p>
     * All action attributes can be combined.  When the state of the monitored element(s) changes to the opposite state
     * as declared in "state" attribute, then the reverse of all the actions that are specified will be applied. Unless the
     * eventHandler has a oneway="true" attribute.
     *</p>
     * <p>
     * examples:
     * </p>
     *
     * &lt;-- when fromelement is checked, hide "element" and show "anotherelement" --&gt;
     * &lt;wdgt:eventHandler for="fromelement" state="checked" visible="false" target="element" oneway="true'/&gt;
     *      &lt;wdgt:action visible="true" target="anotherlement"/&gt;
     * &lt;/wdgt:eventHandler&gt;
     *
     */
    def action={attrs,body->
        if(!this."pageScope"."$ACTION_VAR"){
            this."pageScope"."$ACTION_VAR"=[]
        }
        def actions=this."pageScope"."$ACTION_VAR"
        if(!this."pageScope"."$INV_ACTION_VAR"){
            this."pageScope"."$INV_ACTION_VAR"=[]
        }
        def invactions=this."pageScope"."$INV_ACTION_VAR"
        def varname=this."pageScope"."$ELEM_VAR_NAME"

        def targetSelector=attrs.targetSelector
        def target=attrs.target
        if(!target && !targetSelector){
            //dynamically set target?
            throw new Exception("target or targetSelector required")
        }

        def funcsel=''
        def funcselend=''
        def ftarget='\''+target+'\''
        if(targetSelector){
            funcsel='$$(\''+targetSelector.encodeAsJavaScript()+'\').each(function(ftarget){'
            ftarget='ftarget'
            funcselend="});"
        }

        def funcaction=''
        def funcinvaction=''
        //define action
        if(attrs.visible =='false'){
            funcaction+='$('+ftarget+').hide();'
            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'visible false trigger\');'+funcaction
            }
            funcinvaction+='$('+ftarget+').show();'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcinvaction+='console.log(\'visible !false trigger\');'+funcinvaction
            }
        }else if(attrs.visible=='true'){
            funcaction+='$('+ftarget+').show();'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'visible true trigger\');'+funcaction
            }
            funcinvaction+='$('+ftarget+').hide();'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcinvaction+='console.log(\'visible !true trigger\');'+funcinvaction
            }
        }
        if(attrs.clear=='true'){

            funcaction+='$('+ftarget+').setValue(\'\');'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'clear true trigger\');'+funcaction
            }
        }
        if(attrs.copy=='value'){

            funcaction+='$('+ftarget+').setValue($F('+varname+'));'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'copy value trigger: "\'+$F('+varname+')+\'"\');'+funcaction
            }
        }
        if(attrs.check=='true'){

            funcaction+='$('+ftarget+').checked=true;'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'checked true trigger: \');'+funcaction
            }
        }
        if(attrs.check=='false'){

            funcaction+='$('+ftarget+').checked=false;'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'checked false trigger: \');'+funcaction
            }
        }
        if(attrs.copy=='html'){

            funcaction+='$('+ftarget+').setValue($('+varname+').innerHTML);'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'copy value trigger\');'+funcaction
            }
        }
        if(attrs.copy=='tohtml'){
            def trans=''
            def transend=''
            if(attrs.transformfuncname){
                trans=attrs.transformfuncname.encodeAsJavaScript()+'('
                transend=')'
            }

            funcaction+='$('+ftarget+').innerHTML='+trans+'$F('+varname+')'+transend+';'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'copy tohtml trigger\');'+funcaction
            }
        }
        if(attrs.disabled){
            if('false'==attrs.disabled){
                funcaction+='$('+ftarget+').removeAttribute("disabled");'
            }else{
                funcaction+='$('+ftarget+').setAttribute("disabled","true");'
            }

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'disabled true trigger\');'+funcaction
            }
            if('false'==attrs.disabled){
                funcinvaction+='$('+ftarget+').setAttribute("disabled","true");'
            }else{
                funcinvaction+='$('+ftarget+').removeAttribute("disabled");'
            }

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcinvaction+='console.log(\'disabled !true trigger\');'+funcinvaction
            }
        }
        if(attrs.jshandler){

            funcaction+=attrs.jshandler.encodeAsJavaScript()+'();'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'jshandler trigger\');'+funcaction
            }
        }
        if(attrs.jstargethandler){

            funcaction+=attrs.jstargethandler.encodeAsJavaScript()+'($('+ftarget+'));'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'jstargethandler trigger\');'+funcaction
            }
        }
        if(attrs.focus=='true'){

            funcaction+='$('+ftarget+').focus();'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'focus true trigger\');'+funcaction
            }
            funcinvaction+='$('+ftarget+').blur();'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcinvaction+='console.log(\'focus !true trigger\');'+funcinvaction
            }
        }
        if(attrs.setHtml){
            def bodyval = 'true'==attrs.setHtml?body():attrs.setHtml
            if(!bodyval){
                bodyval=attrs.setHtml
            }

            funcaction+='$('+ftarget+').innerHTML=\''+bodyval.encodeAsJavaScript()+'\';'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'setHtml body trigger\');'+funcaction
            }
        }
        if(attrs.addClassname){

            funcaction+='$('+ftarget+').addClassName(\''+attrs.addClassname.encodeAsJavaScript()+'\');'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'addClassname trigger\');'+funcaction
            }
            funcinvaction+='$('+ftarget+').removeClassName(\''+attrs.addClassname.encodeAsJavaScript()+'\');'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcinvaction+='console.log(\'addClassname invert trigger\');'+funcinvaction
            }
        }
        if(attrs.removeClassname){

            funcaction+='$('+ftarget+').removeClassName(\''+attrs.removeClassname.encodeAsJavaScript()+'\');'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'removeClassname trigger\');'+funcaction
            }
            funcinvaction+='$('+ftarget+').addClassName(\''+attrs.removeClassname.encodeAsJavaScript()+'\');'
            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcinvaction+='console.log(\'removeClassname invert trigger\');'+funcinvaction
            }
        }
        if(attrs.toggleClassname){

            funcaction+='$('+ftarget+').toggleClassName(\''+attrs.toggleClassname.encodeAsJavaScript()+'\');'

            if(Environment.current == Environment.DEVELOPMENT && attrs.debug){
                funcaction+='console.log(\'toggleClassName trigger\');'+funcaction
            }
        }

        def funcstr=''
        funcstr+=funcsel
        funcstr+=funcaction
        funcstr+=funcselend
        def invfuncstr=''
        invfuncstr+=funcsel
        invfuncstr+=funcinvaction
        invfuncstr+=funcselend

        actions<<funcstr
        invactions<<invfuncstr
    }
}