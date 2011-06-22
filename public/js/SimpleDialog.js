/**
 * Simple wrapper around jQuery UI Dialog
 * 
 * @param title The text the in the title bar of the dialog.
 * @param message The text to be display in the dialog box.
 * @param close The text on the close button. If you provide this argument,
 * 		a close button will be created with the specified text; otherwise
 * 		no close button will be created.
 */
function SimpleDialog(title,message,close) {
	
	this.container = $('<div class="simple-dialog-container">');
	this.contentDiv = $('<div class="simple-dialog-content">');
	this.container.append(this.contentDiv);
	this.container.dialog(
		{
			modal : true,
			autoOpen: false,
			buttons: {}
		}
	);
	
	this.title = "";
	this.buttons = new Array();
	
	if(title) {
		this.title = title;
	}
	if(message) {
		this.setMessage(message);
	}
	if(close) {
		this.addButton(close,SimpleDialog.CLOSE_BUTTON);
	}
	
	// An array used to store arbitrary data with a particular
	// instance of a SimpleDialog.
	this.properties = [];
};

/**
 * Special value that you can pass to addButton() if you want the
 * button to do nothing else but close the dialog.
 * var dialog = new SimpleDialog();
 * dialog.addButton("Close Me!", SimpleDialog.CLOSE_BUTTON);
 */
SimpleDialog.CLOSE_BUTTON = 0;

SimpleDialog.prototype.show = function() {
	var buttons = {};
	for(var i = 0; i < this.buttons.length; ++i) {
		var props = this.buttons[i];
		if(props[1] === SimpleDialog.CLOSE_BUTTON) {
			buttons[props[0]] = function() {$(this).dialog('close')};
		}
		else {
			if(typeof props[1] === 'function') {
				buttons[props[0]] = props[1];
			}
			else {
				buttons[props[0]] = function() { eval(props[1]); };
			}
		}		
	}
	this.container.dialog('option', 'buttons', buttons);	
	this.container.dialog('option', 'title', this.title);
	this.container.dialog('open');
};

SimpleDialog.prototype.close = function() {
	this.container.dialog('close');
};

/**
 * Set the dialog's content pane through an AJAX request.
 * 
 * @param url The AJAX URL
 * @param waitMessage (optional) Message to display while waiting for AJAX request to complete
 * 
 */
SimpleDialog.prototype.loadHTML = function(url,waitMessage) {
	if(arguments.length > 1) {
		if(waitMessage) {
			this.setMessage(waitMessage);		
		}
	}
	else {
		this.setMessage("Retrieving data ...");
	}
	var me = this;
	$.get(url, function(data) {
		me.setHTML(data);
	});
};

SimpleDialog.prototype.setTitle = function(title) {
	this.title = title;
	return this;
};

/**
 * Add a button to the dialog.
 * 
 * @param text The text on the button.
 * @param onclick The function called when the button is clicked. If you pass
 * 		a string, the string will be passed to eval(). It's better to pass a
 * 		function, for example:
 * 			var dialog = new SimpleDialog();
 * 			dialog.addButton("Hello", function() { alert("Hello"); });
 */
SimpleDialog.prototype.addButton = function(text,onclick) {
	this.buttons.push([text,onclick]);
	return this;
};

SimpleDialog.prototype.setHTML = function(html) {
	this.contentDiv.empty();
	this.contentDiv.html(html);
	return this;
};

SimpleDialog.prototype.addHTML = function(html) {
	this.contentDiv.append($(html));
	return this;
};

SimpleDialog.prototype.setMessage = function(message,cssClass) {
	var html = '<p'
	if(arguments.length > 1) {
		html += (' class="' + cssClass + '"');
	}
	html += ('>' + message + '</p>');
	return this.setHTML(html);
};

SimpleDialog.prototype.addMessage = function(message,cssClass) {
	var html = '<p'
	if(arguments.length > 1) {
		html += (' class="' + cssClass + '"');
	}
	html += ('>' + message + '</p>');
	return this.addHTML(html);
};

SimpleDialog.prototype.setWidth = function(width) {
	this.container.dialog("option","width",width);
	return this;
};

SimpleDialog.prototype.setHeight = function(height) {
	this.container.dialog("option","height",height);
	return this;
};

/**
 * Whether or not the user must first make the dialog disappear
 * (by pushing a button) before he can continue working in the
 * main window.
 */
SimpleDialog.prototype.setModal = function(bool) {
	this.container.dialog("option","modal",bool);
	return this;
};

/**
 * Store arbitrary data with this particular instance
 */
SimpleDialog.prototype.setProperty = function(name,value) {
	this.properties[name] = value;
};

/**
 * Get data stored on this particular instance
 */
SimpleDialog.prototype.getProperty = function(name) {
	return this.properproperties[name];
};
