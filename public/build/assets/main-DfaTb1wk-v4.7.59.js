function a(e){const t=document.getElementById(e);if(!t?.value)return[];try{return JSON.parse(t.value)}catch{return[]}}function c(e,t){const r=document.getElementById(e);r&&(r.value=JSON.stringify(t))}function l(e){return String(e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}function v(e,t){return`
        <div class="google-reviews-item box m-b-15" data-index="${t}">
            <div class="row">
                <div class="col-sm-4">
                    <label>Author</label>
                    <input type="text" class="form-control gr-item-author" value="${l(e.author||"")}">
                </div>
                <div class="col-sm-3">
                    <label>Date</label>
                    <input type="text" class="form-control gr-item-date" value="${l(e.date||"")}" placeholder="18 APR 2025">
                </div>
                <div class="col-sm-2">
                    <label>Rating</label>
                    <input type="number" class="form-control gr-item-rating" min="1" max="5" value="${e.rating||5}">
                </div>
                <div class="col-sm-2">
                    <label>Likes</label>
                    <input type="number" class="form-control gr-item-likes" min="0" value="${e.likes||0}">
                </div>
                <div class="col-sm-1 text-right">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-block gr-item-remove"><i class="fa fa-trash"></i></button>
                </div>
                <div class="col-sm-12 m-t-10">
                    <label>Review text</label>
                    <textarea class="form-control gr-item-text" rows="3">${l(e.text||"")}</textarea>
                </div>
            </div>
        </div>`}function p(e,t){return`
        <div class="google-reviews-metric box m-b-10" data-index="${t}">
            <div class="row">
                <div class="col-sm-5">
                    <label>Label</label>
                    <input type="text" class="form-control gr-metric-label" value="${l(e.label||"")}">
                </div>
                <div class="col-sm-3">
                    <label>Percent</label>
                    <input type="number" class="form-control gr-metric-percent" min="0" max="100" value="${e.percent||0}">
                </div>
                <div class="col-sm-3">
                    <label>Sentiment</label>
                    <input type="text" class="form-control gr-metric-sentiment" value="${l(e.sentiment||"")}" placeholder="Great">
                </div>
                <div class="col-sm-1 text-right">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-block gr-metric-remove"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        </div>`}function m(e){const t=document.getElementById("google-reviews-items-list");t&&(t.innerHTML=e.map((r,o)=>v(r,o)).join(""))}function d(e){const t=document.getElementById("google-reviews-metrics-list");t&&(t.innerHTML=e.map((r,o)=>p(r,o)).join(""))}function u(){return[...document.querySelectorAll(".google-reviews-item")].map(e=>({author:e.querySelector(".gr-item-author")?.value?.trim()||"",date:e.querySelector(".gr-item-date")?.value?.trim()||"",rating:parseInt(e.querySelector(".gr-item-rating")?.value||"5",10),likes:parseInt(e.querySelector(".gr-item-likes")?.value||"0",10),text:e.querySelector(".gr-item-text")?.value?.trim()||""}))}function g(){return[...document.querySelectorAll(".google-reviews-metric")].map(e=>({label:e.querySelector(".gr-metric-label")?.value?.trim()||"",percent:parseInt(e.querySelector(".gr-metric-percent")?.value||"0",10),sentiment:e.querySelector(".gr-metric-sentiment")?.value?.trim()||""}))}function n(){c("google-reviews-items-json",u())}function i(){c("google-reviews-metrics-json",g())}function f(){const e=document.getElementById("storefront-settings-edit-form");!e||e.dataset.googleReviewsBound==="1"||(e.dataset.googleReviewsBound="1",document.getElementById("google-reviews-add-item")?.addEventListener("click",()=>{const t=u();t.push({author:"",date:"",rating:5,likes:0,text:""}),m(t),n()}),document.getElementById("google-reviews-add-metric")?.addEventListener("click",()=>{const t=g();t.push({label:"",percent:50,sentiment:"Good"}),d(t),i()}),document.getElementById("google-reviews-items-list")?.addEventListener("input",n),document.getElementById("google-reviews-items-list")?.addEventListener("click",t=>{t.target.closest(".gr-item-remove")&&(t.target.closest(".google-reviews-item")?.remove(),n())}),document.getElementById("google-reviews-metrics-list")?.addEventListener("input",i),document.getElementById("google-reviews-metrics-list")?.addEventListener("click",t=>{t.target.closest(".gr-metric-remove")&&(t.target.closest(".google-reviews-metric")?.remove(),i())}),e.addEventListener("submit",()=>{n(),i()}))}function b(){const e=document.getElementById("google-reviews-items-list");e&&e.children.length===0&&m(a("google-reviews-items-json"));const t=document.getElementById("google-reviews-metrics-list");t&&t.children.length===0&&d(a("google-reviews-metrics-json"))}function s(){b(),f(),n(),i()}document.readyState==="loading"?document.addEventListener("DOMContentLoaded",s):s();$(document).on("shown.bs.tab",'a[href="#google_reviews"]',s);window.admin.removeSubmitButtonOffsetOn(["#logo","#footer","#newsletter","#product_page","#slider_banners","#three_column_full_width_banners","#brands","#two_column_banners","#three_column_banners","#one_column_banner","#google_reviews"]);$("#storefront_theme_color").on("change",e=>{e.currentTarget.value==="custom_color"?$("#custom-theme-color").removeClass("hide"):$("#custom-theme-color").addClass("hide")});$("#storefront_mail_theme_color").on("change",e=>{e.currentTarget.value==="custom_color"?$("#custom-mail-theme-color").removeClass("hide"):$("#custom-mail-theme-color").addClass("hide")});$("#storefront-settings-edit-form").on("click",".panel-image",e=>{new MediaPicker({type:"image"}).on("select",r=>{const o=$(e.currentTarget);o.find("i").remove(),o.find("img").attr("src",r.path).removeClass("hide"),o.find(".banner-file-id").val(r.id),o.find("button.remove-image").length===0&&o.append(`<button type="button" class="btn remove-image">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M6.00098 17.9995L17.9999 6.00053" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M17.9999 17.9995L6.00098 6.00055" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>`)})});$("#storefront-settings-edit-form").on("click",".remove-image",e=>{e.stopPropagation();const t=$(e.currentTarget);t.parent().prepend('<i class="fa fa-picture-o"></i>'),t.parent().find("img").removeAttr("src").addClass("hide"),t.parent().find("input").attr("value",""),t.remove()});$(".product-type").on("change",e=>{let t=$(e.currentTarget).parents(".form-group").siblings(".category-products"),r=$(e.currentTarget).parents(".form-group").siblings(".products-limit"),o=$(e.currentTarget).parents(".form-group").siblings(".custom-products");t.addClass("hide"),r.addClass("hide"),o.addClass("hide"),e.currentTarget.value==="category_products"&&t.removeClass("hide"),(e.currentTarget.value==="latest_products"||e.currentTarget.value==="recently_viewed_products"||e.currentTarget.value==="category_products")&&r.removeClass("hide"),e.currentTarget.value==="custom_products"&&o.removeClass("hide")});$(function(){$("#logo").hasClass("active")&&$("#logo").parent().find('button[type="submit"]').parent().removeClass("col-md-offset-2")});
