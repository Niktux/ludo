<?php

namespace Ludo\Import\BoardgameGeek;

use Ludo\Cache;

class Api
{
    private
        $cache,
        $url,
        $pageLimit;
    
    public function __construct($pageLimit = 20)
    {
        $this->cache = new Cache('bgg');
        $this->url = 'http://www.boardgamegeek.com/xmlapi/';
        $this->pageLimit = $pageLimit;
    }
    
    private function remoteCall($function, array $params = array(), array $queryParams = array())
    {
        $api = $this;
        $id = md5($function . implode('', $params) . implode('', $queryParams));
        
        return $this->cache->fetch($id, function() use($api, $function, $params, $queryParams){
            return $api->callAPI($function, $params, $queryParams);
        });
    }

    public function search($name, $exact = false, $offset = 0)
    {
        $parameters = array('search' => urlencode($name));
        if($exact === true)
        {
            $parameters['exact'] = 1;
        }
        
        $xml = $this->remoteCall(
            'search',
            array(),
            $parameters
        );
        
        $result = array();
  /*
        $it = new \LimitIterator(
            new \SimpleXMLIterator($xml->boardgame),
            $offset,
            $this->pageLimit
        );//*/
        
        foreach($xml->boardgame as $boardgame)
        {
            $attributes = $boardgame->attributes();
            $objectid = 0;
            foreach($attributes as $n => $v)
            {
                if($n == 'objectid')
                {
                    $objectid = $v;
                    break;
                }
            }
            
            $result[ (int)$objectid ] = $this->load($objectid);
        }
            
        return $result;
    }
    
    public function load($gameId)
    {
        $game = $this->boardgame($gameId);
        
        $players = $playingTime = null;
        if($game->minplayers != 0 && $game->maxplayers != 0)
        {
            $players = $game->minplayers . ' - ' . $game->maxplayers;
        }
        if($game->playingTime != 0)
        {
            $playingTime = (int) $game->playingtime;
        }
        
        $gameinfo = array(
            'id' => $gameId,
            'name' => $game->name,
            'title' => $game->name . ' (' . $game->yearpublished . ')',
            'box'  => (string) $game->thumbnail,
            'year'  => (string) $game->yearpublished,
            'publishers' => $this->extractList($game, 'boardgamepublisher'),
            'designers' => $this->extractList($game, 'boardgamedesigner'),
            'minPlayers' => (int) $game->minplayers,
            'maxPlayers' => (int) $game->maxplayers,
            'players' => $players,
            'playingTime' => $playingTime,
            'description' => $game->description,
        );
        
        return $gameinfo;
    }
    
    private function extractList(\SimpleXMLElement $game, $field)
    {
        $publishers = array();
        $children = (array) $game->$field;
        
        foreach($children as $key => $child)
        {
            // Skip '@attributes' key
            if(is_numeric($key))
            {
                $publishers[] = (string) $child;
            }
        }
        
        return $publishers;
    }
    
    private function boardgame($gameId)
    {
        $xml = $this->remoteCall('boardgame', array($gameId));
        
        return $xml->boardgame;
    }
    
    private function info($gameid, $field)
    {
        $boardgame = $this->boardgame($gameid);

        return $boardgame->$field;
    }
    
    
    private function callAPI($function, array $params = array(), array $queryParams = array())
    {
        $paramsStr = implode('/', $params);

        $queryString = '';
        if(! empty($queryParams))
        {
            $queryString = '?' . http_build_query($queryParams);
            
        }
        
        $url = $this->url . $function . '/' . $paramsStr . $queryString;
        
        $contents = file_get_contents($url);
        if($contents !== false)
        {
            $xml = new \SimpleXMLElement($contents);
            
            return $xml;
        }
        
        return null;
    }
}