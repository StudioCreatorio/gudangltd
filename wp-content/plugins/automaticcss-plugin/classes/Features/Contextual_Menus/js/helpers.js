let getColorInlineStyles=e=>{return`background-color: white; background-image: linear-gradient(var(--${e[2]+(e[3]??"")}), var(--${e[2]+(e[3]??"")})),
                linear-gradient(45deg, #ccc 25%, transparent 25%),
    linear-gradient(-45deg, #ccc 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, #ccc 75%),
    linear-gradient(-45deg, transparent 75%, #ccc 75%);
    background-size: 20px 20px;
    background-position: 0 0 , 0 0, 0 10px, 10px -10px, -10px 0px;`},getVarColorInlineStyles=e=>{return`background-color: white; background-image: linear-gradient(var(--${e.join("")}),var(--${e.join("")})),linear-gradient(45deg, #ccc 25%, transparent 25%),
    linear-gradient(-45deg, #ccc 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, #ccc 75%),
    linear-gradient(-45deg, transparent 75%, #ccc 75%);
    background-size: 20px 20px;
    background-position: 0 0 , 0 0, 0 10px, 10px -10px, -10px 0px;`},allColors=["action","primary","secondary","accent","base","shade"],contextualColors=["success","danger","warning","info"],xs_to_xxl=["xs","s","m","l","xl","xxl"],breakpoints=["xs","s","m","l"],transparencies=["trans-90","trans-80","trans-70","trans-60","trans-50","trans-40","trans-30","trans-20","trans-10"],getColorGroupItemsWithTransparencies=(e,t)=>{e=[{values:[e,"--",[t+"-ultra-dark"]],options:[["",...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join("")},{values:[e,"--",[t+"-dark"]],options:[["",...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join("")},{values:[e,"--",[t+"-medium"]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join("")},{values:[e,"--",[t,t+"-light"]],options:[["",...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join("")},{values:[e,"--",[t+"-ultra-light",t+"-comp",t+"-hover"]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join("")}];return buildBlueprints(e)},getVarColorGroupItemsWithTransparencies=e=>{e=[{values:[e,["-ultra-dark"]],options:[["",...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getVarColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>"var(--"+e.join("")+")",getTooltip:e=>e.join("")},{values:[e,["-dark"]],options:[["",...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getVarColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>"var(--"+e.join("")+")",getTooltip:e=>e.join("")},{values:[[e+"-medium"]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getVarColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>"var(--"+e.join("")+")",getTooltip:e=>e.join("")},{values:[[e,e+"-light"]],options:[["",...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getVarColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>"var(--"+e.join("")+")",getTooltip:e=>e.join("")},{values:[[e+"-ultra-light",e+"-comp",e+"-hover"]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getVarColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>"var(--"+e.join("")+")",getTooltip:e=>e.join("")}];return buildBlueprints(e)},getVarColorGroupsWithTransparencies=e=>{let t=[];return e.forEach(e=>{t.push({heading:e.charAt(0).toUpperCase()+e.slice(1),filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-"+e+"-clr"]||"shade"===e,classes:["plstr-color-group"],groupItems:getVarColorGroupItemsWithTransparencies(e)})}),t},getColorGroupItems=(e,t)=>buildBlueprint({values:[e,"--",[t+"-ultra-dark",t+"-dark",t+"-medium",t,t+"-light",t+"-ultra-light"]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>`background-color: var(--${e[2]})`,getDisplayText:()=>""}),getContextualColorGroupItems=(e,t)=>buildBlueprint({values:[e,"--",[t,t+"-light",t+"-dark",t+"-hover"]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>`background-color: var(--${e[2]})`,getDisplayText:()=>""}),getContextualColorGroups=(t,e)=>{let l=[];return e.forEach(e=>{l.push({heading:e.charAt(0).toUpperCase()+e.slice(1),classes:["plstr-color-group"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-contextual-colors"]&&"on"===p_acssSettings_object.settings["option-contextual-color-classes"],groupItems:getContextualColorGroupItems(t,e)})}),l},getContextualVarColorGroupItems=e=>buildBlueprint({values:[[e,e+"-light",e+"-dark",e+"-hover"]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>`background-color: var(--${e.join("")})`,getDisplayText:()=>""}),getContextualVarColorGroups=e=>{let t=[];return e.forEach(e=>{t.push({heading:e.charAt(0).toUpperCase()+e.slice(1),classes:["plstr-color-group"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-contextual-colors"],groupItems:getContextualVarColorGroupItems(e)})}),t},getSimpleColorGroupItems=(t,e)=>{let l=[];return e.forEach(e=>{l.push({values:[t,"--",e],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>`background-color: var(--${e[2]})`,getDisplayText:()=>""})}),buildBlueprints(l)},getOverlayColorGroupItems=(e,t)=>{e=[{values:[e,"--",[t+"-ultra-dark"]],options:[[...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join("")},{values:[e,"--",[t,t+"-light"]],options:[[...transparencies.map(e=>"-"+e)]],getClasses:()=>["plstr-color-swatch"],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join("")}];return buildBlueprints(e)},buildBlueprints=e=>{let t=[];return e.forEach(e=>{t.push(...buildBlueprint(e))}),t},defaultBlueprint={options:[""],getClasses:()=>[""],getInlineStyles:e=>"",getDisplayText:e=>{var t="-"===e.slice(-1)[0].charAt(0)?e.slice(-1)[0].substring(1):e.slice(-1)[0];if(t.length<4)switch(t.toLowerCase()){case"row":case"col":case"end":case"top":return t;default:return t.toUpperCase()}return t},getValue:e=>{switch(e.length){case 1:case 2:case 3:return e.join("");default:return e[0]+e[1]+(e[3]??"")+(e[3]?"-":"")+e[2]}},getTooltip:e=>{switch(e.length){case 1:case 2:case 3:return e.join("");default:return e[0]+e[1]+(e[3]??"")+(e[3]?"-":"")+e[2]}}},updateDefaultBlueprint=e=>{defaultBlueprint=e},buildBlueprint=r=>{let t=[];return function(e,t){function l(s){let e=[];return function t(l,r){if(r===s.length)e.push([...l]);else if(Array.isArray(s[r]))for(let e=0;e<s[r].length;e++)l.push(s[r][e]),t(l,r+1),l.pop();else l.push(s[r]),t(l,r+1),l.pop()}([],0),e}e=l(e);let r=l(t),s=[];return e.forEach(t=>{let l=[];r.forEach(e=>{l.push([...t.filter(e=>""!==e),...e.filter(e=>""!==e)])}),s.push(l)}),s}(r.values,r.options??defaultBlueprint.options).forEach(e=>{let l={options:[]};e.forEach(e=>{var t={};t.value=r.getValue?.(e)??defaultBlueprint.getValue(e),t.classes=r.getClasses?.(e)??defaultBlueprint.getClasses(e),t.inlineStyles=r.getInlineStyles?.(e)??defaultBlueprint.getInlineStyles(e),t.displayText=r.getDisplayText?.(e)??defaultBlueprint.getDisplayText(e),t.tooltip=r.getTooltip?.(e)??defaultBlueprint.getTooltip(e),l.options.push(t)}),t.push(l)}),t},getColorGroupsWithTransparencies=(t,e)=>{let l=[];return e.forEach(e=>{l.push({heading:e.charAt(0).toUpperCase()+e.slice(1),filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-"+e+"-clr"]||"shade"===e,classes:["plstr-color-group"],groupItems:getColorGroupItemsWithTransparencies(t,e)})}),l},getColorGroups=(t,e)=>{let l=[];return e.forEach(e=>{l.push({heading:e.charAt(0).toUpperCase()+e.slice(1),classes:["plstr-color-group"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-"+e+"-clr"],groupItems:getColorGroupItems(t,e)})}),l},getOverlayColorGroups=(t,e)=>{let l=[];return e.forEach(e=>{l.push({heading:e.charAt(0).toUpperCase()+e.slice(1),classes:["plstr-color-group"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-"+e+"-clr"],groupItems:getOverlayColorGroupItems(t,e)})}),l};export{getColorInlineStyles,getVarColorInlineStyles,allColors,contextualColors,xs_to_xxl,breakpoints,transparencies,getColorGroupItemsWithTransparencies,getVarColorGroupItemsWithTransparencies,getVarColorGroupsWithTransparencies,getColorGroupItems,getContextualColorGroupItems,getContextualColorGroups,getContextualVarColorGroupItems,getContextualVarColorGroups,getSimpleColorGroupItems,getOverlayColorGroupItems,buildBlueprints,defaultBlueprint,updateDefaultBlueprint,buildBlueprint,getColorGroupsWithTransparencies,getColorGroups,getOverlayColorGroups};