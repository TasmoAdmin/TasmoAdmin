/*!
 * jQuery Internationalization library - Message Store
 *
 * Copyright (C) 2012 Santhosh Thottingal
 *
 * jquery.i18n is dual licensed GPLv2 or later and MIT. You don't have to do anything special to
 * choose one license or the other and you don't have to notify anyone which license you are using.
 * You are free to use UniversalLanguageSelector in commercial projects as long as the copyright
 * header is left intact. See files GPL-LICENSE and MIT-LICENSE for details.
 *
 * @licence GNU General Public Licence 2.0 or later
 * @licence MIT License
 */
!function(e){"use strict";var s=function(){this.messages={},this.sources={}};s.prototype={load:function(s,t){var n=null,r=[],o=this;if("string"==typeof s)return e.i18n.log("Loading messages from: "+s),function(s){var t=e.Deferred();return e.getJSON(s).done(t.resolve).fail((function(n,r,o){e.i18n.log("Error in loading messages from "+s+" Exception: "+o),t.resolve()})),t.promise()}(s).done((function(e){o.set(t,e)})).promise();if(t)return o.set(t,s),e.Deferred().resolve();for(n in s)Object.prototype.hasOwnProperty.call(s,n)&&(t=n,r.push(o.load(s[n],t)));return e.when.apply(e,r)},set:function(s,t){this.messages[s]?this.messages[s]=e.extend(this.messages[s],t):this.messages[s]=t},get:function(e,s){return this.messages[e]&&this.messages[e][s]}},e.extend(e.i18n.messageStore,new s)}(jQuery);