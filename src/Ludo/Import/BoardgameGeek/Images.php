<?php

namespace Ludo\Import\BoardgameGeek;

class Images
{
    const URL = 'http://cf.geekdo-images.com/images/pic';
    
    const SQUARE    = 'sq';
    const MEDIUM    = 'md';
    const LARGE     = 'lg';
    const THUMBNAIL = 't';
    
    public static function formatExists($id, $extension, $format)
    {
        $newUrl = self::URL . $id . '_' . $format . '.' . $extension;
        $file = @fopen($newUrl, 'r');
        if($file)
        {
            fclose($file);
            
            return true;
        }
        
        return false;
    }
    
    public static function formatExistsFromAnother($urlOfExistingFormat, $formatToTest)
    {
        if(preg_match('~/pic(\d+)_([^\.]+)\.(.+)~', $urlOfExistingFormat, $matches))
        {
            return self::formatExists($matches[1], $matches[3], $formatToTest);
        }
        
        return false;
    }
    
    
    public static function getFormat($id, $extension, $format)
    {
        if(self::formatExists($id, $extension, $format))
        {
            return self::URL . $id . '_' . $format . '.' . $extension;
        }

        // original format
        return self::URL . $id . '.' . $extension;
    }
    
    
    public static function getFormatFromAnother($urlOfExistingFormat, $formatToGet)
    {
        if(preg_match('~/pic(\d+)_([^\.]+)\.(.+)~', $urlOfExistingFormat, $matches))
        {
            return self::getFormat($matches[1], $matches[3], $formatToGet);
        }
        
        return null;
    }
}