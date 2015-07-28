document.onkeydown = NavigateThrough;

function NavigateThrough (event)
{
	if (!document.getElementById) return;

	if (window.event) event = window.event;

	if (event.ctrlKey)
	{
		var link = null;
		var href = null;
		switch (event.keyCode ? event.keyCode : event.which ? event.which : null)
		{
			case 0x25:
				link = document.getElementById ('NextLink');
				break;
			case 0x27:
				link = document.getElementById ('PrevLink');
				break;
			case 0x26:
				link = document.getElementById ('UpLink');
				break;
			case 0x28:
				link = document.getElementById ('DownLink');
				break;
		}

		if (link && link.href) document.location = link.href;
		if (href) document.location = href;
	}			
}
