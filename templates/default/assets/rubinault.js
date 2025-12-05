jQuery.extend({

    tmpl: {
        container: '.rbno-main',
        header: '.rbno-header',
        footer: '.rbno-footer',
        menu: '#mainmenu',
        toaster: '#rbno-toast',
        overlay: '#rbno-overlay',
        modal: '#rbno-modal',
        linkclass: '.framed',
    },
    rbno: {
        livesite: '',        
        labels: {},
        cache: {},
        debug: true,
        timer: null,
        online: false,
        logged: false,

        init: function (url) {
            this.livesite = url;
            $.ajaxSetup({ timeout: 5000 });

            if (!localStorage.getItem('rbno-labels')) {
                $.getJSON(this.livesite + '/?task=labels&format=json', (data) => {
                    localStorage.setItem('rbno-labels', JSON.stringify(data));
                    for (let key in data) {
                        if (data.hasOwnProperty(key)) {
                            $.rbno.labels[key.toLocaleLowerCase()] = data[key];
                        }
                    }
                });
            } else {
                data = JSON.parse(localStorage.getItem('rbno-labels'));
                for (let key in data) {
                    if (data.hasOwnProperty(key)) {
                        $.rbno.labels[key.toLocaleLowerCase()] = data[key];
                    }
                }
            }

            this.showMenu();
            this.framed();
            this.setlayout();
            this.lazyimage();

            //Keep alive
            setInterval(() => {
                $.getJSON($.rbno.livesite + '/?task=keepalive&format=json', function (data) {
                    if (data.success) {
                        console.log(data.message);
                    } else {
                        top.document.location.href = $.rbno.livesite;
                    }
                });
            }, 300 * 1000);

            //Hide modal
            $($.tmpl.modal).on('hide.bs.modal', function () {
                $(this).find($.tmpl.playlist.form.submit).off('click');
                $(this).find('.modal-body').empty();
            });

        },
        qs: function (key, value = null) {
            key = key.replace(/[*+?^$.\[\]{}()|\\\/]/g, "\\$&"); // escape RegEx meta chars
            var match = location.search.match(new RegExp("[?&]" + key + "=([^&]+)(&|$)"));
            if (value === null) {
                return match && decodeURIComponent(match[1].replace(/\+/g, " "));
            } else {
                let params = new URLSearchParams(location.href);
                params.set(key, value);
                return decodeURIComponent(params.toString());
            }
        },
        token: function (tokenName = null) {
            if (!tokenName) tokenName = 'token';
            $.get($.rbno.livesite + '/?task=token&name=' + tokenName).done(function (data) {
                let input = $('input#' + tokenName);
                input.attr('name', data[tokenName]);
                input.val(data.sid);
            });
        },
        login: function (selector) {
            $(selector).on('click', function (e) {
                e.preventDefault();
                const username = $('input#usr').val();
                const password = $('input#pwd').val();
                const token = $('input#token').attr('name');
                const sid = $('input#token').val();

                data = { 'username': username, 'password': password, [token]: sid };
                const posting = $.post($.rbno.livesite + '/?task=login', data);
                posting.done(function (result) {
                    $.rbno.toast(result.message, result.error);
                    if (result.error) {
                        $.rbno.token();
                    } else {
                        setTimeout(1000, top.document.location.href = $.rbno.livesite);
                    }
                });
            });
        },
        framed: (object = null, container = null) => {

            if (!object) object = $($.tmpl.linkclass);
            if (!container) container = $.tmpl.container;

            object.each(function () {
                let href = $(this).attr('href');
                $(this).off('click').on('click', function (event) {
                    event.preventDefault();
                    if ($.rbno.timer) clearInterval($.rbno.timer);
                    $($.tmpl.overlay).removeClass('d-none');;
                    $.rbno.go(href, object, container, function () {
                        $($.tmpl.overlay).addClass('d-none');
                    });
                    return false;
                });
            });
        },
        lazyimage: () => {
            $('img[data-remote]:visible,img.owl-image[data-remote]').each(function () {
                let img = $(this);
                let src = img.attr('src');
                let url = img.attr('data-remote');
                if (url && url != src) {
                    $.ajax({
                        url: url,
                        cache: false,
                        type: 'get',
                        dataType: 'json',
                        success: function (data, status, xhr) {
                            img.removeClass('spinner-border');
                            img.attr('src', data.path);
                        },
                        error: function (xhr, status, error) {
                            img.removeClass('spinner-border');
                            img.attr('src', 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=');
                        },
                        complete: function (xhr, status) {
                            img.attr('data-remote', null);
                        }
                    });
                } else {
                    img.removeClass('spinner-border');
                    img.attr('data-remote', null);
                }
            });
        },
        go: function (href, object = null, container = null, callback = null) {

            if (!this.logged) {
                top.document.location.href = $.rbno.livesite + '/?task=login';
                return;
            }

            if (!object) object = $($.tmpl.linkclass);
            if (!container) container = $.tmpl.container;

            $(container).load(href, function () {
                const menu = bootstrap.Collapse.getInstance($($.tmpl.menu).get(0));
                if (menu) menu.hide();

                $(container).scrollTop();
                $(container).show();

                window.history.pushState({ 'href': href, 'container': container }, null, href);
                $.rbno.showMenu();
                $.rbno.framed(object, container);
                $.rbno.setlayout();
                $.rbno.lazyimage();

                if (callback) callback();
            });
        },
        showMenu: () => {
            try {
                let url = new URL(window.history.state ? window.history.state['href'] : top.location.href);
                let vin = url.searchParams.get('vin');
                if (vin != null) {
                    //Highlight the selected VIN
                    $('#mainmenu').find('a').each(function () {
                        const url = new URL($(this).attr('href'));
                        if (url.searchParams.get('vin') == vin) {
                            $(this).addClass('text-warning');
                        } else {
                            $(this).removeClass('text-warning');
                        }
                    });

                    //Show the menu if any VIN selected
                    $('footer').removeClass('d-none').addClass('d-inline');

                    //Disable the links if not applicable to the selected VIN
                    $.get($.rbno.livesite + '/?task=view.availability&vin=' + vin + '&format=json', (response) => {
                        $('footer').find('a').each(function () {
                            const link = $(this);
                            const task = link.attr('data-task');
                            const scope = $(this).attr('data-scope'); 
                            link.find('span').addClass('text-secondary');                       
                            link.attr('href','#').off('click');
                            $.map(response, function (value, key) {
                                if (scope == undefined || (value && key == scope)) {
                                    link.attr('href', $.rbno.livesite + '/?task=' + task + '&vin=' + vin);
                                    link.find('span').removeClass('text-secondary');
                                    $.rbno.framed(link);
                                }
                            });
                        });
                    });
                } else {
                    $('footer').removeClass('d-inline').addClass('d-none');
                    $('footer').find('a').each(function () {
                        $(this).attr('href', '#');
                        $(this).off('click');
                    });
                }


            } catch (e) { };
        },
        toast: function (text, error = false) {
            const wrapper = $($.tmpl.toaster);
            const toast = wrapper.find('.toast:first').clone();
            toast.addClass('temp');
            toast.find('.toast-body').html(text);
            toast.addClass(error ? 'bg-danger' : 'bg-success');
            toast.appendTo('body');
            const tbs = bootstrap.Toast.getOrCreateInstance(toast.get(0));
            tbs.show();
            setTimeout(function () { $('.toast.temp').remove() }, 2000);
        },
        setlayout: function () {
            $('button#layout-list').on('click', function (e) {
                e.preventDefault();
                href = $.rbno.qs('layout', 'list');
                $.rbno.go(href);
            });

            $('button#layout-grid').on('click', function (e) {
                e.preventDefault();
                href = $.rbno.qs('layout', 'grid');
                $.rbno.go(href);
            });

            $('button#go-parent').on('click', function (e) {
                e.preventDefault();
                href = $(this).attr('data-url');
                $.rbno.go(href);
            });
        },
        isJSON: function (string) {
            try {
                JSON.parse(string);
            } catch (e) {
                return false;
            }
            return true;
        },
        sleep: function (ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        },
        extractErrors: function (value, messages = '') {
            if (!value) {
                return messages;
            } else if (typeof value === 'string') {
                if ($.rbno.isJSON(value)) {
                    let buffer = JSON.parse(value);
                    $.each(buffer.errors, function (i, err) {
                        messages += $.rbno.extractErrors(err.errorMessage, messages);
                    });
                } else {
                    messages += value;
                }
            } else {
                try {
                    $.each(value.errors, function (i, err) {
                        messages += $.rbno.extractErrors(err.errorMessage, messages);
                    });
                } catch (e) {
                    messages += JSON.stringify(value);
                }
            }
            return messages;
        },
        kamereon: function (object, callback = null) {
            $(object).each(function () {
                const method = $(this).attr('data-method') || 'get';
                const endpoint = $(this).attr('data-endpoint') || null;
                const payload = $(this).data('payload') || {};

                if (endpoint) {
                    $(this).off('click').on('click', function(e) {
                        $.ajax({
                            url: $.rbno.livesite + '/index.php?task=remote.' + method + '&vin=' + $.rbno.qs('vin') + '&endpoint=' + endpoint,
                            method: method,
                            data : payload,
                            dataType: 'json',
                            success: (response) => {
                                if ($.rbno.debug) console.log(response);
                                if (response.success) {
                                    let message = $.rbno.labels['success'];
                                    if (response.message) message = response.message;
                                    $.rbno.toast(message, false);
                                } else {
                                    let message = $.rbno.extractErrors(response);
                                    if (message) $.rbno.toast(message, true);
                                }
                                if (callback) return callback(response);
                                else return response.success;
                            }
                        }).fail(function (response) {
                            let messages = $.rbno.extractErrors(response);
                            $.rbno.toast(messages, true);
                        });
                    });
                }
            });
        },
        mapping: function(command){
            const vin = $.rbno.qs('vin');
            $.get($.rbno.livesite+'/?task=view.model&format=json',(response) =>{
                if(response.success){
                    $.getJSON(response.mapping,(data)=>{
                        if(data.Action.hasOwnProperty(command)){
                            let jsonData = JSON.parse(data.Action);
                            return jsonData[command]['name'];
                        }                                                
                    });
                }
                return null
            });
            
        },
    }
});

