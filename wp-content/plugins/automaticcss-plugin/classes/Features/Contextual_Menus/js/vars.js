import{getVarColorGroupsWithTransparencies,allColors,buildBlueprint,xs_to_xxl,buildBlueprints,transparencies,getVarColorInlineStyles,getContextualVarColorGroups,contextualColors,updateDefaultBlueprint}from"./helpers.js";updateDefaultBlueprint({options:[""],getClasses:()=>[""],getInlineStyles:e=>"",getDisplayText:e=>{e="-"===e.slice(-1)[0].charAt(0)?e.slice(-1)[0].substring(1):e.slice(-1)[0];return e.length<4?e.toUpperCase():e},getValue:e=>"var(--"+e.join("")+")",getTooltip:e=>e.join("")});let getVarContextMenu=()=>({sections:[{heading:"Colors",tags:["colors","color"],groups:[...getVarColorGroupsWithTransparencies(allColors),...getContextualVarColorGroups(contextualColors),{heading:"Black & White",classes:["plstr-color-group"],groupItems:buildBlueprint({values:[["black","white"]],getDisplayText:()=>"",getInlineStyles:e=>getVarColorInlineStyles(e),options:[["",...transparencies.map(e=>"-"+e)]]})},{heading:"Frames",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-frames"],groupItems:buildBlueprint({values:["fr","-",["bg-dark","bg-light","text-dark","text-light"]]})}]},{heading:"Typography",tags:["font-size","typography"],groups:[{groupItems:buildBlueprint({values:["text","-",xs_to_xxl]})},{heading:"heading sizes",groupItems:buildBlueprint({values:["h",["1","2","3","4","5","6"]]})}]},{heading:"Width",tags:["width"],groups:[{groupItems:buildBlueprint({values:["width","-",xs_to_xxl]})},{heading:"frames",groupItems:buildBlueprint({values:["fr-","lede-width"]})}]},{heading:"Grid",tags:["grid"],filter:"grid",groups:[{groupItems:buildBlueprints([{values:["grid","-",["1","2","3","4"]]},{values:["grid","-",["5"]]},{values:["grid","-",["6"]]}])},{groupItems:buildBlueprint({values:["grid","-",["1-2","1-3","2-3","3-2","2-1","3-1"]]})},{heading:"Auto",groupItems:buildBlueprint({values:["grid","-auto-",["1","2","3","4","5","6","1-2","1-3","2-3","3-2","2-1","3-1"]]})}]},{heading:"Space",tags:["space","gap","padding","margin","height"],groups:[{groupItems:buildBlueprint({values:["space","-",xs_to_xxl]})},{heading:"Paragraph & Heading",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-paragraph-fix"],groupItems:buildBlueprint({values:[["paragraph","heading"],"-","spacing"],getDisplayText:e=>e[0].charAt(0)+e[1]+e[2]})},{heading:"Gaps",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-gaps"],tags:["gap"],groupItems:buildBlueprint({values:[["content-gap","container-gap","grid-gap"]]})},{heading:"frames",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-frames"],groupItems:buildBlueprint({values:[["fr-card-gap"]]})},{filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-frames"],groupItems:buildBlueprint({values:["fr-","-",["list-pad-y","list-pad-x"]]})},{filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-frames"],groupItems:buildBlueprint({values:["fr","-","hero-padding"]})}]},{heading:"Section",tags:["section","spacing"],groups:[{heading:"section",groupItems:buildBlueprint({values:["section","-","space","-",xs_to_xxl]})},{groupItems:buildBlueprint({values:["section","-","padding-x"],getDisplayText:()=>"left/right section padding"})}]},{heading:"Header",tags:["header","height"],groups:[{heading:"header",groupItems:buildBlueprint({values:["header-height"]})}]},{heading:"Radius",tags:["radius"],groups:[{groupItems:buildBlueprint({values:["radius","-",[...xs_to_xxl,"50","circle"]]})},{heading:"frames",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-frames"],groupItems:buildBlueprint({values:["fr-","card-radius"]})}]},{heading:"Button",tags:["button"],groups:[{groupItems:buildBlueprints([{values:["btn","-",["pad-x","pad-y","radius","border-size"]]},{values:["outline-btn","-","border-size"]}])}]},{heading:"Frames Card",filterIfFalse:()=>"on"===p_acssSettings_object.settings["option-frames"],tags:["frames","space","radius","border-size","border-color"],groups:[{groupItems:buildBlueprint({values:["fr-card","-",["padding","radius","avatar-radius","border-size","border-color","border-style"]]})}]},{heading:"Box Shadows",tags:["box-shadow","shadow","box-shadows"],groups:[{groupItems:buildBlueprint({values:["box-shadow","-",[p_acssSettings_object.settings["box-shadow-1-name"],p_acssSettings_object.settings["box-shadow-2-name"],p_acssSettings_object.settings["box-shadow-3-name"]]]})}]}]});export{getVarContextMenu};