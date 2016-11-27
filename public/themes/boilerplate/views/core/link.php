{##info##}
Stuff inside tags like this will get replaced if defined, deleted otherwise
You can easily write comments this way
The following will work with simple replacement by providing the class="" in
your code, or by using the subTemplate functionality, in which case you can
simply provide the class name(s), which might be considered cleaner
Use whatever style your coding-technique favors ^^
{/##info##}
<a href="{##link_url##}"{##css##} class="{##class##}"{/##css##}>{##link_text##}</a>