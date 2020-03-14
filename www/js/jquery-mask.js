(function ($) {
    "use strict";
    var Mask = function (el, mask, options) {
        var jMask = this, old_value;
        el = $(el);

        mask = typeof mask === "function" ? mask(el.val(), undefined, el,  options) : mask;

        jMask.init = function() {
            options = options || {};

            jMask.byPassKeys = [9, 16, 17, 18, 36, 37, 38, 39, 40, 91];
            jMask.translation = {
                '0': {pattern: /\d/},
                '9': {pattern: /\d/, optional: true},
                '#': {pattern: /\d/, recursive: true},
                'A': {pattern: /[a-zá-úA-ZÁ-Ú0-9]/},
                'S': {pattern: /[a-zá-úA-ZÁ-Ú]/},
                
                'X': {pattern: /[a-zá-úA-ZÁ-Ú0-9 _\-+=.,;?!&|\/]/, optional: true},
				'Y': {pattern: /[a-zá-úA-ZÁ-Ú0-9_\-+=.,;?!&|\/]/, optional: true},
				'Z': {pattern: /[a-zá-úA-ZÁ-Ú0-9_\-+=.,;?!&|\/]/, optional: false}
            };
            
            if(el.is('textarea')) {
                options.multiline = true;
			}

            jMask.translation = $.extend({}, jMask.translation, options.translation);
            jMask = $.extend(true, {}, jMask, options);

            el.each(function() {
                if (options.maxlength !== false) {
                    el.attr('maxlength', mask.length);
                }

                el.attr('autocomplete', 'off');
                p.destroyEvents();
                p.events();
                p.val(p.getMasked());
            });
        };

        var p = {
            getCaret: function () {
                var sel,
                    pos = 0,
                    ctrl = el.get(0),
                    dSel = document.selection,
                    cSelStart = ctrl.selectionStart;

                // IE Support
                if (dSel && !~navigator.appVersion.indexOf("MSIE 10")) {
                    ctrl.focus();
                    sel = dSel.createRange ();
                    sel.moveStart ('character', -ctrl.value.length);
                    pos = sel.text.length;
                } 
                // Firefox support
                else if (cSelStart || cSelStart === '0') {
                    pos = cSelStart;
                }
                
                return pos;
            },
            setCaret: function(pos) {
                var range, ctrl = el.get(0);

                if (ctrl.setSelectionRange) {
                    ctrl.focus();
                    ctrl.setSelectionRange(pos,pos);
                } else if (ctrl.createTextRange) {
                    range = ctrl.createTextRange();
                    range.collapse(true);
                    range.moveEnd('character', pos);
                    range.moveStart('character', pos);
                    range.select();
                }
            },
            events: function() {
                el.on('keydown.mask', function() {
                    old_value = p.val();
                });
                el.on('keyup.mask', p.behaviour);
                el.on("paste.mask drop.mask", function() {
                    setTimeout(function() {
                        el.keydown().keyup();
                    }, 100);
                });
            },
            destroyEvents: function() {
                el.off('keydown.mask keyup.mask paste.mask drop.mask');
            },
            val: function(v) {
                var isInput = el.is('input') || el.is('textarea');
                return arguments.length > 0 
                    ? (isInput ? el.val(v) : el.text(v)) 
                    : (isInput ? el.val() : el.text());
            },
            behaviour: function(e) {
                e = e || window.event;
                var keyCode = e.keyCode || e.which;
                if ($.inArray(keyCode, jMask.byPassKeys) === -1) {

                    var changeCaret, caretPos = p.getCaret();
                    if (caretPos < p.val().length) {
                        changeCaret = true;
                    }

                    var new_val = p.getMasked();
                    if (new_val !== p.val())               
                        p.val(new_val);

                    // change caret but avoid CTRL+A
                    if (changeCaret && !(keyCode === 65 && e.ctrlKey)) {
                        p.setCaret(caretPos);     
                    }

                    return p.callbacks(e);
                }
            },
            getMasked: function (skipMaskChars) {
                var buf = [],
                    value = p.val(),
                    m = 0, maskLen = mask.length,
                    v = 0, valLen = value.length,
                    offset = 1, addMethod = "push",
                    resetPos = -1,
                    lastMaskChar,
                    check;
                
                if (options.multiline) {
                	var lines = mask.split('\n'),
                		new_lines = [],
                		val_lines = value.split('\n'),
                		n_lines = val_lines.length + 1;
                	for(var i=1;i <= n_lines;i++){
                		new_lines.push(lines[0]);
					}
                	mask = new_lines.join('\n');
                	
		            if (options.maxlength !== false) {
		                el.attr('maxlength', mask.length);
		            }
				}

                if (options.reverse) {
                    addMethod = "unshift";
                    offset = -1;
                    lastMaskChar = 0;
                    m = maskLen - 1;
                    v = valLen - 1;
                    check = function () {
                        return m > -1 && v > -1;
                    };
                } else {
                    lastMaskChar = maskLen - 1;
                    check = function () {
                        return m < maskLen && v < valLen;
                    };
                }

                while (check()) {
                    var maskDigit = mask.charAt(m),
                        valDigit = value.charAt(v),
                        translation = jMask.translation[maskDigit];

                    if (translation) {
                        if (valDigit.match(translation.pattern)) {
                            buf[addMethod](valDigit);
                             if (translation.recursive) {
                                if (resetPos === -1) {
                                    resetPos = m;
                                } else if (m === lastMaskChar) {
                                    m = resetPos - offset;
                                }

                                if (lastMaskChar === resetPos) {
                                    m -= offset;
                                }
                            }
                            m += offset;
                        } else if (translation.optional) {
                            m += offset;
                            v -= offset;
                        }
                        v += offset;
                    } else {
                        if (!skipMaskChars) {
                            buf[addMethod](maskDigit);
                        }
                        
                        if (valDigit === maskDigit) {
                            v += offset;
                        }

                        m += offset;
                    }
                }
                
                var lastMaskCharDigit = mask.charAt(lastMaskChar);
                if (maskLen === valLen + 1 && !jMask.translation[lastMaskCharDigit]) {
                    buf.push(lastMaskCharDigit);
                }
                
                return buf.join("");
            },
            callbacks: function (e) {
                var val = p.val(),
                    changed = p.val() !== old_value;
                if (changed === true) {
                    if (typeof options.onChange === "function") {
                        options.onChange(val, e, el, options);
                    }
                }

                if (changed === true && typeof options.onKeyPress === "function") {
                    options.onKeyPress(val, e, el, options);
                }

                if (typeof options.onComplete === "function" && val.length === mask.length) {
                    options.onComplete(val, e, el, options);
                }
            }
        };

        // public methods
        jMask.remove = function() {
          p.destroyEvents();
          p.val(jMask.getCleanVal()).removeAttr('maxlength');
        };

        // get value without mask
        jMask.getCleanVal = function() {
           return p.getMasked(true);
        };

        jMask.init();
    };

    $.fn.mask = function(mask, options) {
        return this.each(function() {
            $(this).data('mask', new Mask(this, mask, options));
        });
    };

    $.fn.unmask = function() {
        return this.each(function() {
            try {
                $(this).data('mask').remove();
            } catch (e) {}
        });
    };

    $.fn.cleanVal = function() {
        return $(this).data('mask').getCleanVal();
    };
	
	$.maskAll = function () {
		// looking for inputs with data-mask attribute
		$('*[data-mask]').each(function() {
		    var input = $(this),
		        options = {};

		    if (input.attr('data-mask-reverse') === 'true') {
		        options.reverse = true;
		    }

		    if (input.attr('data-mask-maxlength') === 'false') {
		        options.maxlength = false;
		    }

		    input.mask(input.attr('data-mask'), options);
	    });
	};
	
	$.maskAll();

})(window.jQuery);
