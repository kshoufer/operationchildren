function findItemPosX(obj)
{
        var curleft = 0;
        if (obj.offsetParent)
        {
                while (obj.offsetParent)
                {
                        curleft += obj.offsetLeft
                        var borderLeftWidth = 0;
                        if(document.all)
                                borderLeftWidth = getStyle(obj,"borderLeftWidth");
                        else
                                borderLeftWidth = getStyle(obj,"border-left-width");

                        borderLeftWidth = borderLeftWidth.replace("px","");

                        if(isNumeric(borderLeftWidth))
                        {
                                curleft += parseInt(borderLeftWidth);
                        }

                        obj = obj.offsetParent;
                }
        }
        else if (obj.x)
                curleft += obj.x;
        return curleft;
}

function findItemPosY(obj)
{
        var curtop = 0;
        var printstring = '';
        if (obj.offsetParent)
        {
                while (obj.offsetParent)
                {
                        //printstring += ' element ' + obj.tagName + ' has ' + obj.offsetTop;
                        curtop += obj.offsetTop
                        var borderTopWidth = 0;

                        if(document.all)
                                borderTopWidth = getStyle(obj,"borderTopWidth");
                        else
                                borderTopWidth = getStyle(obj,"border-top-width");

                        borderTopWidth = borderTopWidth.replace("px","");

                        if(isNumeric(borderTopWidth))
                        {
                                curtop += parseInt(borderTopWidth);
                        }
                        obj = obj.offsetParent;
                }
        }
        else if (obj.y)
                curtop += obj.y;
        //window.status = printstring;
        return curtop;
}

function getStyle(x,styleProp)
{
        //var x = document.getElementById(el);
        if (x.currentStyle)
                var y = x.currentStyle[styleProp];
        else if (window.getComputedStyle)
                var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
        return y;
}

function isNumeric(alphane)
{
        var numaric = alphane;
        for(var j=0; j<numaric.length; j++)
        {
                  var alphaa = numaric.charAt(j);
                  var hh = alphaa.charCodeAt(0);
                  if((hh > 47 && hh<58))
                  {
                  }
                else    {
                         return false;
                  }
                }
 return true;
}