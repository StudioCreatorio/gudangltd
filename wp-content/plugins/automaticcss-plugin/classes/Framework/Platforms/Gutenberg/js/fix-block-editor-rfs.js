!function(){function r(){const o=wp.data.select,n=wp.data.subscribe;return new Promise(e=>{const t=n(()=>{(o("core/editor").isCleanNewPost()||0<o("core/block-editor").getBlockCount())&&(t(),e())})})}document.addEventListener("DOMContentLoaded",()=>{if(automatic_css_block_editor_options){let c=automatic_css_block_editor_options.root_font_size+"%";r().then(e=>{console.log("Editor is ready");var t,o=document.querySelector("iframe[name=editor-canvas]"),n=document.querySelector(".editor-styles-wrapper");o?(console.log("Site editor detected"),t=c,async function(){await r();const t=document.querySelector('[name="editor-canvas"]');return new Promise(e=>{t.loading||e(t),t.onload=()=>{e(t)}})}().then(e=>{(e.contentDocument.querySelector("html")||e.contentWindow.document.querySelector("html")).style.fontSize=t})):n&&(console.log("Post editor detected"),o=c,document.querySelector("html").style.fontSize=o)})}else console.error("automatic_css_block_editor_options is not defined. Please check if the plugin is activated.")})}();