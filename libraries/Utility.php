<?php namespace UserReputation\Lib;

class Utility
{
  /* never allowed, string replacement */
  private static $never_allowed_str = array(
    'document.cookie'  => '[removed]',
    'document.write'  => '[removed]',
    '.parentNode'    => '[removed]',
    '.innerHTML'    => '[removed]',
    'window.location'  => '[removed]',
    '-moz-binding'    => '[removed]',
    '<!--'        => '&lt;!--',
    '-->'        => '--&gt;',
    '<![CDATA['      => '&lt;![CDATA['
  );

  /* never allowed, regex replacement */
  private static $never_allowed_regex = array(
    "javascript\s*:"      => '[removed]',
    "expression\s*(\(|&\#40;)"  => '[removed]', // CSS and IE
    "vbscript\s*:"        => '[removed]', // IE, surprise!
    "Redirect\s+302"      => '[removed]'
  );
  
  /**
   * Cut the text and add suffix.
   *
   * @param string $text Plain text
   * @param int $length Number length of text to be cutted
   * @Param string $suffix optional Suffix text to add
   * @return string
   */
  public static function cutText($text, $length, $suffix = '...')
  {
    if (strlen($text) > $length)
    {
      return substr($text, 0, $length).$suffix;
    }

    return $text;
  }
  
  public static function timeAgo($date, $granularity = 2)
  {
    $date = strtotime($date);
    $difference = time() - $date;
    $periods = array(
      'decade' => 315360000,
      'year' => 31536000,
      'month' => 2628000,
      'week' => 604800, 
      'day' => 86400,
      'hour' => 3600,
      'min' => 60,
      'sec' => 1
    );
                     
    foreach ($periods as $key => $value)
    {
      if ($difference >= $value)
      {
        $time = floor($difference/$value);
        $difference %= $value;
        $retval .= ($retval ? ' ' : '').$time.' ';
        $retval .= (($time > 1) ? __($key.'s', 'LL') : __($key, 'LL'));
        $granularity--;
      }
      
      if ($granularity == '0')
      {
        break;
      }
    }
    
    return $retval.' '.__('ago', 'LL');      
  }
  
  public static function escapeData($data)
  {
    return strip_tags(addslashes($data));
  }  
  
  public static function getUriArgument($params, $name)
  {
    $ix = -1;
    $iy = -1;
    if (strlen($params) != 0)
    {
      $args = strtolower($params);
      $arg = strtolower($name).'=';
      $ix = strpos($args, $arg);
      if ($ix > 0)
      {
        $ix = $ix + strlen($arg);
        $iy = strpos(substr($args, $ix, strlen($args)), '&');
        if (!$iy)
        {
          $iy = strlen($args);
        }
      }
    }
    return $argument = ($ix > 0) ? substr($params, $ix, $iy) : '';
  }

  public static function xssClean($str, $is_image = FALSE)
  {
    /*
    * Is the string an array?
    *
    */
    if (is_array($str))
    {
      while (list($key) = each($str))
      {
        $str[$key] = self::xssClean($str[$key]);
      }
    
      return $str;
    }
    
    /*
    * Remove Invisible Characters
    */
    $str = self::removeInvisibleCharacters($str);
    
    /*
    * Protect GET variables in URLs
    */
    
    // 901119URL5918AMP18930PROTECT8198
    
    $str = preg_replace('|\&([a-z\_0-9]+)\=([a-z\_0-9]+)|i', self::xssHash()."\\1=\\2", $str);
    
    /*
    * Validate standard character entities
    *
    * Add a semicolon if missing.  We do this to enable
    * the conversion of entities to ASCII later.
    *
    */
    $str = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);
    
    /*
    * Validate UTF16 two byte encoding (x00) 
    *
    * Just as above, adds a semicolon if missing.
    *
    */
    $str = preg_replace('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;",$str);
    
    /*
    * Un-Protect GET variables in URLs
    */
    $str = str_replace(self::xssHash(), '&', $str);
    
    /*
    * URL Decode
    *
    * Just in case stuff like this is submitted:
    *
    * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
    *
    * Note: Use rawurldecode() so it does not remove plus signs
    *
    */
    $str = rawurldecode($str);
    
    /*
    * Convert character entities to ASCII 
    *
    * This permits our tests below to work reliably.
    * We only convert entities that are within tags since
    * these are the ones that will pose security problems.
    *
    */
    
    $str = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", 'self::convertAttribute', $str);
    
    $str = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", 'self::htmlEntityDecodeCallback', $str);
    
    /*
    * Remove Invisible Characters Again!
    */
    $str = self::removeInvisibleCharacters($str);
    
    /*
    * Convert all tabs to spaces
    *
    * This prevents strings like this: ja  vascript
    * NOTE: we deal with spaces between characters later.
    * NOTE: preg_replace was found to be amazingly slow here on large blocks of data,
    * so we use str_replace.
    *
    */
    
    if (strpos($str, "\t") !== FALSE)
    {
      $str = str_replace("\t", ' ', $str);
    }
    
    /*
    * Capture converted string for later comparison
    */
    $converted_string = $str;
    
    /*
    * Not Allowed Under Any Conditions
    */
    
    foreach (self::$never_allowed_str as $key => $val)
    {
      $str = str_replace($key, $val, $str);   
    }
    
    foreach (self::$never_allowed_regex as $key => $val)
    {
      $str = preg_replace("#".$key."#i", $val, $str);   
    }
    
    /*
    * Makes PHP tags safe
    *
    *  Note: XML tags are inadvertently replaced too:
    *
    *  <?xml
    *
    * But it doesn't seem to pose a problem.
    *
    */
    if ($is_image === TRUE)
    {
      // Images have a tendency to have the PHP short opening and closing tags every so often
      // so we skip those and only do the long opening tags.
      $str = preg_replace('/<\?(php)/i', "&lt;?\\1", $str);
    }
    else
    {
      $str = str_replace(array('<?', '?'.'>'),  array('&lt;?', '?&gt;'), $str);
    }
    
    /*
    * Compact any exploded words
    *
    * This corrects words like:  j a v a s c r i p t
    * These words are compacted back to their correct state.
    *
    */
    $words = array('javascript', 'expression', 'vbscript', 'script', 'applet', 'alert', 'document', 'write', 'cookie', 'window');
    foreach ($words as $word)
    {
      $temp = '';
    
      for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++)
      {
        $temp .= substr($word, $i, 1)."\s*";
      }
    
      // We only want to do this when it is followed by a non-word character
      // That way valid stuff like "dealer to" does not become "dealerto"
      $str = preg_replace_callback('#('.substr($temp, 0, -3).')(\W)#is', 'self::compactExplodedWords', $str);
    }
    
    /*
    * Remove disallowed Javascript in links or img tags
    * We used to do some version comparisons and use of stripos for PHP5, but it is dog slow compared
    * to these simplified non-capturing preg_match(), especially if the pattern exists in the string
    */
    do
    {
      $original = $str;
    
      if (preg_match("/<a/i", $str))
      {
        $str = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", 'self::jsLinkRemoval', $str);
      }
    
      if (preg_match("/<img/i", $str))
      {
        $str = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", 'self::jsImgRemoval', $str);
      }
    
      if (preg_match("/script/i", $str) OR preg_match("/xss/i", $str))
      {
        $str = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
      }
    }
    while($original != $str);
    
    unset($original);
    
    /*
    * Remove JavaScript Event Handlers
    *
    * Note: This code is a little blunt.  It removes
    * the event handler and anything up to the closing >,
    * but it's unlikely to be a problem.
    *
    */
    $event_handlers = array('[^a-z_\-]on\w*','xmlns');
    
    if ($is_image === TRUE)
    {
      /*
      * Adobe Photoshop puts XML metadata into JFIF images, including namespacing, 
      * so we have to allow this for images. -Paul
      */
      unset($event_handlers[array_search('xmlns', $event_handlers)]);
    }
    
    $str = preg_replace("#<([^><]+?)(".implode('|', $event_handlers).")(\s*=\s*[^><]*)([><]*)#i", "<\\1\\4", $str);
    
    /*
    * Sanitize naughty HTML elements
    *
    * If a tag containing any of the words in the list
    * below is found, the tag gets converted to entities.
    *
    * So this: <blink>
    * Becomes: &lt;blink&gt;
    *
    */
    $naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
    $str = preg_replace_callback('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', 'self::sanitizeNaughtyHtml', $str);
    
    /*
    * Sanitize naughty scripting elements
    *
    * Similar to above, only instead of looking for
    * tags it looks for PHP and JavaScript commands
    * that are disallowed.  Rather than removing the
    * code, it simply converts the parenthesis to entities
    * rendering the code un-executable.
    *
    * For example:  eval('some code')
    * Becomes:    eval&#40;'some code'&#41;
    *
    */
    $str = preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);
    
    /*
    * Final clean up
    *
    * This adds a bit of extra precaution in case
    * something got through the above filters
    *
    */
    foreach (self::$never_allowed_str as $key => $val)
    {
      $str = str_replace($key, $val, $str);   
    }
    
    foreach (self::$never_allowed_regex as $key => $val)
    {
      $str = preg_replace("#".$key."#i", $val, $str);
    }
    
    /*
    *  Images are Handled in a Special Way
    *  - Essentially, we want to know that after all of the character conversion is done whether
    *  any unwanted, likely XSS, code was found.  If not, we return TRUE, as the image is clean.
    *  However, if the string post-conversion does not matched the string post-removal of XSS,
    *  then it fails, as there was unwanted XSS code found and removed/changed during processing.
    */
    
    if ($is_image === TRUE)
    {
      if ($str == $converted_string)
      {
        return TRUE;
      }
      else
      {
        return FALSE;
      }
    }
    
    return $str;
  }

  /**
  * Random Hash for protecting URLs
  *
  * @access  public
  * @return  string
  */
  public static function xssHash()
  {
    if (phpversion() >= 4.2)
      mt_srand();
    else
      mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);

    return (md5(time() + mt_rand(0, 1999999999)));
  }

  /**
  * Remove Invisible Characters
  *
  * This prevents sandwiching null characters
  * between ascii characters, like Java\0script.
  *
  * @access  public
  * @param  string
  * @return  string
  */
  public static function removeInvisibleCharacters($str)
  {
    // every control character except newline (dec 10), carriage return (dec 13), and horizontal tab (dec 09),
    $non_displayables = array(
      '/%0[0-8bcef]/',      // url encoded 00-08, 11, 12, 14, 15
      '/%1[0-9a-f]/',        // url encoded 16-31
      '/[\x00-\x08]/',      // 00-08
      '/\x0b/', '/\x0c/',      // 11, 12
      '/[\x0e-\x1f]/'        // 14-31
    );

    do
    {
      $cleaned = $str;
      $str = preg_replace($non_displayables, '', $str);
    }
    while ($cleaned != $str);

    return $str;
  }

  /**
  * Attribute Conversion
  *
  * Used as a callback for XSS Clean
  *
  * @access  public
  * @param  array
  * @return  string
  */
  public static function convertAttribute($match)
  {
    return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
  }

  /**
  * HTML Entity Decode Callback
  *
  * Used as a callback for XSS Clean
  *
  * @access  public
  * @param  array
  * @return  string
  */
  public static function htmlEntityDecodeCallback($match)
  {
    return self::htmlEntityDecode($match[0], 'UTF-8');
  }

  /**
  * Compact Exploded Words
  *
  * Callback function for xss_clean() to remove whitespace from
  * things like j a v a s c r i p t
  *
  * @access  public
  * @param  type
  * @return  type
  */
  public static function compactExplodedWords($matches)
  {
    return preg_replace('/\s+/s', '', $matches[1]).$matches[2];
  }

  /**
  * JS Link Removal
  *
  * Callback function for xss_clean() to sanitize links
  * This limits the PCRE backtracks, making it more performance friendly
  * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
  * PHP 5.2+ on link-heavy strings
  *
  * @access  private
  * @param  array
  * @return  string
  */
  public static function jsLinkRemoval($match)
  {
    $attributes = self::filterAttributes(str_replace(array('<', '>'), '', $match[1]));
    return str_replace($match[1], preg_replace("#href=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
  }

  /**
  * JS Image Removal
  *
  * Callback function for xss_clean() to sanitize image tags
  * This limits the PCRE backtracks, making it more performance friendly
  * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
  * PHP 5.2+ on image tag heavy strings
  *
  * @access  private
  * @param  array
  * @return  string
  */
  public static function jsImgRemoval($match)
  {
    $attributes = self::filterAttributes(str_replace(array('<', '>'), '', $match[1]));
    return str_replace($match[1], preg_replace("#src=.*?(alert\(|alert&\#40;|javascript\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
  }

  /**
  * Sanitize Naughty HTML
  *
  * Callback function for xss_clean() to remove naughty HTML elements
  *
  * @access  private
  * @param  array
  * @return  string
  */
  public static function sanitizeNaughtyHtml($matches)
  {
    // encode opening brace
    $str = '&lt;'.$matches[1].$matches[2].$matches[3];

    // encode captured opening or closing brace to prevent recursive vectors
    $str .= str_replace(array('>', '<'), array('&gt;', '&lt;'), $matches[4]);

    return $str;
  }

  /**
  * HTML Entities Decode
  *
  * This function is a replacement for htmlEntityDecode()
  *
  * In some versions of PHP the native function does not work
  * when UTF-8 is the specified character set, so this gives us
  * a work-around.  More info here:
  * http://bugs.php.net/bug.php?id=25670
  *
  * @access  private
  * @param  string
  * @param  string
  * @return  string
  */
  /* -------------------------------------------------
  /*  Replacement for htmlEntityDecode()
  /* -------------------------------------------------*/

  /*
  NOTE: htmlEntityDecode() has a bug in some PHP versions when UTF-8 is the
  character set, and the PHP developers said they were not back porting the
  fix to versions other than PHP 5.x.
  */
  public static function htmlEntityDecode($str, $charset='UTF-8')
  {
    if (stristr($str, '&') === FALSE) return $str;

    // The reason we are not using htmlEntityDecode() by itself is because
    // while it is not technically correct to leave out the semicolon
    // at the end of an entity most browsers will still interpret the entity
    // correctly.  htmlEntityDecode() does not convert entities without
    // semicolons, so we are left with our own little solution here. Bummer.

    if (function_exists('htmlEntityDecode') && (strtolower($charset) != 'utf-8' OR version_compare(phpversion(), '5.0.0', '>=')))
    {
      $str = html_entity_decode($str, ENT_COMPAT, $charset);
      $str = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
      return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
    }

    // Numeric Entities
    $str = preg_replace('~&#x(0*[0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);
    $str = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);

    // Literal Entities - Slightly slow so we do another check
    if (stristr($str, '&') === FALSE)
    {
      $str = strtr($str, array_flip(get_html_translation_table(HTML_ENTITIES)));
    }

    return $str;
  }

  /**
  * Filter Attributes
  *
  * Filters tag attributes for consistency and safety
  *
  * @access  public
  * @param  string
  * @return  string
  */
  public static function filterAttributes($str)
  {
    $out = '';

    if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
    {
      foreach ($matches[0] as $match)
      {
        $out .= preg_replace("#/\*.*?\*/#s", '', $match);
      }
    }

    return $out;
  }
  
  /**
   * Retrieve the current time based on specified type.
   *
   * The 'mysql' type will return the time in the format for MySQL DATETIME field.
   * The 'timestamp' type will return the current timestamp.
   *
   * If $gmt is set to either '1' or 'true', then both types will use GMT time.
   * if $gmt is false, the output is adjusted with the GMT offset in the option.
   *
   * @param string $type Either 'mysql' or 'timestamp'.
   * @param int|bool $gmt Optional. Whether to use GMT timezone. Default is false.
   * @return int|string String if $type is 'gmt', int if $type is 'timestamp'.
   */
  public static function currentTime( $type, $gmt = 0 )
  {
    switch ( $type )
    {
      case 'mysql':
        return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ((int) date('Z')) ) );
        break;
      case 'timestamp':
        return ( $gmt ) ? time() : time() + ((int) date('Z'));
        break;
    }
  }
  
  public static function parseURL($url)
  {
    $r  = '!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?';
    $r .= '(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!i';

    preg_match ( $r, $url, $out );
    
    $result = @array(
      "scheme" => $out[1],
      "host" => $out[4].(($out[5] == '') ? '' : ':'.$out[5]),
      "user" => $out[2],
      "pass" => $out[3],
      "path" => $out[6],
      "query" => $out[7],
      "fragment" => $out[8]
    );
    
    return $result;
  }
  
  public static function currentUrl()
  {
    $_SERVER['HTTPS'] = (isset($_SERVER['HTTPS'])) ? $_SERVER['HTTPS'] : 'off';
    
    $url = 'http';
    if ($_SERVER['HTTPS'] == 'on')
    {
      $url .= 's';
    }
    $url .= '://';
    
    if ($_SERVER['SERVER_PORT'] != '80')
    {
      $url .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$_SERVER['REQUEST_URI'];
    }
    else
    {
      $url .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    }
    
    return $url;
  }
}
