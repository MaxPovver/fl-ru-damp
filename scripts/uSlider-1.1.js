/**
 * uSlider v1.2
 * Simple Mootools CSS3 Adaptable Slider
 * MIT License <http://joseluisquintana.pe/license.txt>
 * Jose Luis Quintana <http://joseluisquintana.pe/>
 * requires: 
 * - Core: 1.4/*
 * - uSizer: 1.0 (For center crop feature)
 **/

window.uSlider = new Class({
    version: '1.2',
    options: {
        //controlnav: true,
        directionnav: true,
        keymove: true,
        autoslide: true,
        resize: true,
        delay: 5500,
        duration: 1200,
        effect: 'slide',
        transition: 'expo:out',
        imageclass: 'load',
        centercrop: false
    },
    Implements: [Events, Options],
    stackSlides: [],
    ready: false,
    image: null,
    images: null,
    prevslide: null,
    timeout: null,
    working: true,
    slidewrap: null,
    controlnav: null,
    jump: null,
    loaded: null,
    prev: 0,
    transitioncss: {
        'ease': '0.250, 0.100, 0.250, 1.000',
        'expo:in': '0.950, 0.050, 0.795, 0.035',
        'expo:out': '0.190, 1.000, 0.220, 1.000',
        'cubic:in': '0.550, 0.055, 0.675, 0.190',
        'cubic:out': '0.215, 0.610, 0.355, 1.000',
        'quart:in': '0.895, 0.030, 0.685, 0.220',
        'quart:out': '0.165, 0.840, 0.440, 1.000',
        'quad:in': '0.550, 0.085, 0.680, 0.530',
        'quad:out': '0.250, 0.460, 0.450, 0.940',
        'quint:in': '0.755, 0.050, 0.855, 0.060',
        'quint:out': '0.230, 1.000, 0.320, 1.000',
        'sine:in': '0.470, 0.000, 0.745, 0.715',
        'sine:out': '0.390, 0.575, 0.565, 1.000'
    },
    initialize: function(slider, options) {
        this.setOptions(options);
        this.slider = slider;
        this.uSizer = this.options.centercrop ? new uSizer() : null;
        this.setDefaults();
        this.setStructure();
        this.setEvents();
    },
    setDefaults: function() {
        this.slider.setStyle('overflow', 'hidden').addClass('uSlider');
        this.jump = 'start';
        this.transitions = (function() {
            var obj = document.createElement('div'),
                    props = ['perspectiveProperty', 'WebkitPerspective', 'MozPerspective', 'OPerspective', 'msPerspective'];
            for (var i in props) {
                if (obj.style[ props[i] ] !== undefined) {
                    this.upfx = '-' + props[i].replace('Perspective', '').toLowerCase();
                    return true;
                }
            }
            return false;
        }.bind(this)());
    },
    setImages: function(images) {
        this.images = images instanceof Array ? images : [images];
    },
    setStructure: function() {
        var slider = this.slider, slidewrap, images = null, img, opt = this.options, sliderWidth;
        slider.setStyle('position', 'relative');

        slidewrap = this.slidewrap = (this.images && this.images.length > 0) ? new Element('ul.uSlider-slides').inject(this.slider) :
                slider.getElement('.uSlider-slides');
        slidewrap.setStyles({
            'position': 'relative',
            'width': '1000%'
        });

        this.stackSlides = slidewrap.getElements('.b-promo__slider-item');

        if (opt.effect == 'slide') {
            slidewrap.set('tween', {
                duration: opt.duration,
                transition: opt.transition
            }).setStyle('overflow', 'hidden');

            this.stackSlides.getLast().clone().addClass('uSlider-slide-clone').inject(slidewrap, 'top');
            this.stackSlides[0].clone().addClass('uSlider-slide-clone').inject(slidewrap);
        }

        if (opt.centercrop) {
            images = slidewrap.getElements('li img.' + opt.imageclass.trim());

            if (images.length > 0) {
                images.setStyles({
                    'visibility': 'hidden',
                    'opacity': 0,
                    'display': 'none'
                });

                this.updateSizes();
            } else {
                opt.centercrop = false;
            }
        }

        slidewrap.getElements('.b-promo__slider-item').each(function(li) {
            if (opt.effect == 'slide') {
                li.setStyles({
                    'position': 'relative',
                    'float': 'left',
                    'display': 'block',
                    'overflow': 'hidden'
                });
            } else {
                li.setStyles({
                    'position': 'absolute',
                    'top': 0,
                    'opacity': 0
                });

                li.set('tween', {
                    duration: opt.duration,
                    transition: opt.transition
                });
            }

            if (opt.centercrop && images.length > 0) {
                img = li.getElement('img.' + opt.imageclass.trim());

                if (img) {
                    var src = img.get('src');
                    img.destroy();

                    img = new Element('img', {
                        'styles': {
                            'visibility': 'hidden',
                            'opacity': 0
                        }
                    }).set('tween', {
                        duration: opt.duration,
                        transition: opt.transition
                    });

                    img.inject(li);
                    img.addEvent('load', function(e) {
                        if (opt.effect === 'fade' && this.stackSlides.length == 1) {
                            li.setStyles({
                                'visibility': 'visible',
                                'opacity': 1
                            })
                        }

                        e.setStyle('visibility', 'visible').addClass('completed');
                        e.fade('in');

                        this.resize();
                    }.bind(this, img)).set('src', src);
                }
            }
        }.bind(this));

        if (opt.controlnav) {
            this.setControlNav();
        }

        if (opt.directionnav) {
            this.setDirectionNav();
        }

        this.updateSizes();
        this.working = false;
        this.ready = true;
        this.start();
    },
    updateSizes: function() {
        this.slidewrap.getElements('.b-promo__slider-item').each(function(li) {
			  var sliderWidth = getComputedStyle(this.slider,'');
            li.setStyle('width', sliderWidth.width);

            if (this.options.centercrop) {
                li.setStyle('height', this.slider.getStyle('height').toInt());
            }

        }.bind(this));
    },
    setControlNav: function() {
        if (this.stackSlides.length > 1) {
            var ol = new Element('ol.uSlider-control-nav').inject(this.slider, 'top'), li;
            this.controlnav = ol;

            this.stackSlides.each(function(el, i) {
                li = new Element('li').inject(ol);
                li.addEvent('click', function() {
                    this.load(i);
                }.bind(this));

                new Element('a').set('href', 'javascript:;').inject(li);
            }.bind(this));
        }
    },
    setDirectionNav: function() {
        if (this.stackSlides.length > 1) {
            this.directionnav = {
                'prev': new Element('a.uSlider-prev-nav', {
                    'href': 'javascript:;'
                }).setStyle('position', 'absolute').inject(this.slider, 'top').addEvent('click', this.back.bind(this)),
                'next': new Element('a.uSlider-next-nav', {
                    'href': 'javascript:;'
                }).setStyle('position', 'absolute').inject(this.slider, 'top').addEvent('click', this.next.bind(this))
            }
        }
    },
    setEvents: function() {
        if (this.options.keymove && this.stackSlides.length > 1) {
            document.addEvent('keyup', function(e) {
                if (e.code == 37) {
                    this.back();
                } else if (e.code == 39) {
                    this.next();
                }
            }.bind(this));
        }

        if (this.options.resize) {
            window.addEvent('resize', this.resize.bind(this));
        }
    },
    back: function() {
        this._move('back', null);
    },
    next: function() {
        this._move('next', null);
    },
    move: function(i) {
        this._move(null, i);
    },
    _move: function(type, i) {
        if (this.ready && !this.working && this.stackSlides.length > 1) {
            if (type == 'back') {
                this.i--;
                this.i = this.i > -1 ? this.i : this.stackSlides.length - 1;
            } else if (type == 'next') {
                this.i = this.i < this.stackSlides.length - 1 ? this.i + 1 : 0;
            }

            this.load(i || this.i);
        }
    },
    show: function(i) {
        if (this.options.controlnav) {
            this.controlnav.getElements('li.selected').removeClass('selected');
            this.controlnav.getElement('li:nth-child(' + (i + 1) + ')').addClass('selected');
        }

        this.fireEvent('show', [{
                i: i,
                slide: this.stackSlides[i]
            }]);
    },
    start: function() {
        this.load(0);
    },
    load: function(i, reset) {
        if (!this.working && this.stackSlides.length > 1) {
            this.working = true;
            clearTimeout(this.timeout);
            i = i || 0, this.i = i;
            var slide = this.stackSlides[i];

            if (reset == undefined && slide === this.prevslide) {
                this.working = false;
                this.autoslide();
                return;
            }

            this.display(i, slide);
        }
    },
    display: function(i, slide) {
        this.setEffect(this.image = slide);
        this.show(i);
    },
    autoslide: function() {
        if (this.options.autoslide) {
            clearTimeout(this.timeout);
            this.timeout = setTimeout(this.next.bind(this), this.options.delay);
        }

        this.working = false;
    },
    setEffect: function(slide) {
        this.slidewrap.getElements('.uSlider-slide-active').removeClass('uSlider-slide-active');
        this.stackSlides[this.i].addClass('uSlider-slide-active');

        if (this.options.effect === 'fade') {
            if (this.prevslide) {
                this.prevslide.tween('opacity', 0);
            }

            slide.tween('opacity', 1);

            (function() {
                this.autoslide();
            }.bind(this).delay(this.options.duration));
        } else {
            this.translation(this.i, this.options.duration / 1000);
        }

        this.prev = this.i;
        this.prevslide = slide;
    },
    translation: function(i, duration) {
        if (this.options.effect === 'slide') {
            clearTimeout(this.timeout);

            var slider = this.slider.getSize().x, target, len = this.stackSlides.length - 1,
                    limits = null, p = this.prev;

            if (this.jump == 'start') {
                duration = 0;
                target = -slider;
                this.jump = 'run';
            } else if ('run') {
                if (p == len && i == 0) {
                    target = -((slider * (p + 1)) + slider);
                    limits = -slider;
                } else if (p == 0 && i == len) {
                    target = 0;
                    limits = -((len * slider) + slider);
                } else {
                    target = -((slider * i) + slider);
                }
            }

            this.setTranslation(target, duration);

            (function() {
                if (limits !== null) {
                    this.setTranslation(limits, 0);
                }

                this.autoslide();
            }.bind(this).delay(this.options.duration));
        }
    },
    setTranslation: function(target, duration) {
        target += 'px';

        if (this.transitions) {
            var tfn = this.transitioncss[this.options.transition];
            this.slidewrap.setStyle(this.upfx + '-transition-duration', duration + 's');
            this.slidewrap.setStyle(this.upfx + '-transition-timing-function', 'cubic-bezier(' + (tfn ? tfn : this.transitioncss['ease']) + ')');
            this.slidewrap.setStyle(this.upfx + '-transform', 'translate3d(' + target + ',0,0)');
            this.slidewrap.setStyles({
                'transition-duration': duration + 's',
                'transition-timing-function': 'cubic-bezier(' + (tfn ? tfn : this.transitioncss['ease']) + ')',
                'transform': 'translate3d(' + target + ',0,0)'
            });
        } else {
            if (duration === 0) {
                this.slidewrap.setStyle('margin-left', target);
            } else {
                this.slidewrap.tween('margin-left', target);
            }
        }
    },
    resize: function(width, height) {
        (function() {
            this.working = true;
            clearTimeout(this.timeout);

            width = width ? width : parseInt(this.slider.getStyle('width')),
                    height = height ? height : parseInt(this.slider.getStyle('height'));

            this.updateSizes();

            this.slidewrap.getElements('li').each(function(slide) {
                if (this.uSizer && slide.getElement('img.completed')) {
                    this.centerCrop(slide, slide.getElement('img.completed'), width, height);
                }
            }.bind(this));

            this.translation(this.i, 0);

            (function() {
                this.autoslide();
            }.bind(this).delay(this.options.duration));
        }.bind(this).delay(300));
    },
    centerCrop: function(slide, img, width, height) {
        this.uSizer.setParent(slide);
        this.uSizer.setChild(img);

        if (this.options.resize) {
            this.uSizer.centerCropResize(width, height);
        }

        this.uSizer.centerCrop(width, height);
    }
});