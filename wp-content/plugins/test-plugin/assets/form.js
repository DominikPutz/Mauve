!function n(s,u,i){function c(r,e){if(!u[r]){if(!s[r]){var t="function"==typeof require&&require;if(!e&&t)return t(r,!0);if(l)return l(r,!0);var o=new Error("Cannot find module '"+r+"'");throw o.code="MODULE_NOT_FOUND",o}var a=u[r]={exports:{}};s[r][0].call(a.exports,function(e){return c(s[r][1][e]||e)},a,a.exports,n,s,u,i)}return u[r].exports}for(var l="function"==typeof require&&require,e=0;e<i.length;e++)c(i[e]);return c}({1:[function(e,r,t){"use strict";function n(){document.querySelectorAll(".field-msg").forEach(function(e){return e.classList.remove("show")})}document.addEventListener("DOMContentLoaded",function(e){var a=document.getElementById("mauve-testimonial-form");a.addEventListener("submit",function(e){e.preventDefault(),n();var r,t,o={name:a.querySelector('[name="name"]').value,email:a.querySelector('[name="email"]').value,message:a.querySelector('[name="message"]').value,nonce:a.querySelector('[name="nonce"]').value};o.name?/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(String(o.email).toLowerCase())?o.message?(r=a.dataset.url,t=new URLSearchParams(new FormData(a)),a.querySelector(".js-form-submission").classList.add("show"),fetch(r,{method:"POST",body:t}).then(function(e){return e.json()}).catch(function(e){n(),a.querySelector(".js-form-error").classList.add("show")}).then(function(e){n(),0!==e&&"error"!==e.status?(a.querySelector(".js-form-success").classList.add("show"),a.reset()):a.querySelector(".js-form-error").classList.add("show")})):a.querySelector('[data-error="invalidMessage"]').classList.add("show"):a.querySelector('[data-error="invalidEmail"]').classList.add("show"):a.querySelector('[data-error="invalidName"]').classList.add("show")})})},{}]},{},[1]);
//# sourceMappingURL=form.js.map
