(()=>{var e={72856:(e,t,s)=>{"use strict";s.d(t,{to:()=>i,u1:()=>n});let r="";function o(e){if("https://subscribe.wordpress.com"===e.origin&&e.data){const t=JSON.parse(e.data);if(t&&t.result&&t.result.jwt_token&&(r=t.result.jwt_token,c(r)),t&&"close"===t.action&&r)window.location.reload(!0);else if(t&&"close"===t.action){window.removeEventListener("message",o);document.getElementById("memberships-modal-window").close(),document.body.classList.remove("jetpack-memberships-modal-open")}}}function i(e){return new Promise((t=>{const s=document.getElementById("memberships-modal-window");s&&document.body.removeChild(s);const r=document.createElement("dialog");r.setAttribute("id","memberships-modal-window"),r.classList.add("jetpack-memberships-modal"),r.classList.add("is-loading");const i=document.createElement("iframe");i.setAttribute("frameborder","0"),i.setAttribute("allowtransparency","true"),i.setAttribute("allowfullscreen","true"),i.addEventListener("load",(function(){document.body.classList.add("jetpack-memberships-modal-open"),r.classList.remove("is-loading"),t()})),i.setAttribute("id","memberships-modal-iframe"),i.innerText="This feature requires inline frames. You have iframes disabled or your browser does not support them.",i.src=e+"&display=alternate&jwt_token="+a();const n=document.querySelector('input[name="lang"]')?.value;n&&(i.src=i.src+"&lang="+n),document.body.appendChild(r),r.appendChild(i),window.addEventListener("message",o,!1),r.showModal()}))}const n='<span class="jetpack-memberships-spinner">\t<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">\t\t<path d="M12,1A11,11,0,1,0,23,12,11,11,0,0,0,12,1Zm0,19a8,8,0,1,1,8-8A8,8,0,0,1,12,20Z" opacity=".25" fill="currentColor" />\t\t<path d="M10.14,1.16a11,11,0,0,0-9,8.92A1.59,1.59,0,0,0,2.46,12,1.52,1.52,0,0,0,4.11,10.7a8,8,0,0,1,6.66-6.61A1.42,1.42,0,0,0,12,2.69h0A1.57,1.57,0,0,0,10.14,1.16Z" class="jetpack-memberships-spinner-rotating" fill="currentColor" />\t</svg></span>';const a=function(){const e=`; ${document.cookie}`.split("; wp-jp-premium-content-session=");if(2===e.length)return e.pop().split(";").shift()},c=function(e){const t=new Date,s=new Date(t.setMonth(t.getMonth()+1));document.cookie=`wp-jp-premium-content-session=${e}; expires=${s.toGMTString()}; path=/`}},79366:(e,t,s)=>{"object"==typeof window&&window.Jetpack_Block_Assets_Base_Url&&(s.p=window.Jetpack_Block_Assets_Base_Url)},98490:e=>{"use strict";e.exports=window.wp.domReady}},t={};function s(r){var o=t[r];if(void 0!==o)return o.exports;var i=t[r]={exports:{}};return e[r](i,i.exports,s),i.exports}s.n=e=>{var t=e&&e.__esModule?()=>e.default:()=>e;return s.d(t,{a:t}),t},s.d=(e,t)=>{for(var r in t)s.o(t,r)&&!s.o(e,r)&&Object.defineProperty(e,r,{enumerable:!0,get:t[r]})},s.g=function(){if("object"==typeof globalThis)return globalThis;try{return this||new Function("return this")()}catch(e){if("object"==typeof window)return window}}(),s.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),(()=>{var e;s.g.importScripts&&(e=s.g.location+"");var t=s.g.document;if(!e&&t&&(t.currentScript&&"SCRIPT"===t.currentScript.tagName.toUpperCase()&&(e=t.currentScript.src),!e)){var r=t.getElementsByTagName("script");if(r.length)for(var o=r.length-1;o>-1&&(!e||!/^http(s?):/.test(e));)e=r[o--].src}if(!e)throw new Error("Automatic publicPath is not supported in this browser");e=e.replace(/#.*$/,"").replace(/\?.*$/,"").replace(/\/[^\/]+$/,"/"),s.p=e+"../"})(),(()=>{"use strict";s(79366)})(),(()=>{"use strict";var e=s(98490),t=s.n(e),r=s(72856);function o(e){const t="https://subscribe.wordpress.com/memberships/?"+new URLSearchParams(e).toString();return(0,r.to)(t)}t()((function(){const e=document.querySelector("#jp_retrieve_subscriptions_link");e&&e.addEventListener("click",(function(e){e.preventDefault(),function(){const e=document.querySelector(".wp-block-jetpack-subscriptions__container form");if(!e)return;if(!e.checkValidity())return void e.reportValidity();o({email:e.querySelector("input[type=email]").value,blog:e.dataset.blog,plan:"newsletter",source:"jetpack_retrieve_subscriptions",post_access_level:e.dataset.post_access_level,display:"alternate"})}()}));document.querySelectorAll(".wp-block-jetpack-subscriptions__container form").forEach((e=>{if(!e.payments_attached){e.payments_attached=!0;const t=e.querySelector('button[type="submit"]');t.insertAdjacentHTML("beforeend",r.u1),e.addEventListener("submit",(function(s){if(e.resubmitted)return;t.classList.add("is-loading"),t.setAttribute("aria-busy","true"),t.setAttribute("aria-live","polite");let r=e.querySelector("input[type=email]")?.value??"";!r&&e.dataset.subscriber_email&&(r=e.dataset.subscriber_email);if("subscribe"===e.querySelector("input[name=action]").value){s.preventDefault();const i=e.querySelector("input[name=post_id]")?.value??"",n=e.querySelector("input[name=tier_id]")?.value??"",a=e.querySelector("input[name=app_source]")?.value??"";o({email:r,post_id:i,tier_id:n,blog:e.dataset.blog,plan:"newsletter",source:"jetpack_subscribe",app_source:a,post_access_level:e.dataset.post_access_level,display:"alternate"}).then((()=>{e.dispatchEvent(new Event("subscription-modal-loaded")),t.classList.remove("is-loading"),t.setAttribute("aria-busy","false")}))}}))}}))}))})()})();