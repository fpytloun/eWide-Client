//part of Live Form Validation plugin for Nette Framework
//JavaScript functions for custom rendering of client-side errors
//change to your satisfaction (you can use 3rd party JS libs)

function hasClass(ele, cls) {
    var classes = ele.className.split(" ");
    for (var i=0;i<classes.length;i++)
        if (classes[i].indexOf(cls) == 0)
            return true;
    return false;
}
function addClass(ele,cls) {
	if (!this.hasClass(ele,cls)) ele.className += " "+cls;
}
function removeClass(ele,cls) {
	if (hasClass(ele,cls)) {
		var classes = ele.className.split(" ");
        ele.className = '';
        i = 0;
        for (var i=0;i<classes.length;i++)
            if (classes[i].indexOf(cls) != 0)
            {
                if(i==0) ele.className += classes[i];
                else ele.className += ' '+classes[i];
                i++;
            }
	}
}
function errorMessageElement(id, sender)
{
    var el = document.getElementById(id);
    if(!el)
    {
        el = document.createElement('span');
        el.id = id;
        var parent = sender.parentNode;

        if(parent.lastchild == sender) {
            parent.appendChild(el);
        } else {
            parent.insertBefore(el, sender.nextSibling);
        }
    }
    else
    {
        el.style.display = 'inline';
    }
    return el;
}
function addError(sender, message)
{
    addClass(sender, 'form-control-error');
    var id = sender.id+'_message';
    el = errorMessageElement(id, sender);
    el.className = 'form-error-message';
    el.innerHTML = message;
}
function removeError(sender)
{
    removeClass(sender, 'form-control-error');
    var el = document.getElementById(sender.id+'_message');
    if(el)
        el.style.display = 'none';
    onValid(sender);
}
function informError(submitter)
{
    el = errorMessageElement(submitter.id+"_message", submitter);
    el.className = "form-error-message";
    el.innerHTML = "\u0160patn\u011b vypln\u011bný formulá\u0159!"
}
function onValid(sender)
{
    var id = sender.id+'_message';
    el = errorMessageElement(id, sender);
    el.className = 'form-valid-message';
    el.innerHTML = "";
}
