jQuery(function(){function e(h){if(typeof wp_shifty.i18n[h]!=="undefined"){return wp_shifty.i18n[h]}return h}function g(h){h=h||"body";jQuery(h).addClass("wpshifty-is-loading")}function c(h){if(h){jQuery(h).removeClass("wpshifty-is-loading")}else{jQuery(".wpshifty-is-loading").removeClass("wpshifty-is-loading")}}function f(k){var j=0;if(k.length==0){return j}for(var h=0;h<k.length;h++){var l=k.charCodeAt(h);j=((j<<5)-j)+l;j=j&j}return j.toString(16)}function a(j,i){var k=f(j);if(jQuery("#"+k).length==0){var h=jQuery("<div>",{id:k,text:j,"class":"wpshifty-status wpshifty-status-"+i});jQuery(".wpshifty-status-wrapper").prepend(h);setTimeout(function(){jQuery(h).addClass("disappearing")},2000);setTimeout(function(){jQuery(h).remove()},3000)}}function d(i){jQuery("body").addClass("wpshifty-popup-overlay");jQuery(".wpshifty-popup").addClass("opened");jQuery(".wpshifty-popup-inner p").text(i);var h=jQuery.Deferred();jQuery('.wpshifty-popup a[href="#yes"]').one("click",function(){h.resolve()});jQuery('.wpshifty-popup a[href="#no"]').one("click",function(){h.reject()});return h.promise()}function b(){jQuery("body").removeClass("wpshifty-popup-overlay");jQuery(".wpshifty-popup").removeClass("opened");jQuery(".wpshifty-popup-inner p").empty()}jQuery(document).on("click",".wpshifty-scenario-update-status",function(j){j.preventDefault();g();var i=jQuery(this).closest(".wpshifty-scenario");var h=(jQuery(this).hasClass("wpshifty-activate-scenario")?"active":"draft");jQuery.post(ajaxurl,{action:"wpshifty_update_scenario_status",scenario:jQuery(i).attr("data-id"),status:h,nonce:wp_shifty.nonce},function(k){c();if(k.error){a(k.error,"error")}else{jQuery(i).removeClass("wpshifty-scenario-draft wpshifty-scenario-active");jQuery(i).addClass("wpshifty-scenario-"+k.status)}}).error(function(k){if(k.status){switch(k.status){case 400:a(e("Session expired. Please refresh the page and try again."),"error");break;case 403:a(e("Request has been blocked. Please check your security plugin or firewall settings"),"error");break}}else{a(e("Unknown error occured. Please refresh the page and try again."),"error")}c()})});jQuery(document).on("keyup change",".wpshifty-search-scenario input",function(j){var i=(j.keyCode||j.which||"");var h=jQuery(this).val().toLowerCase();jQuery(".wpshifty-scenario").removeClass("wpshifty-hidden");if(h!==""&&i!=27){jQuery(".wpshifty-scenario").addClass("wpshifty-hidden");jQuery(".wpshifty-scenario").each(function(){var k="";jQuery(this).find(".wpshifty-what-summary-list").each(function(){jQuery(this).find("li").each(function(){k+=jQuery(this).text().toLowerCase()})});jQuery(this).find(".wpshifty-rule-where").each(function(){jQuery(this).find("span").each(function(){k+=jQuery(this).text().toLowerCase()})});if(k.match(h)){jQuery(this).removeClass("wpshifty-hidden")}})}});jQuery(document).on("click",".wpshifty-delete-scenario",function(i){i.preventDefault();var h=jQuery(this).closest(".wpshifty-scenario");d(e("Are you sure you want to delete this item?")).then(function(){g();jQuery.post(ajaxurl,{action:"wpshifty_delete_scenario",scenario:jQuery(h).attr("data-id"),nonce:wp_shifty.nonce},function(j){c();if(j.error){a(j.error,"error")}else{jQuery(h).remove()}});b()},function(){b()})});jQuery(document).on("click",".wpshifty-duplicate-scenario",function(i){i.preventDefault();g();var h=jQuery(this).closest(".wpshifty-scenario");jQuery.post(ajaxurl,{action:"wpshifty_duplicate_scenario",scenario:jQuery(h).attr("data-id"),nonce:wp_shifty.nonce},function(j){if(j.error){c();a(j.error,"error")}else{document.location.href=j.url}}).error(function(j){if(j.status){switch(j.status){case 400:a(e("Session expired. Please refresh the page and try again."),"error");break;case 403:a(e("Request has been blocked. Please check your security plugin or firewall settings"),"error");break}}else{a(e("Unknown error occured. Please refresh the page and try again."),"error")}c()})});jQuery(document).on("click",".wpshifty-show-more",function(h){h.preventDefault();jQuery(this).closest(".wpshifty-scenario").addClass("expanded")});jQuery(document).on("click",".wpshifty-show-less",function(h){h.preventDefault();jQuery(this).closest(".wpshifty-scenario").removeClass("expanded")});jQuery(document).on("click",".wpshifty-license-status.wpshifty-badge-success",function(i){i.preventDefault();var h=this;d(e("Would you like to disconnect license?")).then(function(){b();g();jQuery.post(ajaxurl,{action:"wpshifty_disconnect",nonce:wp_shifty.nonce},function(j){if(j.error){a(j.error,"error")}}).always(function(){document.location.reload()})},function(){b()})});jQuery(document).on("click","#deactivate-wp-shifty",function(i){i.preventDefault();var h=jQuery(this).attr("href");d(e("Would you like to keep settings after uninstall WP Shifty?")).then(function(){b();g();jQuery.post(ajaxurl,{action:"wpshifty_deactivate",keep:1,nonce:wp_shifty.nonce},function(j){if(j.error){a(j.error,"error")}}).always(function(){document.location.href=h})},function(){b();g();jQuery.post(ajaxurl,{action:"wpshifty_deactivate",keep:0,nonce:wp_shifty.nonce},function(j){if(j.error){a(j.error,"error")}}).always(function(){document.location.href=h})})})});