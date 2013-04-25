Locker = {
	doAction: function(action, params, opts){
		params.token = HordeCore.conf.TOKEN;
		var _self = this;
		$.ajax({
			type: 'POST',
			url: HordeCore.conf.URI_AJAX + action,
			data: params
		}).done(function(response, status, request){
			if (response.msgs){
				_self.showNotifications(response.msgs);
			}
			opts.callback(response, status, request);
		}).error(function(){
			var msgs = [{
				message: 'Error de conexión',
				type: 'horde.error'
			}];
			_self.showNotifications(msgs);
		});
	},
	showNotifications: function(msgs){
		$.each(msgs, function(i, msg){
			if (msg.type == 'horde.error'){
				msg.type = 'error';
			} else {
				msg.type = 'success';
			}
			$.ambiance(msg);
		});
	},
	views: {}
};

Locker.views.UploadFileLine = Backbone.View.extend({
	initialize: function(attrs){
		this.xhr = attrs.xhr;
		this.data = attrs.xhr.files[0];
		this.data.fsize = this._formatFileSize(this.data.size);
		this.xhr.context = this;
	},
    _formatFileSize: function (bytes) {
        if (typeof bytes !== 'number') {
            return '';
        }
        if (bytes >= 1000000000) {
            return (bytes / 1000000000).toFixed(2) + ' GB';
        }
        if (bytes >= 1000000) {
            return (bytes / 1000000).toFixed(2) + ' MB';
        }
        return (bytes / 1000).toFixed(2) + ' KB';
    },
	render: function(){
		var tpl = _.template($('#upload-list-item').html());
		console.log(tpl);
		this.$el.html(tpl(this.data));
		return this;
	}
});
Locker.views.UploadWindow = Backbone.View.extend({
	events:{
		'click .upload-shadowbox-wrapper': 'remove',
		'click a[data-action=cancelar]': 'remove',
		'keyup .upload-shadowbox-wrapper': 'keyRemove',
		'click a[data-action=send]' : 'send',
		'click #fileupload_form': function(e){e.stopPropagation();}
	},
	send: function(){
		var data = {
			type: 'mail',
			to: this.$el.find('input[name=to]').val(),
			subject: this.$el.find('input[name=subject]').val(),
			msg: this.$el.find('textarea[name=msg]').val(),
			expire: this.$el.find('select[name=expire]').val()
			//token: HordeCore.conf.TOKEN
		};


		if (!data.to){
			alert('Debes especificar al menos un destinatario');
			return;
		}

		var all_uploaded = true;
		data.tickets = [];
		for(var i = 0; i < this.files.length;i++){

			if (!this.files[i].context.tickets){
				all_uploaded = false;
			} else {
				$.each(this.files[i].context.tickets, function(index, ticket){
					data.tickets.push(ticket);
				});
			}
		}
		if (!all_uploaded){
			alert('Todavía quedan ficheros por subir');
			return;
		}

		Locker.doAction('share', data, {
			callback: function(r){
				if (r.success){
					window.location.reload();
				} else {
					console.log(r);
				}
			}
		});
	},
	initialize: function(){
		var _self = this;
		this.files = [];
	},
	keyRemove: function(e){
		if (e.keyCode == 27){
			this.remove();
		}
	},
	remove: function(){
		this.$el.remove();
		this.undelegateEvents();
	},
	addFile: function(data){
		if (this.files.length === 0){
			this.$el.find('.dropzone').addClass('withfiles');
			this.$el.find('.hideable').show();
			this.$el.find('#upload_msg').html('Subiendo ficheros... Puedes seguir añadiendo más<br/>');
			this.$el.find('input[name=to]').focus();
		}
		var fileline = new Locker.views.UploadFileLine({xhr: data});
		this.$el.find('.uploading-zone-content').prepend(fileline.render().el);
		this.files.push(data);
		console.log(fileline.render().el);
		this._refreshProgressAll();
	},
	_refreshProgressAll: function(percent){

	},

    render: function(){
		this.$el.html(_.template($('#new-upload').html()));
		return this;
    }
});


$(function(){
	'use strict';
	var _self = this;
	window.PAGE = 0;
	$('#lockerUploadFiles').on('click', function(){
		var upload_win = new Locker.views.UploadWindow();
		$('body').append(upload_win.render().el);
		$('#fileupload').fileupload({
			url: 'api/uploader.php',
			dataType: 'json',
			add: function(e, data){
				upload_win.addFile(data);
				data.submit();
			},
            progress: function (e, data) {
                if (data.context) {
                    var progress = Math.floor(data.loaded / data.total * 100);
                    data.context.$el.find('.upload-progress')
                        .find('.bar').css(
                            'width',
                            progress + '%'
                        );
                }
            },
			done: function (e, data) {
				data.context.tickets = [];
				$.each(data.result.files, function (index, file) {
					data.context.tickets.push(file.name);
				});
			}
		});
	});
	$('.locker-list').on('click', '.icon-delete', function(e){
		var $e = $(e.currentTarget);
		var str ='';
		if ($e.data('type') == 'group'){
			if ($e.data('status') == 'active'){
				str='¿Seguro que quiere borrar este mensaje? Los ficheros asociados dejarán de estar accesibles para descarga';
			}
		} else {
			if ($e.data('status') == 'active'){
				str = '¿Seguro que quiere borrar este fichero? Tenga en cuenta que ya no podrá descargarse';
			}
		}

		if (str === '' || confirm(str)){
			Locker.doAction(($e.data('status') == 'disabled' ? 'hide':'delete'),{
				id: $e.data('id'),
				type: $e.data('type')
			},{
				callback: function(r){
					if (r.success){
						if ($e.data('status') == 'disabled'){
							$e.parent().fadeOut();
						} else {
							window.location.reload();
						}
					}
				}
			});
		}
	});
	$('.locker-list').on('click', '.group-info', function(e){
		$(e.currentTarget).parent().toggleClass('open');
	});
	$('.locker-list .group .extra').click(function(e){
		$(e.currentTarget).parent().toggleClass('open');
	});
	$('a[data-action=next-page]').click(function(e){

		$(e.currentTarget).hide();
		$(e.currentTarget).parent().find('.loading').show();
		Locker.doAction('groupPage', {
				page: (PAGE+1),
				type: $(e.currentTarget).data('type')
			},{
			callback: function(r){
				if (r.success){
					$(e.currentTarget).parent().find('.loading').hide();
					PAGE++;
					$(e.currentTarget).parent().find('.more').append(r.html);
					if (r.morepages){
						$(e.currentTarget).show();
					}
				} else {
					console.log(r);
				}
			}
		});

	});
	if (window.location.hash == '#new'){
		$('#lockerUploadFiles').trigger('click');
	}

});