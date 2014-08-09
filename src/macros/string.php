<?php

Str::macro('parseLinks', function($string)
{
    // Links
    $string = preg_replace("/([^\w\/])(www\.[a-z0-9\-]+\.[a-z0-9\-]+)/ui", "$1http://$2", $string);
    $string = preg_replace("/([\w]+:\/\/[\w-_?&;#~%=\.\/\@]+[\w\/])/ui", "<a target=\"_blank\" href=\"$1\">$1</a>", $string);

    // e-mail
    $string = preg_replace("/([\w-_?&;#~%=\.\/]+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,3}|[0-9]{1,3})(\]?))/ui", "<a href=\"mailto:$1\">$1</a>", $string);
    
    return $string;
});

Str::macro('parseTweet', function($string)
{
    $string = Str::parseLinks($string);

    // Twitter users
    $string = preg_replace("/@(\w+)/", "<a target=\"_blank\" href=\"https://twitter.com/$1\">@$1</a>", $string);
    
    // Twitter hashtags
    $string = preg_replace("/\s+#(\w+)/", "<a target=\"_blank\" href=\"https://twitter.com/hashtag/$1?src=hash\">#$1</a>", $string);

    return $string;
});