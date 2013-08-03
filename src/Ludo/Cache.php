<?php

namespace Ludo;

use \DateTime as DateTime;
use \DateInterval as DateInterval;

class Cache
{
    const
        EXTENSION = '.cache',
        DEFAULT_EXPIRATION_TIME = 'PT15M';
    
    private
        $rootDir;
    
    public function __construct($dir = 'spare', $hashDepth = 3)
    {
        $this->rootDir = 'var/cache/' . rtrim($dir, '/') . '/';
        $this->hashDepth = $hashDepth;
    }
    
    public function fetch($id, \Closure $generationCallback, $expirationTime = self::DEFAULT_EXPIRATION_TIME)
    {
        $key = $this->computeKey($id);
        
        if(! $this->exists($key, $expirationTime))
        {
            $content = $generationCallback();
            $this->store($key, $content);
        }
        
        return $this->get($key);
    }
    
    private function computeKey($id)
    {
        return $this->rootDir . $this->hash($id);
    }
    
    private function exists($key, $expirationTime)
    {
        if(is_file($key))
        {
            $expirationDate = new DateTime();
            $expirationDate->setTimestamp(filemtime($key));
            $expirationDate->add(new DateInterval($expirationTime));
            
            $now = new DateTime();
            
            return $expirationDate > $now;
        }
        
        return false;
    }
    
    private function store($key, $content)
    {
        $this->ensureDirectoryExists(dirname($key));
        file_put_contents($key, $this->serialize($content));
    }
    
    private function get($key)
    {
        if(is_file($key))
        {
            return $this->unserialize(file_get_contents($key));
        }
        
        return null;
    }
    
    private function ensureDirectoryExists($directory)
    {
        if(!is_dir($directory))
        {
            if(!mkdir($directory, 0755, true))
            {
                throw new \Firenote\Exceptions\Filesystem("Cannot create directory $directory");
            }
        }
    }
    
    private function hash($id)
    {
        $filename = md5($id);
        
        $hashedPart = substr($filename, 0, $this->hashDepth);
        $directories = str_split($hashedPart);
    
        return implode('/', $directories) . '/' . substr($filename, $this->hashDepth) . self::EXTENSION;
    }
    
    private function serialize($content)
    {
        if($content instanceof \SimpleXMLElement)
        {
            $content = $content->asXml();
        }
        
        return $content;
    }
    
    private function unserialize($content)
    {
        if($this->isSerializedData($content))
        {
            return unserialize($content);
        }
        
        if($this->isXML($content))
        {
            return new \SimpleXMLElement($content);
        }
        
        throw new \Firenote\Exception('Cannot read cache content');
    }
    
    private function isSerializedData($content)
    {
        return stripos($content, '{') === 0;
    }
    
    private function isXML($content)
    {
        return stripos($content, '<?xml') === 0;
    }
}