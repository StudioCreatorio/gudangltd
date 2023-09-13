import{waitForEl}from"../../../../assets/js/helpers.js?ver=2.6.1";import{Builder}from"../../../../assets/js/builder-apis.js?ver=2.6.1";!function(){let d=[{regex:/: *([^;]*?)(?=;|$)/g,prefix:": ",replaceWith:"<group1>"}],u=[{regex:/(\d+\.?\d*)ctr(?= |$)/g,replaceWith:(e,t)=>{var r=p_acssSettings_object.settings["root-font-size"]??100;return t[0]/(r/100*16)+"rem"}},{regex:/(var\(|var\( )(--[^\s\(\)\+\*\/]+)(\)| \))?/g,replaceWith:"<group2>"},{regex:/^calc\( *(.*?[^\)]) *$/g,replaceWith:"calc(<group1>)"},{regex:/--[^\s\(\)\+\*\/]+/g,replaceWith:"var(<match>)"},{regex:/^(?!calc\()([^\s]+ +[\*\+\/\-] +[^\s]+)/g,replaceWith:"calc(<match>)"},{regex:/^(?!calc\()(var\(\S+|\d+\.?\d*[a-z]{0,3}|\d+) *([\*\+]) *(var\(\S+|\d+\.?\d*[a-z]{0,3}|\d+)/g,replaceWith:"calc(<group1> <group2> <group3>)"},{regex:/^(calc\([^\s]+) *([\*\+\/]) *([^\s]+)/g,replaceWith:"<group1> <group2> <group3>"}],t=async()=>{let e,s="input[type='text']",c,n=".CodeMirror",i=(document.querySelector(".ng-scope")?(e="#oxygen-sidebar",c=".oxygen-classes-dropdown-input, .custom-attributes, .custom-js, .code-php"):document.querySelector(".brx-body.main")?(e="#bricks-panel",c="#bricks-panel-element-classes input"):(e=".interface-navigable-region.interface-interface-skeleton__sidebar",await waitForEl(e),n=""),(e,t,r)=>!e.closest(r)&&!!e.closest(t)),l=(e,t,a)=>(e.forEach(o=>{t=t.replace(o.regex,(e,...t)=>{if("function"==typeof o.replaceWith)return o.replaceWith(e,t);let r="";r=o.replaceWith.replace("<match>",e);for(let e=0;e<t.length;e++)r=r.replace(`<group${e+1}>`,t[e]);return a&&(r=l(a,r)),r=""+(o.prefix??"")+r+(o.suffix??"")})}),t);var t;document.querySelector(e).addEventListener("keydown",e=>{let t=e.target;var r,o;i(t,s,c)&&")"===e.key&&((r=(o=t.value).match(/\(/g))?r.length:0)===((r=o.match(/\)/g))?r.length:0)&&(e.preventDefault(),o=t.selectionStart,")"==t.value.charAt(o)&&(t.selectionStart=t.selectionStart+1),t.classList.add("acss-input-error"),setTimeout(()=>{t.classList.remove("acss-input-error")},500))}),document.querySelector(e).addEventListener("keydown",e=>{var t=e.target;i(t,s,c)&&"Enter"===e.key&&(e=l(u,t.value),Builder.setValue(e,t),Builder.setUnitToNone(t))}),""!=n&&document.querySelector(e).addEventListener("keydown",e=>{var t,r,o,a,s=document.querySelector(n);s&&s.contains(e.target)&&";"==e.key&&(t=s.CodeMirror.doc.getValue(),o={...r=s.CodeMirror.doc.getCursor(),ch:0},a=s.CodeMirror.doc.indexFromPos(r),o=s.CodeMirror.doc.indexFromPos(o),o=t.slice(o,a),";"===t.charAt(a)&&e.preventDefault(),(a=l(d,o,u))!==o)&&(e=t.replace(o,a),s.CodeMirror.doc.setValue(e),s.CodeMirror.doc.setCursor(r.line,9999))}),document.querySelector(e).addEventListener("input",function(t,r){let o;return(...e)=>{clearTimeout(o),o=setTimeout(()=>{t.apply(this,e)},r)}}(t=>{let r=null,e=null,o;if((o=""!=n?document.querySelector(n):o)?(r=o,e=r.CodeMirror.doc.getValue()):(r=t.target,i(r,s,c)&&(e=r.value)),null!==e){var t=e.match(/\(/g),t=t?t.length:0,a=e.match(/\)/g);if(t!==(a?a.length:0)){var t="Your input contains unbalanced brackets.",a=r;console.warn(t,a);let e=document.querySelector("#acss-error-message");e||((e=document.createElement("div")).id="acss-error-message"),e.style.top=a.getBoundingClientRect().bottom+"px",a.matches(n)&&(e.style.top=a.getBoundingClientRect().top-15+"px"),e.style.left=a.getBoundingClientRect().left+"px",document.querySelector("body").appendChild(e),e.innerHTML=t,e.classList.remove("acss-hidden"),setTimeout(()=>{e.classList.add("acss-hidden")},5e3),a.classList.add("acss-input-error")}else t=r,(a=document.querySelector("#acss-error-message"))&&(a.innerHTML="",a.classList.add("acss-hidden")),t.classList.remove("acss-input-error")}},750)),(t=document.createElement("style")).textContent=`
:root {
    --acss-red: #ff0000;
}

#acss-error-message {
    position: absolute;
    background-color: var(--acss-red);
    color: white;
    padding: .1em;
    font-size: .6em;
    font-weight: bold;
    z-index: 9999;
}

.acss-input-error {
    outline: 1px solid var(--acss-red) !important;
    transition: border-width 0.3s linear;
}

.acss-hidden {
    display: none;
}
`,document.head.appendChild(t)};document.addEventListener("DOMContentLoaded",()=>{let e;null==(e=null==(e=document.getElementById("bricks-builder-iframe"))?document.getElementById("ct-artificial-viewport"):e)?t():e.addEventListener("load",()=>{t()})})}();