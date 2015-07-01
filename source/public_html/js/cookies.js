function readCookie(name) {
	var name = name + "=";
	var cookies = document.cookie.split(';');
	for (var i in cookies) {
		var c = cookies[i];
		c = c.replace(/^\s+|\s+$/g, ''); // trim whitespace
		if (c.indexOf(name) == 0) {
                        return c.substring(name.length, c.length);
                }
	}

	return null;
}

function setCookie(name, value) {
        var d = new Date();
        d.setFullYear(2037);
        document.cookie = name + '=' + value + '; expires=' + d.toUTCString() + '; path=/';
}