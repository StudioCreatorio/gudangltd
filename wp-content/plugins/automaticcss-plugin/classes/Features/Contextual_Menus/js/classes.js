import{allColors,xs_to_xxl,breakpoints,getSimpleColorGroupItems,buildBlueprint,buildBlueprints,getColorGroups,getOverlayColorGroups,defaultBlueprint,getColorGroupsWithTransparencies,transparencies,getColorInlineStyles,getContextualColorGroups,contextualColors}from"./helpers.js";let contextMenu={sections:[{heading:"Padding",tags:["padding","spacing"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-padding"],groups:[{groupItems:buildBlueprint({values:["pad","--",[...xs_to_xxl,"none"]]})},{heading:"header",groupItems:buildBlueprint({values:["pad-header","--",xs_to_xxl]})},{heading:"section",filter:"section-padding",groupItems:buildBlueprint({values:["pad-section","--",[...xs_to_xxl,"none"]],options:[["",...breakpoints]]})}]},{heading:"Margin",tags:["margin","spacing"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-margin"],groups:[{heading:"top",groupItems:buildBlueprint({values:["margin-top","--",xs_to_xxl],getDisplayText:e=>defaultBlueprint.getDisplayText(e).toUpperCase()})},{heading:"bottom",groupItems:buildBlueprint({values:["margin-bottom","--",xs_to_xxl]})},{heading:"left",groupItems:buildBlueprint({values:["margin-left","--",xs_to_xxl]})},{heading:"right",groupItems:buildBlueprint({values:["margin-right","--",xs_to_xxl]})}]},{heading:"Grid",tags:["grid","col-end","col-start","auto","start","end","gap","spacing","layout","row-start","row-end"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-grid"],groups:[{groupItems:buildBlueprints([{values:["grid","--",["1","2","3","4"]],options:[["","s","m","l","xl"]]},{values:["grid","--",["5"]],options:[["","l","xl"]]},{values:["grid","--",["6"]],options:[[""]]}])},{groupItems:buildBlueprint({values:["grid","--",["1-2","1-3","2-3","3-2","2-1","3-1"]]})},{heading:"Auto Grids",groupItems:buildBlueprint({values:["grid","--auto-",["1","2","3","4","5","6","1-2","1-3","2-3","3-2","2-1","3-1"]]})},{heading:"Column Start",groupItems:buildBlueprint({values:["col-start","--",["1","2","3","4","5","6"]],options:[["",...breakpoints]]})},{heading:"Column End",groupItems:buildBlueprint({values:["col-end","--",["1","2","3","4","5","6","last"]],options:[["",...breakpoints]]})},{heading:"Row Start",groupItems:buildBlueprint({values:["row-start","--",["1","2","3","4","5","6"]],options:[["",...breakpoints]]})},{heading:"Row End",groupItems:buildBlueprint({values:["row-end","--",["1","2","3","4","5","6"]],options:[["",...breakpoints]]})},{heading:"Alternate",groupItems:buildBlueprint({values:["grid","--",["alternate"]]})}]},{heading:"Flex Grids",tags:["flex","grid","flex-grid"],groups:[{groupItems:buildBlueprint({values:["flex-grid","--",["1","2","3","4","5","6"]]})}]},{heading:"Spacing/Gap",tags:["spacing","gap","gutter"],groups:[{groupItems:buildBlueprints([{values:["gap","--",xs_to_xxl]},{values:[["content","container","grid"],"-","gap"],getDisplayText:e=>e.join("")}])}]},{heading:"Background",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-backgrounds"],tags:["background","bg"],groups:[...getColorGroupsWithTransparencies("bg",allColors),{heading:"Black & White",classes:["plstr-color-group"],groupItems:buildBlueprint({values:["bg","--",["black","white"]],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join(""),options:[["",...transparencies.map(e=>"-"+e)]]})},...getContextualColorGroups("bg",contextualColors)]},{heading:"Width",tags:["width"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-width"],groups:[{groupItems:buildBlueprint({values:["width","--",[...xs_to_xxl,"full","vp-max","auto"]]})}]},{heading:"Center",tags:["center"],groups:[{groupItems:buildBlueprint({values:["center","--",["top"]],options:[["",...breakpoints.map(e=>"-"+e)]],getValue:e=>e.join(""),getTooltip:e=>e.join("")})},{groupItems:buildBlueprint({values:["center","--",["left","all","right"]],options:[["",...breakpoints.map(e=>"-"+e)]],getValue:e=>e.join(""),getTooltip:e=>e.join("")})},{groupItems:buildBlueprint({values:["center","--",["bottom"]],options:[["",...breakpoints.map(e=>"-"+e)]],getValue:e=>e.join(""),getTooltip:e=>e.join("")})},{groupItems:buildBlueprint({values:["center","--",["self"]],options:[["",...breakpoints.map(e=>"-"+e)]],getValue:e=>e.join(""),getTooltip:e=>e.join("")})}]},{heading:"Box-Shadow",tags:["box-shadow","shadow"],filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-box-shadows"],groups:[{groupItems:buildBlueprint({values:["box-shadow","--",[p_acssSettings_object.settings["box-shadow-1-name"],p_acssSettings_object.settings["box-shadow-2-name"],p_acssSettings_object.settings["box-shadow-3-name"]]]})}]},{heading:"Text",tags:["text","txt","heading","h1","h2","h3","h4","h5","h6","align","font","weight","color"],groups:[...getColorGroupsWithTransparencies("text",allColors),...getContextualColorGroups("text",contextualColors),{heading:"Text-Size",groupItems:buildBlueprint({values:["text","--",[...xs_to_xxl,"larger"]]})},{heading:"Font-Weight",groupItems:buildBlueprint({values:["text","--",["100","200","300","400","500","600","700","800","900"]]})},{heading:"Text-Style/Transfomation",groupItems:buildBlueprint({values:["text","--",["italic","bold","oblique","uppercase","capitalize","lowercase","transform-none"]],getDisplayText:e=>{switch(e[2]){case"bold":return"b";case"italic":return"i";case"uppercase":return"AA";case"lowercase":return"aa";case"capitalize":return"Aa";case"oblique":return"o";case"transform-none":return"none";default:return defaultBlueprint.getDisplayText(e)}},getInlineStyles:e=>{switch(e[2]){case"bold":return"font-weight: bold";case"italic":return"font-style: italic";case"oblique":return"font-style: obliuqe";default:return defaultBlueprint.getInlineStyles(e)}}})},{heading:"Text-Decoration",filter:"",groupItems:buildBlueprint({values:["text","--",["overline","underline","line-through","underline-dotted","underline-wavy","underline-double","decoration-none"]],getDisplayText:e=>{switch(e[2]){case"overline":return"over";case"underline":return"under";case"line-through":return"through";case"underline-dotted":return"dotted";case"underline-wavy":return"wavy";case"underline-double":return"double";case"underline-dashed":return"dashed";case"decoration-none":return"none";default:return defaultBlueprint.getDisplayText(e)}},getInlineStyles:e=>{switch(e[2]){case"overline":return"text-decoration: overline";case"underline":return"text-decoration: underline";case"line-through":return"text-decoration: line-through";case"underline-dotted":return"text-decoration: underline dotted";case"underline-wavy":return"text-decoration: underline wavy";case"underline-double":return"text-decoration: underline double";case"underline-dashed":return"text-decoration: underline dashed";case"decoration-none":return"text-decoration: none";default:return defaultBlueprint.getInlineStyles(e)}}})},{heading:"Heading Sizes",groupItems:buildBlueprint({values:["h","--",["1","2","3","4","5","6"]]})},{heading:"Text-Align",groupItems:buildBlueprint({values:["text","--",["left","center","justify","right"]],getDisplayText:e=>{switch(e[2]){case"left":return'<img style="height:2em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAAAkUlEQVRoge3YTQrDIBRF4WvX0S6oXUegyxYKXcfNJIOAkmCIPyTnm4vvOTooAQBwJbY/tqPbi7bfpfOGzAI/Sa9TXqPcP4TwLDnwqDVJK7kFvpJi60GWO6cO9wIAMJCOMbcnG3ujxdyeJPaIuYaIPQDATbhe0B36fduSxNyyQM2gK/5923LJmJPqBR1BBgDA2gxFRJA5M3O3FgAAAABJRU5ErkJggg==">';case"center":return'<img style="height:2em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAAAlElEQVRoge3YUQoCMQxF0VfXoQvSdQguuyC4juf/TDEVzATqPd+FSebrEgkAgJXZvtnuPl63fY3maxMLPCVdfvI3vvdqrZ0/PTgdNUmWmQUeknr2IANd0r3guwAAFCqMt8gw7nYx59p4i+zibsmYq4q3CHEHAEBm7E1d4rbCy9xggczYCy9xW0vGXCQr9og1AMD/eQPUcJA53YnRawAAAABJRU5ErkJggg==">';case"justify":return'<img style="height:2em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAAAcUlEQVRoge3WMQqAMBBE0VnPoQfScwgeOyB4jrEVSaFgEtH/ylSzqb4EAMCX2J5sJ79Psj2e90bmgFXSUOW37tsioj8+dK2WPCV3wCIp1R5yQZI0tx4BAEBZJuaKIuZaIuYAAD9gYq4oYq4lYg4A8D07sTnv2T2tcaQAAAAASUVORK5CYII=">';case"right":return'<img style="height:2em;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAABmJLR0QA/wD/AP+gvaeTAAAAi0lEQVRoge3YMQ7DIBBE0cHncA4UnyOSj41kKeeYtJEbCAavFP4radil+kICAGAmtjfb2ffLtp/neVLDAoekR5fX+N07pbR+HyxBg3TTssAuKfcepEKW9Aq4FwCAGwXGWkldzDk21krmiLmoWCsh5gAAKPK4GOzzM1exwMgYnCPmrhoVg8QcAOD/fADNlZA56EG7mAAAAABJRU5ErkJggg==">';default:return defaultBlueprint.getDisplayText(e)}}})}]},{heading:"Aspect Ratio",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-aspect-ratios"],tags:["aspect-ratio","ar"],groups:[{groupItems:buildBlueprint({values:["aspect","--",["1-1","2-1","1-2","16-9","4-3","3-4","9-16","3-2","2-3"]],options:[["",...breakpoints]]})}]},{heading:"Accessibility",tags:["accessibility","ally","a11y"],groups:[{groupItems:buildBlueprint({values:[["hidden-accessible","clickable-parent"]]})},{heading:"Focus",groupItems:[]},...getColorGroups("focus",allColors),{heading:"Black & White",classes:["plstr-color-group"],groupItems:getSimpleColorGroupItems("focus",["black","white"])}]},{heading:"Selection Color",tags:["selection","highlight"],groups:[{groupItems:buildBlueprint({values:["selection-alt"]})}]},{heading:"Flex & Grid Alignment",tags:["flex","grid","alignment","justify","center","self","stretch"],groups:[{heading:"justify-content",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-flex"],groupItems:buildBlueprint({values:["justify-content","--",["start","around","center","between","end"]]})},{heading:"justify-items",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-flex"],groupItems:buildBlueprint({values:["justify-items","--",["start","center","end"]]})},{heading:"align-content",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-flex"],groupItems:buildBlueprint({values:["align-content","--",["start","baseline","center","end"]]})},{heading:"align-items",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-flex"],groupItems:buildBlueprint({values:["align-items","--",["start","baseline","center","end"]]})},{heading:"self",groupItems:buildBlueprint({values:["self","--",["start","stretch","center","end"]]})},{heading:"Flex Children",groupItems:buildBlueprint({values:["stretch"],getDisplayText:()=>"stretch-children"})}]},{heading:"Object Fit",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-object-fit"],tags:["object-fit","of","cover","contain","fill"],groups:[{groupItems:buildBlueprint({values:["object-fit","--",["top-left","top-center","top-right","center-left","center-right","bottom-left","bottom-center","bottom-right","cover","contain"]]})}]},{heading:"Overlay",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-overlays"],tags:["overlay"],groups:[...getOverlayColorGroups("overlay",["primary","secondary","action","base","shade"]),{heading:"Black & White",classes:["plstr-color-group"],groupItems:buildBlueprint({values:["bg","--",["black","white"]],getInlineStyles:e=>getColorInlineStyles(e),getDisplayText:()=>"",getValue:e=>e.join(""),getTooltip:e=>e.join(""),options:[["",...transparencies.map(e=>"-"+e)]]})}]},{heading:"Button",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-buttons"],tags:["button","btn"],groups:[{heading:"Sizes",groupItems:buildBlueprint({values:["btn","--",xs_to_xxl]})},{heading:"Colors",classes:["plstr-color-group"],groupItems:getSimpleColorGroupItems("btn",["primary","secondary","action","base","black","white"])},{heading:"Types",groupItems:buildBlueprint({values:["btn","--","outline"]})}]},{heading:"Form",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-forms"],tags:["form","wsform"],groups:[{groupItems:buildBlueprint({values:["form","--",["light","dark"]]})}]},{heading:"Height",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-height"],tags:["height"],groups:[{groupItems:buildBlueprint({values:["height","--",["30","40","50","60","70","80","90","full"]],options:[["",...breakpoints]]})},{heading:"Max-Height",groupItems:buildBlueprint({values:["max-height","--",["30","40","50","60","70","80","90"]],options:[["",...breakpoints]]})}]},{heading:"Col/Row span",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-grid"],tags:["col","row","grid","span"],groups:[{heading:"Column Span",groupItems:buildBlueprints([{values:["col-span","--",["1","2","3","4","5","6"]],options:[["",...breakpoints]]},{values:["col-span","--",["all"]]}])},{heading:"Row Span",groupItems:buildBlueprints([{values:["row-span","--",["1","2","3","4","5","6"]],options:[["",...breakpoints]]}])}]},{heading:"Text-Columns",tags:["col-count","columns","text","col-rule","rule","text-columns"],groups:[{heading:"Column Count",groupItems:buildBlueprints([{values:["col-count","--",["1","2","3","4","5"]],options:[["",...breakpoints]]}])},{heading:"Columns Width",groupItems:buildBlueprints([{values:["col-width","--",["s","m","l"]]}])},{heading:"Column Gap",groupItems:buildBlueprints([{values:["col-gap","--",xs_to_xxl]}])},{heading:"Column Rule Style",groupItems:buildBlueprints([{values:["col-rule","--",["solid","dashed","dotted","double","groove","ridge","inset","outset"]]}])},{heading:"Column Rule Width",groupItems:buildBlueprints([{values:["col-rule","--",["s","m","l"]]}])},{heading:"Column Rule Color",groupItems:[]},...getColorGroups("col-rule",allColors),{heading:"Black & White",classes:["plstr-color-group"],groupItems:getSimpleColorGroupItems("col-rule",["black","white"])}]},{heading:"Links",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-link-color"],tags:["link","clickable-parent","link-color","color","anchor","anchor-color","skip-link"],groups:[...getColorGroups("link",allColors),...getContextualColorGroups("link",contextualColors),{groupItems:buildBlueprints([{heading:"Other",values:["clickable-parent"]},{values:["link--skip"]}])}]},{heading:"Z-Index",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-z-index"],tags:["order","z-index","level","stacking","isolation"],groups:[{groupItems:buildBlueprint({values:["z-index","--",["bottom","10","20","30","40","50","60","70","80","90","100","top"]],options:[["",...breakpoints]]})},{groupItems:buildBlueprint({values:["relative"]})},{groupItems:buildBlueprint({values:["isolation-isolate"],getDisplayText:e=>"isolate"})}]},{heading:"Order",tags:["order","grid"],groups:[{groupItems:buildBlueprint({values:["order","--",["first","last"]],options:[["",...breakpoints]]})}]},{heading:"Focus",filter:"focus",tags:["focus","accessibility"],groups:[...getColorGroups("focus",allColors),{heading:"Black & White",classes:["plstr-color-group"],groupItems:getSimpleColorGroupItems("focus",["black","white"])}]},{heading:"Display",filterIfFalse:()=>"on"===p_acssSettings_object.settings["options-display"],tags:["display"],groups:[{groupItems:buildBlueprints([{values:["display","--",["block","none"]],options:[["",...breakpoints]]},{values:["display","--",["inline","contents","inline-block","inline-flex","inline-list-item","list-item"]]}])}]},{heading:"Opacity",filterIfFalse:()=>"on"===p_acssSettings_object.settings["options-opacities"],tags:["opacity","transparency"],groups:[{groupItems:buildBlueprint({values:["opacity","--",["10","20","30","40","50","60","70","80","90","full"]]})}]},{heading:"Sticky",filterIfFalse:()=>"on"===p_acssSettings_object.settings.sticky,tags:["sticky"],groups:[{groupItems:buildBlueprints([{values:["sticky"]},{values:["sticky-top",["s","m","l"]]}])}]},{heading:"Border",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-rounded"],tags:["border","radius","rounded"],groups:[{groupItems:buildBlueprint({values:["rounded","--",[...xs_to_xxl,"50","circle"]]})}]},{heading:"List/Markers",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-marker-colors"],tags:["list","li","ul","ol","marker","dot","color"],groups:[{heading:"List Styling",groupItems:buildBlueprint({values:["list","--",["none"]]})},...getColorGroups("marker",allColors)]},{heading:"Flip",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-flip"],tags:["flip","turn","image","switch","mirror"],groups:[{groupItems:buildBlueprint({values:["flip","--",["x","y","xy"]]})}]},{heading:"Breakout",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-breakouts"],tags:["breakout","large","overflow","full-width","viewport"],groups:[{groupItems:buildBlueprint({values:["breakout","--",["full","xl","l","m","s"]]})}]},{heading:"Flex",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-flex"],tags:["flex","reverse","row","column","wrap","flex-wrap"],groups:[{groupItems:buildBlueprints([{values:["flex","--",["col","row","col-reverse","row-reverse"]],options:[["",...breakpoints]]},{values:["flex","--","wrap"]}])}]},{heading:"Visibility",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-visibility"],tags:["visibility","hidden","visible","hide","show"],groups:[{groupItems:buildBlueprint({values:["visibility","--",["hidden","visible"]],options:[["",...breakpoints]]})}]},{heading:"Frames",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-frames"],tags:["frames","content","gap","space","container","pad","fr"],groups:[{heading:"Background",groupItems:buildBlueprint({values:["fr-bg","--",["light","dark"]]})},{heading:"Text",groupItems:buildBlueprint({values:["fr-text","--",["light","dark"]]})},{heading:"Padding",groupItems:buildBlueprint({values:["fr","-","hero-padding"]})},{heading:"Lede Width",groupItems:buildBlueprint({values:["fr","-","lede"]})}]}]};export{contextMenu};