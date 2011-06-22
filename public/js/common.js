$(document).ready(function() {
	$("ul.sf-menu").superfish();
	$(".data tbody tr:even").css("background-color", "#eff");
	$(".data tbody tr:odd").css("background-color", "#fee");
	$(".deactivated").attr("href","javascript:;");
});

function notImplemented() {
	new SimpleDialog("Confirm", "This function is not available yet", "OK").show();
};


/**
 * Change window.location.href using the specified action, controller
 * and module to generate a URL. All parameters are optional; the
 * current action, controller and/or module will be used if you don't
 * specify one). You can pass extra arguments after the module
 * argument. These will alternately be taken to be a parameter key
 * and a parameter value.
 * 
 * @param action default: current action
 * @param controller default: current controller
 * @param module default: current module
 * 
 */
function gotoAction(action,controller,module) {
	if(!action) action = mvcAction;
	if(!controller) controller = mvcController;
	if(!module) module = mvcModule;
	params = "";
	if(arguments.length > 3) {
		for(var i = 3; i < arguments.length; ++i) {
			params += "/" + arguments[i];
		}
	}
	window.location.href = baseUrl(module + '/' + controller + '/' + action + params);
};

/**
 * The default action when clicking the Save button or selecting
 * the File->Save menu item
 */
function saveObject() {
	document.forms[0].submit();
};

/**
 * The default action when clicking the Search button
 * the File->Save menu item
 */
function query() {
	notImplemented();
	return;
	document.forms[0].elements.sortColumn.value = "";
	document.forms[0].elements.sortDirection.value= "";
	document.forms[0].submit();
};

/**
 * The default action when clicking the sort icon next
 * a column name
 */
function sort(column) {
	notImplemented();
	return;
	var sortColumn = document.forms[0].elements.sortColumn;
	var sortDirection = document.forms[0].elements.sortDirection;
	if(sortColumn.value == column) {
		if(sortDirection.value == 'DESC') {
			sortDirection.value = 'ASC';
		}
		else {
			sortDirection.value = 'DESC';
		}
	}
	else {
		sortColumn.value = column;
		sortDirection.value = 'ASC';
	}
	document.forms[0].submit();
};

/**
 * Shows the About dialog in the Help menu.
 */
function showAboutDialog() {
	var dialog = new SimpleDialog("About");
	dialog.addButton("OK",SimpleDialog.CLOSE_BUTTON);
	dialog.addMessage("ETI Blank", "center");
	dialog.addMessage("Version 0.1.r177", "center");
	dialog.show();
};

function showGlobalProjectDialog() {
	var dialog = new SimpleDialog("Active GSD");
	dialog.addButton("Change GSD",function() {
		gotoAction("unset-global-project","project","home");
	});	
	dialog.addButton("OK",SimpleDialog.CLOSE_BUTTON);
	dialog.loadHTML(baseUrl("home/project/show-global-project"));
	dialog.show();
};

function message(text) {
	var div = $('<div class="info message">' + text + '</div>');
	$("#messages").empty().append(div);
};

function warning(text) {
	var div = $('<div class="warning message">' + text + '</div>');
	$("#messages").empty().append(div);
};

function error(text) {
	var div = $('<div class="error message">' + text + '</div>');
	$("#messages").empty().append(div);
};

function generateKey() {
	var domain = encodeURIComponent($('#domain').val());
	var email = encodeURIComponent($('#email').val());
	$.get(
		baseUrl('/api/index/generate-key?domain=' + domain + '&email=' + email),
		function(response) {
			var tr = $('<tr>');
			tr.append('<td class="right-aligned">Your Key</td>');
			var input = $('<input style="width:300px;">');
			input.val(response);
			tr.append($('<td>').append(input));
			$('#keyform-container').append(tr);
			$('#button-container0').hide();
		}
	);
};
