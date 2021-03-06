// Scroll to the very bottom to see the stuff we wrote, the big giant blocks are:
// froogaloop
// and
// fitvid.js


(function ($) {

        'use strict';

        $.fn.fitVids = function (options) {
            var settings = {
                customSelector: null,
                ignore: null
            };

            if (!document.getElementById('fit-vids-style')) {
                // appendStyles: https://github.com/toddmotto/fluidvids/blob/master/dist/fluidvids.js
                var head = document.head || document.getElementsByTagName('head')[0];
                var css = '.fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}';
                var div = document.createElement("div");
                div.innerHTML = '<p>x</p><style id="fit-vids-style">' + css + '</style>';
                head.appendChild(div.childNodes[1]);
            }

            if (options) {
                $.extend(settings, options);
            }

            return this.each(function () {
                var selectors = [
                    'iframe[src*="player.vimeo.com"]',
                    'iframe[src*="youtube.com"]',
                    'iframe[src*="youtube-nocookie.com"]',
                    'iframe[src*="kickstarter.com"][src*="video.html"]',
                    'object',
                    'embed',
                    'video'];

                if (settings.customSelector) {
                    selectors.push(settings.customSelector);
                }

                var ignoreList = '.fitvidsignore';

                if (settings.ignore) {
                    ignoreList = ignoreList + ', ' + settings.ignore;
                }

                var $allVideos = $(this).find(selectors.join(','));
                $allVideos = $allVideos.not('object object'); // SwfObj conflict patch
                $allVideos = $allVideos.not(ignoreList); // Disable FitVids on this video.

                $allVideos.each(function (count) {
                    var $this = $(this);
                    if ($this.parents(ignoreList).length > 0) {
                        return; // Disable FitVids on this video.
                    }
                    if (this.tagName.toLowerCase() === 'embed' && $this.parent('object').length || $this.parent('.fluid-width-video-wrapper').length) {
                        return;
                    }
                    if ((!$this.css('height') && !$this.css('width')) && (isNaN($this.attr('height')) || isNaN($this.attr('width')))) {
                        $this.attr('height', 9);
                        $this.attr('width', 16);
                    }
                    var height = (this.tagName.toLowerCase() === 'object' || ($this.attr('height') && !isNaN(parseInt($this.attr('height'), 10)))) ? parseInt($this.attr('height'), 10) : $this.height(),
                        width = !isNaN(parseInt($this.attr('width'), 10)) ? parseInt($this.attr('width'), 10) : $this.width(),
                        aspectRatio = height / width;
                    if (!$this.attr('id')) {
                        var videoID = 'fitvid' + count;
                        $this.attr('id', videoID);
                    }
                    $this.wrap('<div class="fluid-width-video-wrapper"></div>').parent('.fluid-width-video-wrapper').css('padding-top', (aspectRatio * 100) + '%');
                    $this.removeAttr('height').removeAttr('width');
                });
            });
        };
        // Works with either jQuery or Zepto
    })(window.jQuery || window.Zepto);

    // Init style shamelessly stolen from jQuery http://jquery.com
    var Froogaloop = (function () {
        // Define a local copy of Froogaloop
        function Froogaloop(iframe) {
            // The Froogaloop object is actually just the init constructor
            return new Froogaloop.fn.init(iframe);
        }

        var eventCallbacks = {},
        hasWindowEvent = false,
            isReady = false,
            slice = Array.prototype.slice,
            playerDomain = '';

        Froogaloop.fn = Froogaloop.prototype = {
            element: null,

            init: function (iframe) {
                if (typeof iframe === "string") {
                    iframe = document.getElementById(iframe);
                }

                this.element = iframe;

                // Register message event listeners
                playerDomain = getDomainFromUrl(this.element.getAttribute('src'));

                return this;
            },

            api: function (method, valueOrCallback) {
                if (!this.element || !method) {
                    return false;
                }

                var self = this,
                    element = self.element,
                    target_id = element.id !== '' ? element.id : null,
                    params = !isFunction(valueOrCallback) ? valueOrCallback : null,
                    callback = isFunction(valueOrCallback) ? valueOrCallback : null;

                // Store the callback for get functions
                if (callback) {
                    storeCallback(method, callback, target_id);
                }

                postMessage(method, params, element);
                return self;
            },

            addEvent: function (eventName, callback) {
                if (!this.element) {
                    return false;
                }

                var self = this,
                    element = self.element,
                    target_id = element.id !== '' ? element.id : null;


                storeCallback(eventName, callback, target_id);

                // The ready event is not registered via postMessage. It fires regardless.
                if (eventName != 'ready') {
                    postMessage('addEventListener', eventName, element);
                } else if (eventName == 'ready' && isReady) {
                    callback.call(null, target_id);
                }

                return self;
            },

            removeEvent: function (eventName) {
                if (!this.element) {
                    return false;
                }

                var self = this,
                    element = self.element,
                    target_id = element.id !== '' ? element.id : null,
                    removed = removeCallback(eventName, target_id);

                // The ready event is not registered
                if (eventName != 'ready' && removed) {
                    postMessage('removeEventListener', eventName, element);
                }
            }
        };

        function postMessage(method, params, target) {
            if (!target.contentWindow.postMessage) {
                return false;
            }

            var url = target.getAttribute('src').split('?')[0],
                data = JSON.stringify({
                    method: method,
                    value: params
                });

            if (url.substr(0, 2) === '//') {
                url = window.location.protocol + url;
            }

            target.contentWindow.postMessage(data, url);
        }

        function onMessageReceived(event) {
            var data, method;

            try {
                data = JSON.parse(event.data);
                method = data.event || data.method;
            } catch (e) {
                //fail silently... like a ninja!
            }

            if (method == 'ready' && !isReady) {
                isReady = true;
            }

            // Handles messages from moogaloop only
            if (event.origin != playerDomain) {
                return false;
            }

            var value = data.value,
                eventData = data.data,
                target_id = target_id === '' ? null : data.player_id,

                callback = getCallback(method, target_id),
                params = [];

            if (!callback) {
                return false;
            }

            if (value !== undefined) {
                params.push(value);
            }

            if (eventData) {
                params.push(eventData);
            }

            if (target_id) {
                params.push(target_id);
            }

            return params.length > 0 ? callback.apply(null, params) : callback.call();
        }

        function storeCallback(eventName, callback, target_id) {
            if (target_id) {
                if (!eventCallbacks[target_id]) {
                    eventCallbacks[target_id] = {};
                }
                eventCallbacks[target_id][eventName] = callback;
            } else {
                eventCallbacks[eventName] = callback;
            }
        }

        function getCallback(eventName, target_id) {
            if (target_id) {
                return eventCallbacks[target_id][eventName];
            } else {
                return eventCallbacks[eventName];
            }
        }

        function removeCallback(eventName, target_id) {
            if (target_id && eventCallbacks[target_id]) {
                if (!eventCallbacks[target_id][eventName]) {
                    return false;
                }
                eventCallbacks[target_id][eventName] = null;
            } else {
                if (!eventCallbacks[eventName]) {
                    return false;
                }
                eventCallbacks[eventName] = null;
            }

            return true;
        }

        function getDomainFromUrl(url) {
            if (url.substr(0, 2) === '//') {
                url = window.location.protocol + url;
            }

            var url_pieces = url.split('/'),
                domain_str = '';

            for (var i = 0, length = url_pieces.length; i < length; i++) {
                if (i < 3) {
                    domain_str += url_pieces[i];
                } else {
                    break;
                }
                if (i < 2) {
                    domain_str += '/';
                }
            }

            return domain_str;
        }

        function isFunction(obj) {
            return !!(obj && obj.constructor && obj.call && obj.apply);
        }

        function isArray(obj) {
            return toString.call(obj) === '[object Array]';
        }

        // Give the init function the Froogaloop prototype for later instantiation
        Froogaloop.fn.init.prototype = Froogaloop.fn;

        // Listens for the message event.
        // W3C
        if (window.addEventListener) {
            window.addEventListener('message', onMessageReceived, false);
        }
        // IE
        else {
            window.attachEvent('onmessage', onMessageReceived);
        }

        // Expose froogaloop to the global object
        return (window.Froogaloop = window.$f = Froogaloop);

    })();




////////////////////////////////////////
// Our Script 
////////////////////////////////////////
